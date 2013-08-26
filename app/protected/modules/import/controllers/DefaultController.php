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

    class ImportDefaultController extends ZurmoBaseController
    {
        public function filters()
        {
            $filters   = array();
            $filters[] = array(
                         ZurmoBaseController::RIGHTS_FILTER_PATH,
                         'moduleClassName' => 'ImportModule',
                         'rightName' => ImportModule::getAccessRight(),
            );
            return $filters;
        }

        public function actionIndex()
        {
            $this->actionStep1();
        }

        /**
         * Step 1. Select the module to import data into.
         */
        public function actionStep1()
        {
            $importWizardForm = new ImportWizardForm();
            if (isset($_GET['id']))
            {
                $import = Import::getById((int)$_GET['id']);
            }
            else
            {
                $import = new Import();
            }
            $importWizardForm = ImportWizardUtil::makeFormByImport($import);
            if (isset($_POST[get_class($importWizardForm)]))
            {
                ImportWizardUtil::setFormByPostForStep1($importWizardForm, $_POST[get_class($importWizardForm)]);
                $this->attemptToValidateImportWizardFormAndSave($importWizardForm, $import, 'step2');
            }
            $title = Zurmo::t('ImportModule', 'Import Wizard - Select Module');
            if ($importWizardForm->importRulesType != null)
            {
                $importRulesClassName  = ImportRulesUtil::getImportRulesClassNameByType($importWizardForm->importRulesType);
            }
            else
            {
                $importRulesClassName  = null;
            }
            $progressBarAndStepsView = new ImportStepsAndProgressBarForWizardView($importRulesClassName, 0);
            $importView = new ImportWizardImportRulesView($this->getId(),
                                                          $this->getModule()->getId(),
                                                          $importWizardForm, $title);
            $view       = new ImportPageView(ZurmoDefaultAdminViewUtil::
                              makeTwoStandardViewsForCurrentUser($this, $progressBarAndStepsView, $importView));
            echo $view->render();
        }

        /**
         * Step 2. Upload the csv to import.
         */
        public function actionStep2($id)
        {
            $import           = Import::getById((int)$id);
            $importWizardForm = ImportWizardUtil::makeFormByImport($import);

            if (isset($_POST[get_class($importWizardForm)]))
            {
                ImportWizardUtil::setFormByPostForStep2($importWizardForm, $_POST[get_class($importWizardForm)]);
                if ($importWizardForm->fileUploadData == null)
                {
                    $importWizardForm->addError('fileUploadData',
                    Zurmo::t('ImportModule', 'A file must be uploaded in order to continue the import process.'));
                }
                elseif (!ImportWizardUtil::importFileHasAtLeastOneImportRow($importWizardForm, $import))
                {
                    if ($importWizardForm->firstRowIsHeaderRow)
                    {
                        $importWizardForm->addError('fileUploadData',
                        Zurmo::t('ImportModule', 'The file that has been uploaded only has a header row and no additional rows to import.'));
                    }
                    else
                    {
                        $importWizardForm->addError('fileUploadData',
                        Zurmo::t('ImportModule', 'A file must be uploaded with at least one row to import.'));
                    }
                }
                else
                {
                    $importRulesClassName  = $importWizardForm->importRulesType . 'ImportRules';
                    if (!is_subclass_of($importRulesClassName::getModelClassName(), 'SecurableItem'))
                    {
                        $nextStep = 'step4';
                    }
                    else
                    {
                        $nextStep = 'step3';
                    }
                    $this->attemptToValidateImportWizardFormAndSave($importWizardForm, $import, $nextStep);
                }
            }
            $title = Zurmo::t('ImportModule', 'Import Wizard - Upload File');
            $importRulesClassName  = ImportRulesUtil::getImportRulesClassNameByType($importWizardForm->importRulesType);
            $progressBarAndStepsView = new ImportStepsAndProgressBarForWizardView($importRulesClassName, 1);
            $importView = new ImportWizardUploadFileView($this->getId(), $this->getModule()->getId(),
                                                         $importWizardForm, $title);
            $view       = new ImportPageView(ZurmoDefaultAdminViewUtil::
                              makeTwoStandardViewsForCurrentUser($this, $progressBarAndStepsView, $importView));
            echo $view->render();
        }

        /**
         * Step 3. Decide permissions for upload.
         */
        public function actionStep3($id)
        {
            $import                = Import::getById((int)$_GET['id']);
            $importWizardForm      = ImportWizardUtil::makeFormByImport($import);
            if (isset($_POST[get_class($importWizardForm)]))
            {
                ImportWizardUtil::setFormByPostForStep3($importWizardForm, $_POST[get_class($importWizardForm)]);
                $this->attemptToValidateImportWizardFormAndSave($importWizardForm, $import, 'step4');
            }
            $title      = Zurmo::t('ImportModule', 'Import Wizard - Select Permissions');
            $importRulesClassName  = ImportRulesUtil::getImportRulesClassNameByType($importWizardForm->importRulesType);
            $progressBarAndStepsView = new ImportStepsAndProgressBarForWizardView($importRulesClassName, 2);
            $importView = new ImportWizardSetModelPermissionsView($this->getId(),
                                                                  $this->getModule()->getId(),
                                                                  $importWizardForm, $title);
            $view       = new ImportPageView(ZurmoDefaultAdminViewUtil::
                                makeTwoStandardViewsForCurrentUser($this, $progressBarAndStepsView, $importView));
            echo $view->render();
        }

        /**
         * Step 4. Import mapping
         */
        public function actionStep4($id)
        {
            $import               = Import::getById((int)$id);
            $importWizardForm     = ImportWizardUtil::makeFormByImport($import);
            $importWizardForm->setScenario('saveMappingData');
            $importRulesClassName = ImportRulesUtil::getImportRulesClassNameByType($importWizardForm->importRulesType);
            if (isset($_POST[get_class($importWizardForm)]))
            {
                $reIndexedPostData                          = ImportMappingUtil::
                                                              reIndexExtraColumnNamesByPostData(
                                                              $_POST[get_class($importWizardForm)]);
                $sanitizedPostData                          = ImportWizardFormPostUtil::
                                                              sanitizePostByTypeForSavingMappingData(
                                                              $importWizardForm->importRulesType, $reIndexedPostData);
                ImportWizardUtil::setFormByPostForStep4($importWizardForm, $sanitizedPostData);

                $mappingDataMappingRuleFormsAndElementTypes = MappingRuleFormAndElementTypeUtil::
                                                              makeFormsAndElementTypesByMappingDataAndImportRulesType(
                                                              $importWizardForm->mappingData,
                                                              $importWizardForm->importRulesType);
                $validated                                  = MappingRuleFormAndElementTypeUtil::
                                                              validateMappingRuleForms(
                                                              $mappingDataMappingRuleFormsAndElementTypes);
                if ($validated)
                {
                    //Still validate even if MappingRuleForms fails, so all errors are captured and returned.
                    $this->attemptToValidateImportWizardFormAndSave($importWizardForm, $import, 'step5');
                }
                else
                {
                    $importWizardForm->validate();
                    $importWizardForm->addError('mappingData', Zurmo::t('ImportModule',
                                                'There are errors with some of your mapping rules. Please fix.'));
                }
            }
            else
            {
                $mappingDataMappingRuleFormsAndElementTypes = MappingRuleFormAndElementTypeUtil::
                                                              makeFormsAndElementTypesByMappingDataAndImportRulesType(
                                                              $importWizardForm->mappingData,
                                                              $importWizardForm->importRulesType);
            }
            $dataProvider                                   = $this->makeDataProviderForSampleRow($import,
                                                              (bool)$importWizardForm->firstRowIsHeaderRow);
            if ($importWizardForm->firstRowIsHeaderRow)
            {
                $headerRow = ImportDatabaseUtil::getFirstRowByTableName($import->getTempTableName());
                assert('$headerRow != null');
            }
            else
            {
                $headerRow = null;
            }
            $sampleData                                     = $dataProvider->getData();
            assert('count($sampleData) == 1');
            $sample                                         = current($sampleData);
            $pagerUrl                                       = Yii::app()->createUrl('import/default/sampleRow',
                                                              array('id' => $import->id));
            $pagerContent                                   = ImportDataProviderPagerUtil::
                                                              renderPagerAndHeaderTextContent($dataProvider, $pagerUrl);
            $mappingDataMetadata                            = ImportWizardMappingViewUtil::
                                                              resolveMappingDataForView($importWizardForm->mappingData,
                                                              $sample, $headerRow);
            $mappableAttributeIndicesAndDerivedTypes        = $importRulesClassName::
                                                              getMappableAttributeIndicesAndDerivedTypes();
            $title                                          = Zurmo::t('ImportModule', 'Import Wizard - Map Fields');
            $importRulesClassName                           = ImportRulesUtil::getImportRulesClassNameByType(
                                                              $importWizardForm->importRulesType);
            $stepToUse = ImportStepsAndProgressBarForWizardView::resolveAfterUploadStepByImportClassName(3, $importRulesClassName);
            $progressBarAndStepsView                        = new ImportStepsAndProgressBarForWizardView(
                                                              $importRulesClassName, $stepToUse);
            $importView                                     = new ImportWizardMappingView($this->getId(),
                                                              $this->getModule()->getId(),
                                                              $importWizardForm,
                                                              $pagerContent,
                                                              $mappingDataMetadata,
                                                              $mappingDataMappingRuleFormsAndElementTypes,
                                                              $mappableAttributeIndicesAndDerivedTypes,
                                                              $importRulesClassName::getRequiredAttributesLabelsData(),
                                                              $title);
            $view                                           = new ImportPageView(ZurmoDefaultAdminViewUtil::
                                                              makeTwoStandardViewsForCurrentUser($this,
                                                              $progressBarAndStepsView, $importView));
            echo $view->render();
        }

        /**
         * Step 5. Analyze data in a sequential process.
         * @param integer id - Import model id
         * @param string $step
         */
        public function actionStep5($id, $step = null, $pageSize = null)
        {
            $getData              = GetUtil::getData();
            if (isset($getData['nextParams']))
            {
                $nextParams = $getData['nextParams'];
            }
            else
            {
                $nextParams = null;
            }
            assert('$step == null || is_string($step)');
            assert('$nextParams == null || is_array($nextParams)');
            $import               = Import::getById((int)$id);
            $importWizardForm     = ImportWizardUtil::makeFormByImport($import);
            $unserializedData     = unserialize($import->serializedData);
            if ($pageSize == null)
            {
                $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('importPageSize');
            }
            $config               = array('pagination' => array('pageSize' => $pageSize));
            $filteredByStatus       = $this->resolveFilteredByStatus();
            $dataProvider         = new ImportDataProvider($import->getTempTableName(),
                                                           (bool)$importWizardForm->firstRowIsHeaderRow,
                                                           $config, null, $filteredByStatus);
            $sequentialProcess    = new ImportDataAnalysisSequentialProcess($import, $dataProvider);
            $sequentialProcess->run($step, $nextParams);
            $route                = $this->getModule()->getId() . '/' . $this->getId() . '/step5';
            if ($sequentialProcess->isComplete())
            {
                $this->resolveResettingPageOnCompletion($dataProvider);
                $columnNamesAndAttributeIndexOrDerivedTypeLabels = ImportMappingUtil::
                                                                  makeColumnNamesAndAttributeIndexOrDerivedTypeLabels(
                                                                  $unserializedData['mappingData'],
                                                                  $unserializedData['importRulesType']);
                if (isset($getData['ajax']) && $getData['ajax'] == 'import-temp-table-list-view')
                {
                    $resolvedView = new AnalysisResultsImportTempTableListView(
                                        $this->getId(),
                                        $this->getModule()->getId(),
                                        $dataProvider,
                                        $unserializedData['mappingData'],
                                        $importWizardForm->importRulesType,
                                        ImportWizardDataAnalysisCompleteView::resolveConfigurationForm(),
                                        new ZurmoActiveForm(),
                                        $import->id);
                }
                else
                {
                    $dataAnalysisCompleteView = new ImportWizardDataAnalysisCompleteView($this->getId(),
                                                    $this->getModule()->getId(),
                                                    $importWizardForm,
                                                    $columnNamesAndAttributeIndexOrDerivedTypeLabels,
                                                    $dataProvider,
                                                    $unserializedData['mappingData']);
                    $resolvedView = new ContainedViewCompleteSequentialProcessView($dataAnalysisCompleteView);
                }
            }
            else
            {
                $resolvedView = SequentialProcessViewFactory::makeBySequentialProcess($sequentialProcess, $route);
            }
            if ($step == null)
            {
                $title                   = Zurmo::t('ImportModule', 'Import Wizard - Analyze Data');
                $importRulesClassName    = ImportRulesUtil::getImportRulesClassNameByType($importWizardForm->importRulesType);
                $stepToUse = ImportStepsAndProgressBarForWizardView::resolveAfterUploadStepByImportClassName(4, $importRulesClassName);
                $progressBarAndStepsView = new ImportStepsAndProgressBarForWizardView($importRulesClassName, $stepToUse);
                $wrapperView  = new ImportSequentialProcessContainerView($resolvedView,
                                                                         $sequentialProcess->getAllStepsMessage(),
                                                                         $title);
                $wrapperView->setCssClasses(array('DetailsView'));
                $view = new ImportPageView(ZurmoDefaultAdminViewUtil::makeTwoStandardViewsForCurrentUser($this,
                                $progressBarAndStepsView, $wrapperView));
            }
            else
            {
                $view        = new AjaxPageView($resolvedView);
            }
            echo $view->render();
        }

        protected function resolveFilteredByStatus()
        {
            $getData = GetUtil::getData();
            if (isset($getData['ImportResultsConfigurationForm']) &&
               !empty($getData['ImportResultsConfigurationForm']['filteredByStatus']) &&
                $getData['ImportResultsConfigurationForm']['filteredByStatus'] !=
                ImportResultsConfigurationForm::FILTERED_BY_ALL)
            {
                return $getData['ImportResultsConfigurationForm']['filteredByStatus'];
            }
            return null;
        }

        protected function resolveResettingPageOnCompletion(ImportDataProvider $dataProvider)
        {
            $getData = GetUtil::getData();
            if (!isset($getData['ajax']))
            {
                $dataProvider->getPagination()->setCurrentPage(0);
            }
        }

        /**
         * Step 6. Sanitize and create/update models using a sequential process.
         * @param integer $id - Import model id
         * @param null|string $step
         * @param null|int $pageSize
         */
        public function actionStep6($id, $step = null, $pageSize = null)
        {
            $getData              = GetUtil::getData();
            if (isset($getData['nextParams']))
            {
                $nextParams = $getData['nextParams'];
            }
            else
            {
                $nextParams = null;
            }
            assert('$step == null || is_string($step)');
            assert('$nextParams == null || is_array($nextParams)');
            $import               = Import::getById((int)$id);
            $importWizardForm     = ImportWizardUtil::makeFormByImport($import);
            $cs                   = Yii::app()->getClientScript();
            $cs->registerCoreScript('bbq');
            $unserializedData     = unserialize($import->serializedData);
            $passedInPageSize     = $pageSize;
            if ($pageSize == null)
            {
                $pageSize             = Yii::app()->pagination->resolveActiveForCurrentUserByType('importPageSize');
            }
            $config               = array('pagination' => array('pageSize' => $pageSize));
            $filteredByStatus       = $this->resolveFilteredByStatus();
            $dataProvider         = new ImportDataProvider($import->getTempTableName(),
                                                           (bool)$importWizardForm->firstRowIsHeaderRow,
                                                           $config, (int)$filteredByStatus);
            $sequentialProcess    = new ImportCreateUpdateModelsSequentialProcess($import, $dataProvider);
            Yii::app()->gameHelper->muteScoringModelsOnSave();
            $sequentialProcess->run($step, $nextParams);
            Yii::app()->gameHelper->unmuteScoringModelsOnSave();
            $nextStep             = $sequentialProcess->getNextStep();
            $route                = $this->getModule()->getId() . '/' . $this->getId() . '/step6';
            if ($sequentialProcess->isComplete())
            {
                $this->resolveResettingPageOnCompletion($dataProvider);
                $importingIntoModelClassName = $unserializedData['importRulesType'] . 'ImportRules';
                Yii::app()->gameHelper->triggerImportEvent($importingIntoModelClassName::getModelClassName());
                if (isset($getData['ajax']) && $getData['ajax'] == 'import-temp-table-list-view')
                {
                    $resolvedView = new ImportResultsImportTempTableListView(
                                        $this->getId(),
                                        $this->getModule()->getId(),
                                        $dataProvider,
                                        $unserializedData['mappingData'],
                                        $importWizardForm->importRulesType,
                                        ImportWizardDataAnalysisCompleteView::resolveConfigurationForm(),
                                        new ZurmoActiveForm(),
                                        $import->id);
                }
                else
                {
                    $importCompleteView          = $this->makeImportCompleteView($import, $importWizardForm, $dataProvider, true, $passedInPageSize);
                    $resolvedView                = new ContainedViewCompleteSequentialProcessView($importCompleteView);
                }
            }
            else
            {
                $resolvedView = SequentialProcessViewFactory::makeBySequentialProcess($sequentialProcess, $route);
            }
            if ($step == null)
            {
                $title = Zurmo::t('ImportModule', 'Import Wizard - Import Data');
                $importRulesClassName    = ImportRulesUtil::getImportRulesClassNameByType($importWizardForm->importRulesType);
                $stepToUse = ImportStepsAndProgressBarForWizardView::resolveAfterUploadStepByImportClassName(5, $importRulesClassName);
                $progressBarAndStepsView = new ImportStepsAndProgressBarForWizardView($importRulesClassName, $stepToUse);
                $wrapperView  = new ImportSequentialProcessContainerView($resolvedView, $sequentialProcess->getAllStepsMessage(), $title);
                $wrapperView->setCssClasses(array('DetailsView'));
                $view = new ImportPageView(ZurmoDefaultAdminViewUtil::makeTwoStandardViewsForCurrentUser($this,
                                $progressBarAndStepsView, $wrapperView));
            }
            else
            {
                $view        = new AjaxPageView($resolvedView);
            }
            echo $view->render();
        }

        protected function makeImportCompleteView(Import $import, ImportWizardForm $importWizardForm, ImportDataProvider $dataProvider,
                                                  $setCurrentPageToFirst = false, $pageSize = null)
        {
            if ($pageSize == null)
            {
                $pageSize             = Yii::app()->pagination->resolveActiveForCurrentUserByType('listPageSize');
            }
            $config               = array('pagination' => array('pageSize' => $pageSize));
            $unserializedData     = unserialize($import->serializedData);

            $importCompleteView       = new ImportWizardCreateUpdateModelsCompleteView($this->getId(),
                                            $this->getModule()->getId(),
                                            $importWizardForm,
                                            $dataProvider,
                                            $unserializedData['mappingData'],
                                            (int)ImportRowDataResultsUtil::getCreatedCount($import->getTempTableName()),
                                            (int)ImportRowDataResultsUtil::getUpdatedCount($import->getTempTableName()),
                                            (int)ImportRowDataResultsUtil::getErrorCount($import->getTempTableName()));
            return $importCompleteView;
        }

        /**
         * Step 4 ajax process.  When you change the attribute dropdown, new mapping rule information is retrieved
         * and displayed in the user interface.
         */
        public function actionMappingRulesEdit($id, $attributeIndexOrDerivedType, $columnName, $columnType)
        {
            $import                                  = Import::getById((int)$_GET['id']);
            $importWizardForm                        = ImportWizardUtil::makeFormByImport($import);
            $importRulesClassName                    = ImportRulesUtil::
                                                       getImportRulesClassNameByType($importWizardForm->importRulesType);
            $mappableAttributeIndicesAndDerivedTypes = $importRulesClassName::
                                                       getMappableAttributeIndicesAndDerivedTypes();

            $mappingFormLayoutUtil                   = ImportToMappingFormLayoutUtil::make(
                                                       get_class($importWizardForm),
                                                       new ZurmoActiveForm(),
                                                       $importWizardForm->importRulesType,
                                                       $mappableAttributeIndicesAndDerivedTypes);

            $content                                 = $mappingFormLayoutUtil->renderMappingRulesElements(
                                                       $columnName,
                                                       $attributeIndexOrDerivedType,
                                                       $importWizardForm->importRulesType,
                                                       $columnType,
                                                       array());
            DropDownUtil::registerScripts(CClientScript::POS_END);
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        /**
         * Step 4 ajax process.  When you click the 'Add Field' button in the user interface, this ajax action
         * is called and makes an extra row to display for mapping.
         */
        public function actionMappingAddExtraMappingRow($id, $columnCount)
        {
            $import                                  = Import::getById((int)$_GET['id']);
            $importWizardForm                        = ImportWizardUtil::makeFormByImport($import);
            $importRulesClassName                    = ImportRulesUtil::
                                                       getImportRulesClassNameByType($importWizardForm->importRulesType);
            $mappableAttributeIndicesAndDerivedTypes = $importRulesClassName::
                                                       getMappableAttributeIndicesAndDerivedTypes();
            $extraColumnName                         = ImportMappingUtil::makeExtraColumnNameByColumnCount(
                                                       (int)$columnCount);
            $mappingDataMetadata                     = ImportWizardMappingViewUtil::
                                                       makeExtraColumnMappingDataForViewByColumnName($extraColumnName);
            $extraColumnView                         = new ImportWizardMappingExtraColumnView(
                                                       $importWizardForm,
                                                       $mappingDataMetadata,
                                                       $mappableAttributeIndicesAndDerivedTypes);
            $view                                    = new AjaxPageView($extraColumnView);
            echo $view->render();
        }

        public function actionSampleRow($id)
        {
            $import              = Import::getById((int)$_GET['id']);
            $importWizardForm    = ImportWizardUtil::makeFormByImport($import);
            $dataProvider        = $this->makeDataProviderForSampleRow($import,
                                   (bool)$importWizardForm->firstRowIsHeaderRow);
            $data                = $dataProvider->getData();
            $renderedContentData = array();
            $pagerUrl            = Yii::app()->createUrl('import/default/sampleRow', array('id' => $import->id));
            $headerContent       = ImportDataProviderPagerUtil::renderPagerAndHeaderTextContent($dataProvider, $pagerUrl);
            $renderedContentData[MappingFormLayoutUtil::getSampleColumnHeaderId()] = $headerContent;
            foreach ($data as $sampleColumnData)
            {
                foreach ($sampleColumnData as $columnName => $value)
                {
                    if (!in_array($columnName, ImportDatabaseUtil::getReservedColumnNames()))
                    {
                        $renderedContentData[MappingFormLayoutUtil::
                        resolveSampleColumnIdByColumnName($columnName)] = MappingFormLayoutUtil::
                                                                          renderChoppedStringContent($value);
                    }
                }
            }
            echo CJSON::encode($renderedContentData);
            Yii::app()->end(0, false);
        }

        /**
         * Ajax action called from user interface to upload an import file. If a file for this import model is
         * already uploaded, then this will overwrite it.
         * @param string $filesVariableName
         * @param string $id (should be integer, but php type casting doesn't work so well)
         */
        public function actionUploadFile($filesVariableName, $id)
        {
            assert('is_string($filesVariableName)');
            $import           = Import::getById((int)$id);
            $importWizardForm = ImportWizardUtil::makeFormByImport($import);
            $importWizardForm->setAttributes($_POST['ImportWizardForm']);
            if (!$importWizardForm->validateRowColumnDelimeterIsNotEmpty())
            {
                $fileUploadData = array('error' => Zurmo::t('ImportModule', 'Error: Invalid delimiter'));
            }
            elseif (!$importWizardForm->validateRowColumnEnclosureIsNotEmpty())
            {
                $fileUploadData = array('error' => Zurmo::t('ImportModule', 'Error: Invalid qualifier'));
            }
            else
            {
                try
                {
                    $uploadedFile = ImportUploadedFileUtil::getByNameCatchErrorAndEnsureFileIsACSV($filesVariableName);
                    assert('$uploadedFile instanceof CUploadedFile');
                    ImportUploadedFileUtil::convertWindowsAndMacLineEndingsIntoUnixLineEndings($uploadedFile->getTempName());
                    $fileHandle  = fopen($uploadedFile->getTempName(), 'r');
                    if ($fileHandle !== false)
                    {
                        $tempTableName = $import->getTempTableName();
                        try
                        {
                            $tableCreated = ImportDatabaseUtil::
                                            makeDatabaseTableByFileHandleAndTableName($fileHandle, $tempTableName,
                                                                                      $importWizardForm->rowColumnDelimiter,
                                                                                      $importWizardForm->rowColumnEnclosure);
                            if (!$tableCreated)
                            {
                                throw new FailedFileUploadException(Zurmo::t('ImportModule', 'Failed to create temporary database table from CSV.'));
                            }
                        }
                        catch (BulkInsertFailedException $e)
                        {
                            throw new FailedFileUploadException($e->getMessage());
                        }

                        $fileUploadData = array(
                            'name' => $uploadedFile->getName(),
                            'type' => $uploadedFile->getType(),
                            'size' => $uploadedFile->getSize(),
                        );
                        ImportWizardUtil::setFormByFileUploadDataAndTableName($importWizardForm, $fileUploadData,
                                                                              $tempTableName);
                        ImportWizardUtil::setImportSerializedDataFromForm($importWizardForm, $import);
                        if (!$import->save())
                        {
                            throw new FailedFileUploadException(Zurmo::t('ImportModule', 'Import model failed to save.'));
                        }
                    }
                    else
                    {
                        throw new FailedFileUploadException(Zurmo::t('ImportModule', 'Failed to open the uploaded file.'));
                    }
                    $fileUploadData['id']                = $import->id;
                }
                catch (FailedFileUploadException $e)
                {
                    $fileUploadData = array('error' => Zurmo::t('ImportModule', 'Error') . ' ' . $e->getMessage());
                    ImportWizardUtil::clearFileAndRelatedDataFromImport($import);
                }
            }
            echo CJSON::encode(array($fileUploadData));
            Yii::app()->end(0, false);
        }

        /**
         * Ajax action to delete an import file that was uploaded.  Will drop the temporary table created for the import.
         * @param string $id
         */
        public function actionDeleteFile($id)
        {
            $import = Import::getById((int)$id);
            ImportWizardUtil::clearFileAndRelatedDataFromImport($import);
        }

        /**
         * Generic method that is used by all steps to validate and saved the ImportWizardForm and Import model.
         * @param object $importWizardForm
         * @param object $import
         * @param string $redirectAction
         */
        protected function attemptToValidateImportWizardFormAndSave($importWizardForm, $import, $redirectAction)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('$import instanceof Import');
            assert('is_string($redirectAction)');
            if ($importWizardForm->validate())
            {
                ImportWizardUtil::setImportSerializedDataFromForm($importWizardForm, $import);
                if ($import->save())
                {
                    $this->redirect(array($this->getId() . '/' . $redirectAction, 'id' => $import->id));
                    Yii::app()->end(0, false);
                }
                else
                {
                    $messageView = new ErrorView(Zurmo::t('ImportModule', 'There was an error processing this import.'));
                    $view        = new ErrorPageView($messageView);
                    echo $view->render();
                    Yii::app()->end(0, false);
                }
            }
        }

        /**
         * @param Import $import
         * @param bool $firstRowIsHeaderRow
         * @return ImportDataProvider
         */
        protected function makeDataProviderForSampleRow($import, $firstRowIsHeaderRow)
        {
            assert('$import instanceof Import');
            assert('is_bool($firstRowIsHeaderRow)');
            $config = array('pagination' => array('pageSize' => 1));
            return    new ImportDataProvider($import->getTempTableName(), $firstRowIsHeaderRow, $config);
        }
    }
?>
