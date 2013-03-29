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

    class SavedWorkflow extends Item
    {
        public static function getByName($name)
        {
            assert('is_string($name) && $name != ""');
            return self::getSubset(null, null, null, "name = '$name'");
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
                    'isActive',
                    'moduleClassName',
                    'name',
                    'order',
                    'serializedData',
                    'triggerOn',
                    'type'
                ),
                'rules' => array(
                    array('description',         'type',   'type' => 'string'),
                    array('isActive',            'boolean'),
                    array('moduleClassName',     'required'),
                    array('moduleClassName',     'type',   'type' => 'string'),
                    array('moduleClassName',     'length', 'max'  => 64),
                    array('name',                'required'),
                    array('name',                'type',   'type' => 'string'),
                    array('name',                'length', 'max'  => 64),
                    array('order',               'type',    'type' => 'integer'),
                    array('serializedData',      'required'),
                    array('serializedData',      'type', 'type' => 'string'),
                    array('type',       		 'required'),
                    array('type',       		 'type',   'type' => 'string'),
                    array('type',       		 'length', 'max'  => 15),
                    array('triggerOn',       	 'required'),
                    array('triggerOn',       	 'type',   'type' => 'string'),
                    array('triggerOn',       	 'length', 'max'  => 15),
                ),
                'elements' => array(
                    'triggerOn'       => 'TriggerOnStaticDropDown',
                    'type'            => 'WorkflowTypeStaticDropDown',
                    'moduleClassName' => 'ModuleForWorkflowStaticDropDown',
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'WorkflowsModule';
        }

        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Yii::t('Default', '(Unnamed)');
            }
            return $this->name;
        }

        public static function getActiveByModuleClassNameAndIsNewModel($moduleClassName, $isNewModel)
        {
            assert('is_string($moduleClassName)');
            assert('is_bool($isNewModel)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'moduleClassName',
                    'operatorType'         => 'equals',
                    'value'                => $moduleClassName,
                ),
                2 => array(
                    'attributeName'        => 'isActive',
                    'operatorType'         => 'equals',
                    'value'                => true,
                ),
                3 => array(
                    'attributeName'        => 'triggerOn',
                    'operatorType'         => 'equals',
                    'value'                => Workflow::TRIGGER_ON_NEW_AND_EXISTING
                ),
                4 => array(
                    'attributeName'        => 'triggerOn',
                    'operatorType'         => 'equals',
                    'value'                => self::resolveExtraTriggerOnValueByIsNewModel($isNewModel)
                ),
                //todo: triggerOn by isNewModel
            );
            $searchAttributeData['structure'] = '1 AND 2 AND (3 OR 4)';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('SavedWorkflow');
            $where = RedBeanModelDataProvider::makeWhere('SavedWorkflow', $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, null, $where, null);
        }

        protected static function resolveExtraTriggerOnValueByIsNewModel($isNewModel)
        {
            assert('is_bool($isNewModel)');
            if($isNewModel)
            {
                return Workflow::TRIGGER_ON_NEW;
            }
            else
            {
                return Workflow::TRIGGER_ON_EXISTING;
            }
        }
    }
?>