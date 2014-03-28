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

    class ContactWebFormEntry extends Item
    {
        const STATUS_SUCCESS          = 1;

        const STATUS_ERROR            = 2;

        const STATUS_SUCCESS_MESSAGE  = 'Success';

        const STATUS_ERROR_MESSAGE    = 'Error';

        const HASH_INDEX_HIDDEN_FIELD = 'hashIndex';

        /**
         * @param string $name
         * @return model
         */
        public static function getByName($name)
        {
            return ZurmoModelSearch::getModelsByFullName('ContactWebFormEntry', $name);
        }

        /**
         * @param $language
         * @return array
         */
        protected static function translatedAttributeLabels($language)
        {
            return parent::translatedAttributeLabels($language);
        }

        /**
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'ContactWebFormsModule';
        }

        /**
         * @param $language
         * @return string
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('ContactWebFormsModule', 'Contact Web Form Entry', array(), null, $language);
        }

        /**
         * @param $language
         * @return string, display name for plural of the model class.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('ContactWebFormsModule', 'Contact Web Form Entries', array(), null, $language);
        }

        /**
         * @return bool
         */
        public static function canSaveMetadata()
        {
            return true;
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'serializedData',
                    'status',
                    'message',
                    'hashIndex'
                ),
                'relations' => array(
                    'contact'            => array(static::HAS_ONE, 'Contact'),
                    'contactWebForm'     => array(static::HAS_ONE, 'ContactWebForm', static::NOT_OWNED,
                                                                    static::LINK_TYPE_SPECIFIC, 'entries'),
                ),
                'rules' => array(
                    array('serializedData',    'type', 'type' => 'string'),
                    array('status',            'type', 'type' => 'integer'),
                    array('message',           'type', 'type' => 'string'),
                    array('hashIndex',         'type', 'type' => 'string'),
                ),
                'elements' => array(
                ),
                'defaultSortAttribute' => 'status',
                'noAudit' => array('serializedData', 'hashIndex'),
            );
            return $metadata;
        }

        /**
         * @return bool
         */
        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * @return string
         */
        public static function getRollUpRulesType()
        {
            return 'ContactWebFormEntry';
        }

        /**
         * @return bool
         */
        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        /**
         * @param string $hashIndex row identifier for ContactWebFormEntry
         * @return array of module class names and display labels.
         */
        public static function getByHashIndex($hashIndex)
        {
            $modelClassName = get_called_class();
            $tableName      = $modelClassName::getTableName();
            $columnName     = self::getColumnNameByAttribute('hashIndex');
            $beans          = ZurmoRedBean::find($tableName, "$columnName = '$hashIndex'");
            assert('count($beans) <= 1');
            if (count($beans) == 0)
            {
                return null;
            }
            else
            {
                return static::makeModel(end($beans), $modelClassName);
            }
        }
    }
?>