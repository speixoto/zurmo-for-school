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

    class Contact extends Person
    {
        public static function getByName($name)
        {
            return ZurmoModelSearch::getModelsByFullName('Contact', $name);
        }

        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(),
                array(
                    'state'         => 'Status',
                    'account'       => 'AccountsModuleSingularLabel',
                    'opportunities' => 'OpportunitiesModulePluralLabel'
                )
            );
        }

        public static function getModuleClassName()
        {
            return 'ContactsModule';
        }

        /**
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel()
        {
            return 'ContactsModuleSingularLabel';
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel()
        {
            return 'ContactsModulePluralLabel';
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
                    'companyName',
                    'description',
                    'website',
                ),
                'relations' => array(
                    'account'          => array(RedBeanModel::HAS_ONE,   'Account'),
                    'industry'         => array(RedBeanModel::HAS_ONE,   'OwnedCustomField', RedBeanModel::OWNED),
                    'opportunities'    => array(RedBeanModel::MANY_MANY, 'Opportunity'),
                    'secondaryAddress' => array(RedBeanModel::HAS_ONE,   'Address',          RedBeanModel::OWNED),
                    'secondaryEmail'   => array(RedBeanModel::HAS_ONE,   'Email',            RedBeanModel::OWNED),
                    'source'           => array(RedBeanModel::HAS_ONE,   'OwnedCustomField', RedBeanModel::OWNED),
                    'state'            => array(RedBeanModel::HAS_ONE,   'ContactState'),
                ),
                'rules' => array(
                    array('companyName',      'type',    'type' => 'string'),
                    array('companyName',      'length',  'min'  => 3, 'max' => 64),
                    array('description',      'type',    'type' => 'string'),
                    array('state',            'required'),
                    array('website',          'url'),
                ),
                'elements' => array(
                    'account'          => 'Account',
                    'description'      => 'TextArea',
                    'secondaryEmail'   => 'EmailAddressInformation',
                    'secondaryAddress' => 'Address',
                    'state'            => 'ContactState',
                ),
                'customFields' => array(
                    'industry' => 'Industries',
                    'source'   => 'LeadSources',
                ),
                'defaultSortAttribute' => 'lastName',
                'rollupRelations' => array(
                    'opportunities',
                ),
                'noAudit' => array(
                    'description',
                    'website'
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getRollUpRulesType()
        {
            return 'Contact';
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            return 'ContactGamification';
        }
    }
?>
