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
        public function actionUpload()
        {
            $uploadedFile = UploadedFileUtil::getByNameAndCatchError('file');
            $tempFilePath = $uploadedFile->getTempName();
            $fileContent  = new FileContent();
            $fileContent->content = file_get_contents($tempFilePath);
            list($width, $height, $type, $attr) = getimagesize($tempFilePath);
            $imageFileModel = new ImageFileModel();
            $imageFileModel->name        = $uploadedFile->getName();
            $imageFileModel->size        = $uploadedFile->getSize();
            $imageFileModel->type        = $uploadedFile->getType();
            $imageFileModel->width       = $width;
            $imageFileModel->height      = $height;
            $imageFileModel->fileContent = $fileContent;
            if ($imageFileModel->save())
            {
                $imageFileModel->createImageCache();
                $array = array(
                    'filelink' => $this->createAbsoluteUrl('imageModel/getImage',
                            array('fileName' => $imageFileModel->getImageCacheFileName()))
                );
                echo CJSON::encode($array);
            }
            else
            {
                $message = Zurmo::t('ZurmoModule', 'Error uploading the image');
                $fileUploadData = array('error' => $message);
                echo CJSON::encode($fileUploadData);
            }
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
            $imageFileModel->delete();
        }

        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                $_GET['modalTransferInformation']['sourceIdFieldId'],
                $_GET['modalTransferInformation']['sourceNameFieldId'],
                $_GET['modalTransferInformation']['modalId']
            );
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
            $dataProvider      = $this->makeRedBeanDataProviderByDataCollection(
                $searchModel,
                $pageSize,
                $stateMetadataAdapterClassName);
//            $searchAndListView = new $className(
//                $controller->getId(),
//                $controller->getModule()->getId(),
//                $controller->getAction()->getId(),
//                $modalListLinkProvider,
//                $searchModel,
//                $model,
//                $dataProvider,
//                'modal'
//            );
            $searchAndListView = new ImagesModalListView($this->id, $this->module->id, 'modalList', $modelClassName, $modalListLinkProvider, $dataProvider, 'modal');
            $view = new ModalView($this, $searchAndListView);
            echo $view->render();
        }
    }
?>