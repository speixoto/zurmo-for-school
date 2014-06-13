<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class ZurmoImageModelController extends ZurmoModuleController
    {
        public function actionUploadFromUrl()
        {
            $form = new ImportImageFromUrlForm();
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'image-import-form')
            {
                $errors = ZurmoActiveForm::validate($form);
                if ($form->hasErrors())
                {
                    echo $errors;
                    Yii::app()->end();
                }
            }
            if(isset($_POST['ImportImageFromUrlForm']))
            {
                $url = $_POST['ImportImageFromUrlForm']['url'];
                $tempFilePath = tempnam(sys_get_temp_dir(), 'upload_image_from_url_');
                $name = preg_replace("#.*\/#", '', $url);
                file_put_contents($tempFilePath, file_get_contents($url));
                $fileUploadData = $this->saveImageFromTemporaryFile($tempFilePath, $name);
                if (isset($fileUploadData['error']))
                {
                    $result[CHtml::activeId($form, 'url')] = array($fileUploadData['error']);
                    echo CJSON::encode($result);
                    Yii::app()->end();
                }
                else
                {
                    echo CJSON::encode($fileUploadData);
                }
            }
        }

        public function actionUpload()
        {
            $uploadedFile   = UploadedFileUtil::getByNameAndCatchError('file');
            $tempFilePath   = $uploadedFile->getTempName();
            $fileUploadData = $this->saveImageFromTemporaryFile($tempFilePath,
                                                                $uploadedFile->getName());
            echo CJSON::encode(array($fileUploadData));
        }

        protected function saveImageFromTemporaryFile($tempFilePath, $name)
        {
            $fileContent  = new FileContent();
            $fileContent->content = file_get_contents($tempFilePath);
            $imageProperties = getimagesize($tempFilePath);
            $imageFileModel  = new ImageFileModel();
            $imageFileModel->name        = $name;
            $imageFileModel->size        = filesize($tempFilePath);
            $imageFileModel->type        = $imageProperties['mime'];
            $imageFileModel->width       = $imageProperties[0];
            $imageFileModel->height      = $imageProperties[1];
            $imageFileModel->fileContent = $fileContent;
            if ($imageFileModel->save())
            {
                $imageFileModel->createImageCache();
                $fileUploadData = array(
                    'id'   => $imageFileModel->id,
                    'name' => $imageFileModel->name,
                    'summary' => ImageFileModelUtil::getImageSummary($imageFileModel),
                    'size' => FileModelDisplayUtil::convertSizeToHumanReadableAndGet($imageFileModel->size),
                    'thumbnail_url' => $this->createAbsoluteUrl('imageModel/getThumb',
                            array('fileName' => $imageFileModel->getImageCacheFileName())),
                    'filelink' => $this->createAbsoluteUrl('imageModel/getImage',
                            array('fileName' => $imageFileModel->getImageCacheFileName())),
                    'insert_link' => $this->resolveInsertLink($imageFileModel),
                );
            }
            else
            {
                $message = Zurmo::t('ZurmoModule', 'Error uploading the image');
                $fileUploadData = array('error' => $message);
            }
            return $fileUploadData;
        }

        protected function resolveInsertLink($imageFileModel)
        {
            $summary = ImageFileModelUtil::getImageSummary($imageFileModel);
            return "javascript:parent.transferModalImageValues({$imageFileModel->id}, '{$summary}')";
        }

        public function actionGetUploaded()
        {
            $array = array();
            $imageFileModels = ImageFileModel::getAll();
            foreach ($imageFileModels as $imageFileModel)
            {
                $array[] = array('thumb' => $this->createAbsoluteUrl('imageModel/getThumb',
                                                array('fileName' => $imageFileModel->getImageCacheFileName())),
                                 'image' => $this->createAbsoluteUrl('imageModel/getImage',
                                                array('fileName' => $imageFileModel->getImageCacheFileName())));
            }
            echo stripslashes(json_encode($array));
        }

        public function actionGetImage($fileName)
        {
            assert('is_string($fileName)');
            ImageFileModelUtil::readImageFromCache($fileName, false);
        }

        public function actionGetThumb($fileName)
        {
            assert('is_string($fileName)');
            ImageFileModelUtil::readImageFromCache($fileName, true);
        }

        public function actionDelete($id)
        {
            $imageFileModel = ImageFileModel::getById((int)$id);
            if (!$imageFileModel->canDelete())
            {
                throw new NotSupportedException();
            }
            $imageFileModel->delete();
        }

        public function actionModalList()
        {
            $getData = GetUtil::getData();
            $modalTransferInformation = ArrayUtil::getArrayValue($getData, 'modalTransferInformation');
            if ($modalTransferInformation != null)
            {
                $modalListLinkProvider = new ImageSelectFromRelatedEditModalListLinkProvider(
                    $modalTransferInformation['sourceIdFieldId'],
                    $modalTransferInformation['sourceNameFieldId'],
                    $modalTransferInformation['modalId']
                );
            }
            else
            {
                $modalListLinkProvider = new ImageSelectFromRelatedEditModalListLinkProvider(null, null);
            }
            Yii::app()->getClientScript()->setToAjaxMode();
            $className           = 'ImageModalSearchAndListView';
            $modelClassName      = 'ImageFileModel';
            $stateMetadataAdapterClassName = null;
            $searchViewClassName = $className::getSearchViewClassName();
            if ($searchViewClassName::getModelForMetadataClassName() != null)
            {
                $formModelClassName   = $searchViewClassName::getModelForMetadataClassName();
                $model                = new $modelClassName(false);
                $searchModel          = new $formModelClassName($model);
            }
            else
            {
                throw new NotSupportedException();
            }
            $pageSize          = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                'modalListPageSize', get_class($this->getModule()));

            $dataProvider = $this->resolveSearchDataProvider(
                $searchModel,
                $pageSize,
                $stateMetadataAdapterClassName,
                'ImagesSearchView'
            );

            $imageModalSearchAndListAndUploadView = new ImageModalSearchAndListAndUploadView(
                                                            $this,
                                                            $this->module->id,
                                                            'modalList',
                                                            $modalListLinkProvider,
                                                            $searchModel,
                                                            $model,
                                                            $dataProvider,
                                                            'modal'
                                                        );
            $view = new ModalView($this, $imageModalSearchAndListAndUploadView);
            echo $view->render();
        }

        public function actionModalPreview($fileName)
        {
            Yii::app()->getClientScript()->setToAjaxMode();
            $imageModalPreview = new ImagePreviewView($fileName);
            echo $imageModalPreview->render();
        }

        protected function resolveFilteredByMetadataBeforeMakingDataProvider($searchForm, & $metadata)
        {
            $userId = Yii::app()->user->userModel->id;
            if ($searchForm->filteredBy == ImagesSearchForm::FILTERED_BY_I_CREATED)
            {
                $clauseNumber = count($metadata['clauses']) + 1;
                $metadata['clauses'][$clauseNumber] = array('attributeName' => 'createdByUser',
                                                            'operatorType'  => 'equals',
                                                            'value'         => $userId);
                if ($metadata['structure'] == '')
                {
                    $metadata['structure'] = '(' . $clauseNumber . ')';
                }
                else
                {
                    $metadata['structure'] .= ' AND (' . $clauseNumber . ')';
                }
            }
            elseif ($searchForm->filteredBy == ImagesSearchForm::FILTERED_BY_SHARED)
            {
                $clauseNumber = count($metadata['clauses']) + 1;
                $metadata['clauses'][$clauseNumber] = array('attributeName' => 'createdByUser',
                                                            'operatorType'  => 'doesNotEqual',
                                                            'value'         => $userId);
                $metadata['clauses'][$clauseNumber + 1] = array('attributeName' => 'isShared',
                                                                'operatorType'  => 'equals',
                                                                'value'         => true);
                if ($metadata['structure'] == '')
                {
                    $metadata['structure'] = '(' . $clauseNumber . ' AND ' . ($clauseNumber + 1) . ')';
                }
                else
                {
                    $metadata['structure'] .= ' AND (' . $clauseNumber . ' AND ' . ($clauseNumber + 1) . ')';
                }
            }
            else
            {
                $clauseNumber = count($metadata['clauses']) + 1;
                $metadata['clauses'][$clauseNumber] = array('attributeName' => 'createdByUser',
                                                            'operatorType'  => 'equals',
                                                            'value'         => $userId);
                $metadata['clauses'][$clauseNumber + 1] = array('attributeName' => 'isShared',
                                                                'operatorType'  => 'equals',
                                                                'value'         => 1);
                if ($metadata['structure'] == '')
                {
                    $metadata['structure'] = '(' . $clauseNumber . ' OR ' . ($clauseNumber + 1) . ')';
                }
                else
                {
                    $metadata['structure'] .= ' AND (' . $clauseNumber . ' OR ' . ($clauseNumber + 1) . ')';
                }
            }
        }

        public function actionToggle($id, $attribute)
        {
            if (Yii::app()->request->isAjaxRequest && Yii::app()->request->isPostRequest)
            {
                $imageFile = ImageFileModel::getById((int) $id);
                $imageFile->toggle($attribute);
            }
        }
    }
?>