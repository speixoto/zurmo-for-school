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

    class WorkflowModelTestItem9 extends OwnedSecurableItem
    {
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                ),
                'relations' => array(
                    'dropDown'       => array(RedBeanModel::HAS_ONE,   'OwnedCustomField', RedBeanModel::OWNED,
                                                       RedBeanModel::LINK_TYPE_SPECIFIC, 'dropDown'),
                    'hasOne'         => array(RedBeanModel::HAS_ONE,   'WorkflowModelTestItem', RedBeanModel::NOT_OWNED,
                                                       RedBeanModel::LINK_TYPE_SPECIFIC, 'hasOne'),
                    'hasOne2'        => array(RedBeanModel::HAS_ONE,   'WorkflowModelTestItem8', RedBeanModel::NOT_OWNED,
                                        RedBeanModel::LINK_TYPE_SPECIFIC, 'hasOne2'),
                    'hasMany'        => array(RedBeanModel::HAS_MANY,   'WorkflowModelTestItem', RedBeanModel::NOT_OWNED,
                                                       RedBeanModel::LINK_TYPE_SPECIFIC, 'hasMany'),
                    'manyMany'       => array(RedBeanModel::MANY_MANY,   'WorkflowModelTestItem', RedBeanModel::NOT_OWNED,
                                            RedBeanModel::LINK_TYPE_SPECIFIC, 'manyMany'),
                    'workflowModelTestItem9'          => array(RedBeanModel::HAS_MANY_BELONGS_TO,  'WorkflowModelTestItem9'),
                    'workflowModelTestItem9s'         => array(RedBeanModel::HAS_MANY,             'WorkflowModelTestItem9'),
                    'hasOne'                          => array(RedBeanModel::HAS_ONE,             'WorkflowModelTestItem'),
                    'hasOne2'                         => array(RedBeanModel::HAS_ONE,             'WorkflowModelTestItem5'),
                ),
                'rules' => array(
                    array('name',  'type',   'type' => 'string'),
                    array('name',  'length', 'max' => 32),
                ),
                'customFields' => array(
                    'dropDown'        => 'WorkflowTestDropDown',
                ),
                'elements' => array(
                    'dropDown'            => 'DropDown',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'WorkflowsTestModule';
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return 'WorkflowModelTestItem9';
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return 'WorkflowModelTestItem9s';
        }
    }
?>
