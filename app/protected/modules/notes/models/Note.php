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

    class Note extends MashableActivity
    {
        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('description', $name);
        }

        public function __toString()
        {
            try
            {
                $description  = trim($this->description);
                if ($description == '')
                {
                    $description = Yii::t('Default', '(Unnamed)');
                }
                return $description;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public static function getModuleClassName()
        {
            return 'NotesModule';
        }

        /**
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel()
        {
            return 'NotesModuleSingularLabel';
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel()
        {
            return 'NotesModulePluralLabel';
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'description',
                    'occurredOnDateTime',
                ),
                'relations' => array(
                    'files'       => array(RedBeanModel::HAS_MANY,  'FileModel', RedBeanModel::OWNED, 'relatedModel'),
                    'socialItems' => array(RedBeanModel::HAS_MANY,  'SocialItem', RedBeanModel::OWNED),
                ),
                'rules' => array(
                    array('description',        'required'),
                    array('description',        'type',   'type' => 'string'),
                    array('occurredOnDateTime', 'type', 'type' => 'datetime'),
                    array('occurredOnDateTime', 'dateTimeDefault', 'value' => DateTimeCalculatorUtil::NOW),
                    ),
                'elements' => array(
                    'description'        => 'TextArea',
                    'files'              => 'Files',
                    'occurredOnDateTime' => 'DateTime'
                ),
                'defaultSortAttribute' => 'occurredOnDateTime',
            );
            return $metadata;
        }

        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(),
                array(
                    'occurredOnDateTime'       => 'Occurred On',
                )
            );
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if (array_key_exists('occurredOnDateTime', $this->originalAttributeValues) &&
                    $this->occurredOnDateTime != null)
                {
                    $this->unrestrictedSet('latestDateTime', $this->occurredOnDateTime);
                }
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function getMashableActivityRulesType()
        {
            return 'Note';
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            return 'NoteGamification';
        }
    }
?>
