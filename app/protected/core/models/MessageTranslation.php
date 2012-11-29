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

    class MessageTranslation extends RedBeanModel
    {
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'translation',
                    'language',
                ),
                'relations' => array(
                    'messagesource'   => array(
                                               RedBeanModel::HAS_ONE,
                                               'MessageSource',
                                               RedBeanModel::OWNED
                                               ),
                ),
                'rules' => array(
                    array('translation',        'required'),
                    array('translation',        'type', 'type' => 'blob'),
                    array('language',           'required'),
                    array('language',           'type', 'type' => 'string'),
                    array('language',           'length',  'min'  => 1, 'max' => 255),
                ),
                'elements' => array(
                    'messagesource' => 'MessageSource',
                )
            );
            return $metadata;
        }

        /**
         * Gets a model from the database by source message id and langcode
         * @param $sourceId Integer Id of the source message
         * @param $langCode String Language code of the translation
         * @param $modelClassName Pass only when getting it at runtime
         *                        gets the wrong name.
         * @return A model of the type of the extending model.
         */
        public static function getBySourceIdAndLangCode($sourceId, $langCode, $modelClassName = null)
        {
            assert('!intval($sourceId) && $sourceId > 0');
            assert('!empty($langCode)');
            assert('$modelClassName === null || is_string($modelClassName) && $modelClassName != ""');
            if ($modelClassName === null)
            {
                $modelClassName = get_called_class();
            }
            $tableName = self::getTableName($modelClassName);
            $bean = R::findOne(
                               $tableName,
                               ' messagesource_id = :source_id AND language = :langCode',
                               array(
                                     ':source_id'=>$sourceId,
                                     ':language'=>$langCode
                                     )
                               );
            assert('is_object($bean)');
            if (!is_object($bean)) {
                throw new NotFoundException();
            }
            return RedBeanModel::makeModel($bean, $modelClassName);
        }
    }
?>
