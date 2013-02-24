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

    class Product extends OwnedSecurableItem
    {
        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(),
                array(
                    'productTemplate' => 'ProductTemplatesModuleSingularLabel',
                    'contact'         => 'ContactsModuleSingularLabel',
                    'account'         => 'AccountsModuleSingularLabel',
                    'opportunity'     => 'OpportunitiesModuleSingularLabel',
                )
            );
        }

        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('ProductsModule', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public static function getModuleClassName()
        {
            return 'ProductsModule';
        }

        /**
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel()
        {
            return 'ProductsModuleSingularLabel';
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel()
        {
            return 'ProductsModulePluralLabel';
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
                    'name',
                    'description',
                    'quantity',
                ),
                'relations' => array(
                    'account'         => array(RedBeanModel::HAS_ONE, 'Account'),
                    'contact'         => array(RedBeanModel::HAS_ONE, 'Contact'),
                    'opportunity'     => array(RedBeanModel::HAS_ONE, 'Opportunity'),
                    'stage'           => array(RedBeanModel::HAS_ONE, 'OwnedCustomField', RedBeanModel::OWNED),
                ),
                'rules' => array(
                    array('name',         'required'),
                    array('name',         'type',    'type' => 'string'),
                    array('name',         'length',  'min'  => 3, 'max' => 64),
                    array('description',  'type',    'type' => 'string'),
                    array('quantity',     'type',    'type' => 'integer'),
                    array('stage',        'required'),
                ),
                'elements' => array(
                    'description'    => 'TextArea',
                    'account'        => 'Account',
                ),
                'customFields' => array(
                    'stage'    => 'ProductStages',
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            //return 'ProductGamification';
        }
    }
?>