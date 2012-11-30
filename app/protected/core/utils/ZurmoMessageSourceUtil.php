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
     * Utility class for importing messages to the database
     */
    class ZurmoMessageSourceUtil
    {
        /**
         * Imports one message string to the database
         *
         * @param String $langCode The language code
         * @param String $category The category of the translation
         * @param String $source Message source
         * @param String $translation Message translation
         *
         * @return Integer Id of the added translation
         */
        public static function importOneMessage($langCode, $category, $source, $translation)
        {
            assert('is_string($langCode) && !empty($langCode)');
            assert('is_string($category) && !empty($category)');
            assert('is_string($source) && !empty($source)');
            assert('is_string($translation) && !empty($translation)');
            if (
                !is_string($langCode) || empty($langCode) ||
                !is_string($category) || empty($category) ||
                !is_string($source) || empty($source) ||
                !is_string($translation) || empty($translation)
                )
            {
                return false;
            }

            try {
                $sourceModel = MessageSource::getByCategoryAndSource(
                                                                     $category,
                                                                     $source
                                                                    );
            } catch (NotFoundException $e) {
                $sourceModel = MessageSource::addNewSource($category, $source);
            }

            try {
                $translationModel = MessageTranslation::getBySourceIdAndLangCode(
                                        $sourceModel->id,
                                        $langCode
                                    );
                $translationModel->updateTranslation($translation);
            } catch (NotFoundException $e) {
                MessageTranslation::addNewTranslation(
                                                      $langCode,
                                                      $sourceModel,
                                                      $translation
                                                      );
            }

            return true;
        }

        /**
         * Imports messages array to the database
         *
         * @param $langCode String The language code
         * @param $category String The category of the translation
         * @param Array 
         */
        public static function importMessagesArray($langCode, $category, $messages)
        {
            
            assert('is_string($langCode) && !empty($langCode)');
            assert('is_string($category) && !empty($category)');
            assert('is_array($messages) && !empty($messages)');
            if (
                !is_string($langCode) || empty($langCode) ||
                !is_string($category) || empty($category) ||
                !is_array($messages) || empty($messages)
                )
            {
                return false;
            }

            foreach ($messages as $source=>$translation) {
                self::importOneMessage(
                                       $langCode,
                                       $category,
                                       $source,
                                       $translation
                                       );
            }
        }
    }
?>