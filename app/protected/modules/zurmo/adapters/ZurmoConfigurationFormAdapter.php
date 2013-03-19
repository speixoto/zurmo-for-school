<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * Class to adapt global configuration values into a configuration form.
     * Saves global values from a configuration form.
     */
    class ZurmoConfigurationFormAdapter
    {
        /**
         * @return ZurmoConfigurationForm
         */
        public static function makeFormFromGlobalConfiguration()
        {
            $form                                        = new ZurmoConfigurationForm();
            $form->applicationName                       = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'applicationName');
            $form->timeZone                              = Yii::app()->timeZoneHelper->getGlobalValue();
            $form->listPageSize                          = Yii::app()->pagination->getGlobalValueByType('listPageSize');
            $form->subListPageSize                       = Yii::app()->pagination->getGlobalValueByType('subListPageSize');
            $form->modalListPageSize                     = Yii::app()->pagination->getGlobalValueByType('modalListPageSize');
            $form->dashboardListPageSize                 = Yii::app()->pagination->getGlobalValueByType('dashboardListPageSize');
            $form->gamificationModalNotificationsEnabled = Yii::app()->gameHelper->modalNotificationsEnabled;
            $form->realtimeUpdatesEnabled                = static::getRealtimeUpdatesEnabled();
            self::getLogoAttributes($form);
            return $form;
        }

        /**
         * Given a ZurmoConfigurationForm, save the configuration global values.
         */
        public static function setConfigurationFromForm(ZurmoConfigurationForm $form)
        {
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'applicationName', $form->applicationName);
            Yii::app()->timeZoneHelper  ->setGlobalValue(                         (string)$form->timeZone);
            Yii::app()->pagination->setGlobalValueByType('listPageSize',          (int)   $form->listPageSize);
            Yii::app()->pagination->setGlobalValueByType('subListPageSize',       (int)   $form->subListPageSize);
            Yii::app()->pagination->setGlobalValueByType('modalListPageSize',     (int)   $form->modalListPageSize);
            Yii::app()->pagination->setGlobalValueByType('dashboardListPageSize', (int)   $form->dashboardListPageSize);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule',
                                                    'gamificationModalNotificationsEnabled',
                                                    (boolean) $form->gamificationModalNotificationsEnabled);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule',
                                                    'realtimeUpdatesEnabled',
                                                    (boolean) $form->realtimeUpdatesEnabled);
            self::setLogoAttributes($form);
        }

        public static function getRealtimeUpdatesEnabled()
        {
            if (ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'realtimeUpdatesEnabled') !== null)
            {
                return ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'realtimeUpdatesEnabled');
            }
            else
            {
                return false;
            }
        }

        public static function getLogoAttributes(& $form)
        {
           if (!is_null(ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoThumbFileModelId')))
           {
               $logoThumbFileId  = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoThumbFileModelId');
               $logoThumbFileSrc = Yii::app()->createUrl('zurmo/default/logo', array('id'=>$logoThumbFileId));
               $logoThumbFile    = FileModel::getById($logoThumbFileId);
               $logoFileData     = array('name'              => $logoThumbFile->name,
                                         'type'              => $logoThumbFile->type,
                                         'size'              => (int) $logoThumbFile->size,
                                         'thumbnail_url'     => $logoThumbFileSrc);
           }
           else
           {
               $logoThumbFilePath = Yii::app()->theme->basePath . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Zurmo_logo.png';
               $logoThumbFileSrc  = Yii::app()->baseUrl.'/themes/default/images/Zurmo_logo.png';
               $logoFileData      = array('name'              => pathinfo($logoThumbFilePath, PATHINFO_FILENAME),
                                          'type'              => ZurmoFileHelper::getMimeType($logoThumbFilePath),
                                          'size'              => filesize($logoThumbFilePath),
                                          'thumbnail_url'     => $logoThumbFileSrc);
           }
           $form->logoFileData  = $logoFileData;
           $form->logoHeight    = self::resolveLogoHeight();
           $form->logoWidth     = self::resolveLogoWidth();
        }

        public static function setLogoAttributes($form)
        {
           ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoHeight', $form->logoHeight);
           ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoWidth', $form->logoWidth);
           if (Yii::app()->user->getState('deleteCustomLogo') === true)
           {
               if (ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoFileModelId') !== null)
               {
                   self::deleteCurrentCustomLogo();
                   ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoFileModelId', null);
                   ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoThumbFileModelId', null);
                   Yii::app()->user->setState('deleteCustomLogo', null);
               }
           }
           if (!is_null(Yii::app()->user->getState('logoFileName')))
           {
               $logoFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . Yii::app()->user->getState('logoFileName');
               self::resizeLogoImageFile($logoFilePath, $logoFilePath, $form->logoWidth, $form->logoHeight);
               $logoFileName = Yii::app()->user->getState('logoFileName');
               $logoFileId   = self::saveLogoFile($logoFileName, $logoFilePath, 'logoFileModelId');
               self::publishLogo($logoFileName, $logoFilePath);
               self::deleteCurrentCustomLogo();
               ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoFileModelId', $logoFileId);
               $thumbFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . ZurmoConfigurationForm::LOGO_THUMB_FILE_NAME_PREFIX . $logoFileName;
               $thumbFileId = self::saveLogoFile($logoFileName, $thumbFilePath, 'logoThumbFileModelId');
               ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoThumbFileModelId', $thumbFileId);
               Yii::app()->user->setState('logoFileName', null);
           }
        }

        public static function resolveLogoWidth()
        {
           if(!($logoWidth = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoWidth')))
           {
               $logoWidth = ZurmoConfigurationForm::DEFAULT_LOGO_WIDTH;
           }
           return $logoWidth;
        }

        public static function resolveLogoHeight()
        {
           if(!($logoHeight = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoHeight')))
           {
               $logoHeight = ZurmoConfigurationForm::DEFAULT_LOGO_HEIGHT;
           }
           return $logoHeight;
        }

        public static function saveLogoFile($fileName, $filePath, $fileModelIdentifier)
        {
           if (ZurmoConfigurationUtil::getByModuleName('ZurmoModule', $fileModelIdentifier) !== null)
           {
               $fileModelId          = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', $fileModelIdentifier);
               $file                 = FileModel::getById($fileModelId);
               $fileContent          = FileContent::getById($file->fileContent->id);
               $contents             = file_get_contents($filePath);
               $fileContent->content = $contents;
               $file->fileContent    = $fileContent;
               $file->name           = $fileName;
               $file->type           = ZurmoFileHelper::getMimeType($filePath);
               $file->size           = filesize($filePath);
               $saved                = $file->save();
               return $file->id;
           }
           else
           {
               $contents             = file_get_contents($filePath);
               $fileContent          = new FileContent();
               $fileContent->content = $contents;
               $file                 = new FileModel();
               $file->fileContent    = $fileContent;
               $file->name           = $fileName;
               $file->type           = ZurmoFileHelper::getMimeType($filePath);
               $file->size           = filesize($filePath);
               $saved                = $file->save();

               return $file->id;
           }
        }

        public static function publishLogo($logoFileName, $logoFilePath)
        {
            if (!is_dir(Yii::getPathOfAlias('application.runtime.uploads')))
            {
                mkdir(Yii::getPathOfAlias('application.runtime.uploads'), 0755, true); // set recursive flag and permissions 0755
            }
            copy($logoFilePath, Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR . $logoFileName);
            Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR . $logoFileName);
        }

        public static function deleteCurrentCustomLogo()
        {
            if ($logoFileModelId = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoFileModelId'))
            {
                //Get path of currently uploaded logo, required to delete/unlink legacy logo from runtime/uploads
                $logoFileModel       = FileModel::getById($logoFileModelId);
                $currentLogoFileName = $logoFileModel->name;
                $currentLogoFilePath = Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR . $currentLogoFileName;
                if (file_exists($currentLogoFilePath))
                {
                    unlink($currentLogoFilePath);
                }
            }
        }

        public static function resizeLogoImageFile($sourcePath, $destinationPath, $newWidth, $newHeight)
        {
            WideImage::load($sourcePath)->resize($newWidth, $newHeight)->saveToFile($destinationPath);
        }
    }
?>