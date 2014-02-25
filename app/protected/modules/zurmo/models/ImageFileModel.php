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

    /**
     * Class ImageFileModel
     * Used to store public accessible images
     */
    class ImageFileModel extends FileModel
    {
        const DEFAULT_THUMBNAIL_HEIGHT      = 30;
        const DEFAULT_THUMBNAIL_WIDTH       = 65;
        const THUMB_FILE_NAME_PREFIX   = 'thumb_';

        public static function getByFileName($fileName)
        {
            assert('is_string($fileName)');
            $matches = array();
            preg_match('/^(\d+)_/', $fileName, $matches);
            if (count($matches) == 2)
            {
                $id = $matches[1];
                return static::getById((int) $id);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function getImageCachePath($isThumbnail = false)
        {
            //TODO: @sergio: Add test
            if ($isThumbnail)
            {
                return static::getPathToCachedFiles() . static::THUMB_FILE_NAME_PREFIX . $this->getImageCacheFileName();
            }
            return static::getPathToCachedFiles() . $this->getImageCacheFileName();
        }

        public static function getImageCachePathByFileName($fileName, $isThumb)
        {
            assert('is_string($fileName)');
            if ($isThumb)
            {
                $fileName = static::THUMB_FILE_NAME_PREFIX . $fileName;
            }
            return static::getPathToCachedFiles() . $fileName;
        }

        protected static function getPathToCachedFiles()
        {
            return Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR;
        }

        public function getImageCacheFileName()
        {
            return $this->id . '_' . $this->name;
        }

        public function createImageCache($isThumbnail = false)
        {
            $imageCachePath = $this->getImageCachePath($isThumbnail);
            $this->createCacheDirIfNotExists();
            if (!$this->isImageCached($imageCachePath))
            {
                $this->cacheImage($imageCachePath, $isThumbnail);
            }
        }

        protected function createCacheDirIfNotExists()
        {
            if (!is_dir(Yii::getPathOfAlias('application.runtime.uploads')))
            {
                mkdir(Yii::getPathOfAlias('application.runtime.uploads'), 0755, true); // set recursive flag and permissions 0755
            }
        }

        protected function isImageCached($imageCachePath)
        {
            return file_exists($imageCachePath);
        }

        protected function cacheImage($imageCachePath, $isThumbnail)
        {
            if ($isThumbnail)
            {
                $newWidth  = static::DEFAULT_THUMBNAIL_WIDTH;
                $newHeight = static::DEFAULT_THUMBNAIL_HEIGHT;
                WideImage::load($this->fileContent->content)->resize($newWidth, $newHeight)->saveToFile($imageCachePath);
            }
            else
            {
                file_put_contents($imageCachePath, $this->fileContent->content);
            }
        }
    }
?>