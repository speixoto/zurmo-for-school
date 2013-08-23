<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * For exports with many records we create jobs that will generate export file
     * in background, and send notification to user with export download link,
     * when export job is completed.
     */
    class ExportJob extends BaseJob
    {
        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('ExportModule', 'Export Job');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'Export';
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('ExportModule', 'Every 2 minutes.');
        }

        /**
        * @returns the threshold for how long a job is allowed to run. This is the 'threshold'. If a job
        * is running longer than the threshold, the monitor job might take action on it since it would be
        * considered 'stuck'.
        */
        public static function getRunTimeThresholdInSeconds()
        {
            return 600;
        }

        public function run()
        {
            $exportItems = ExportItem::getUncompletedItems();
            $startTime   = Yii::app()->performance->startClock();
            Yii::app()->performance->startMemoryUsageMarker();
            if (count($exportItems) > 0)
            {
                foreach ($exportItems as $exportItem)
                {
                    //todo: some of this should be combined with non-async for better non-async memory management and reuse
                    //todo: change userModel to user who requested this... so security pans.
                    //todo: deal with proper paging for report export
                    //todo: add tests
                    //todo: manual tests
                    $this->processExportItem($exportItem);
                }
            }
            $this->processEndMemoryUsageMessage($startTime);
            return true;
        }

        protected function processExportItem(ExportItem $exportItem)
        {
            $dataProviderOrIdsToExport = unserialize($exportItem->serializedData);
            if($dataProviderOrIdsToExport instanceOf RedBeanModelDataProvider)
            {
                $this->processRedBeanModelDataProviderExport($exportItem, $dataProviderOrIdsToExport);
            }
            elseif($dataProviderOrIdsToExport instanceOf ReportDataProvider)
            {
                $this->processReportDataProviderExport($exportItem, $dataProviderOrIdsToExport);
            }
            else
            {
                $this->processIdsToExport($exportItem, $dataProviderOrIdsToExport);
            }
            unset($dataProviderOrIdsToExport);
        }

        protected function processRedBeanModelDataProviderExport(ExportItem $exportItem, RedBeanModelDataProvider $dataProvider)
        {

            $dataProvider->getPagination()->setPageSize(6);

            $headerData = array();
            $data       = array();
            $offset     = 0;
            $this->processExportPage($dataProvider, $offset, $headerData, $data);
            $content         = ExportItemToCsvFileUtil::export($data, $headerData);
            $exportFileModel = $this->makeExportFileModelByContent($content, $exportItem->exportFileName);
            $this->processCompletedExportItem($exportItem, $exportFileModel);
        }

        protected function processReportDataProviderExport(ExportItem $exportItem, ReportDataProvider $dataProvider)
        {
            $headerData = array();
            $reportToExportAdapter  = ReportToExportAdapterFactory::
                createReportToExportAdapter($dataProvider->getReport(),
                    $dataProvider);
            if (count($headerData) == 0)
            {
                $headerData = $reportToExportAdapter->getHeaderData();
            }
            $data            = $reportToExportAdapter->getData();
            $content         = ExportItemToCsvFileUtil::export($data, $headerData);
            $exportFileModel = $this->makeExportFileModelByContent($content, $exportItem->exportFileName);
            $this->processCompletedExportItem($exportItem, $exportFileModel);
        }

        protected function processIdsToExport(ExportItem $exportItem, $idsToExport)
        {
            $headerData = array();
            $data       = array();
            $models     = array();
            foreach ($idsToExport as $idToExport)
            {
                $models[] = call_user_func(array($exportItem->modelClassName, 'getById'), intval($idToExport));
            }
            $this->processExportModels($models, $headerData, $data);
            $content         = ExportItemToCsvFileUtil::export($data, $headerData);
            $exportFileModel = $this->makeExportFileModelByContent($content, $exportItem->exportFileName);
            $this->processCompletedExportItem($exportItem, $exportFileModel);
        }

        protected function makeExportFileModelByContent($content, $exportFileName)
        {
            assert('is_string($exportFileName)');
            $fileContent                  = new FileContent();
            $fileContent->content         = $content;
            $exportFileModel              = new ExportFileModel();
            $exportFileModel->fileContent = $fileContent;
            $exportFileModel->name        = $exportFileName . ".csv";
            $exportFileModel->type        = 'application/octet-stream';
            $exportFileModel->size        = strlen($content);
            $saved = $exportFileModel->save();
            if(!$saved)
            {
                throw new FailedToSaveFileModelException();
            }
            return $exportFileModel;
        }

        protected function processCompletedExport(ExportItem $exportItem, ExportFileModel $exportFileModel)
        {
            $exportItem->isCompleted = true;
            $exportItem->exportFileModel = $exportFileModel;
            $exportItem->save();
            $message                    = new NotificationMessage();
            $message->htmlContent       = Zurmo::t('ExportModule', 'Export of {fileName} requested on {dateTime} is completed. <a href="{url}">Click here</a> to download file!',
                array(
                    '{fileName}' => $exportItem->exportFileName,
                    '{url}'      => Yii::app()->createUrl('export/default/download', array('id' => $exportItem->id)),
                    '{dateTime}' => DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($exportItem->createdDateTime, 'long'),
                )
            );
            $rules                      = new ExportProcessCompletedNotificationRules();
            NotificationsUtil::submit($message, $rules);
        }

        protected function processEndMemoryUsageMessage($startTime)
        {
            $memoryUsageIncrease = Yii::app()->performance->getMemoryMarkerUsage();
            $endTime             = Yii::app()->performance->endClockAndGet();

            $this->getMessageLogger()->addInfoMessage(
                Zurmo::t('ExportModule',
                    'Memory in use: {memoryInUse} Memory Increase: {memoryUsageIncrease} Processing Time: {processingTime}',
                    array('{memoryInUse}'         => Yii::app()->performance->getMemoryUsage(),
                        '{memoryUsageIncrease}' => $memoryUsageIncrease,
                        '{processingTime}'      => number_format(($endTime - $startTime), 3))));
        }

        protected function processExportPage(CDataProvider $dataProvider, $offset, & $headerData, & $data)
        {
            $dataProvider->setOffset($offset);
            $models = $dataProvider->getData(true);
            $this->processExportModels($models, $headerData, $data);
        }

        protected function processExportModels(array $models, & $headerData, & $data)
        {
            foreach($models as $model)
            {
                $canRead = ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($model, Permission::READ);
                if ($canRead)
                {
                    $modelToExportAdapter  = new ModelToExportAdapter($model);
                    if (count($headerData) == 0)
                    {
                        $headerData        = $modelToExportAdapter->getHeaderData();
                    }
                    $data[]                = $modelToExportAdapter->getData();
                    unset($modelToExportAdapter);
                }
                $this->runGarbageCollection($model);
            }
            unset($models);
        }

        protected function runGarbageCollection($model)
        {
            foreach ($model->attributeNames() as $attributeName)
            {
                if($model->isRelation($attributeName) && $model->{$attributeName} instanceof RedBeanModel)
                {
                    $model->{$attributeName}->forgetValidators();
                    $model->{$attributeName}->forget();
                }
            }
            $model->forgetValidators();
            $model->forget();
        }
    }
?>