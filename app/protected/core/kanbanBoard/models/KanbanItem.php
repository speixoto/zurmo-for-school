<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class KanbanItem extends RedBeanModel
    {
        /*
         * Constants for task status
         */
        const TYPE_SOMEDAY               = 1;

        const TYPE_TODO                  = 2;

        const TYPE_IN_PROGRESS           = 3;

        const TYPE_COMPLETED             = 4;

        /**
         * @return string
         */
        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('TasksModule', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        /**
         * Gets module class name
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'TasksModule';
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
                    'type',
                    'sortOrder'
                ),
                'relations' => array(
                    'kanbanRelatedItem'         => array(RedBeanModel::HAS_ONE, 'Item', RedBeanModel::OWNED, RedBeanModel::LINK_TYPE_SPECIFIC, 'kanbanrelateditem'),
                    'task'                      => array(RedBeanModel::HAS_ONE, 'Task')
                ),
                'rules' => array(
                    array('type', 'type', 'type' => 'integer'),
                    array('sortOrder', 'type', 'type' => 'integer'),
                ),
                'elements' => array(
                    'kanbanRelatedItem' => 'Item',
                    'task'              => 'Task'
                ),
                'defaultSortAttribute' => 'sortOrder',
                'noAudit' => array(

                ),
            );
            return $metadata;
        }

        /**
         * @return array
         */
        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'type'          => Zurmo::t('TasksModule', 'Type', array(), null, $language),
                    'sortOrder'     => Zurmo::t('TasksModule', 'Order',  array(), null, $language),
                    'kanbanItem'    => Zurmo::t('TasksModule', 'Kanban Item',  array(), null, $language),
                    'task'          => Zurmo::t('TasksModule', 'Task', array(), null, $language)
                )
            );
        }

        /**
         * @return bool
         */
        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * @return bool
         */
        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        /**
         * @return string
         */
        public static function getGamificationRulesType()
        {
            return null;
        }

        /**
         * @return array of type values and labels
         */
        public static function getTypeDropDownArray()
        {
            return array(
                self::TYPE_SOMEDAY                  => Zurmo::t('TasksModule', 'Someday'),
                self::TYPE_IN_PROGRESS              => Zurmo::t('TasksModule', 'In Progress'),
                self::TYPE_TODO                     => Zurmo::t('TasksModule', 'To Do'),
                self::TYPE_COMPLETED                => Zurmo::t('TasksModule', 'Completed'),
            );
        }

        /**
         * Gets the display name for the type
         * @param int $type
         */
        public static function getTypeDisplayName($type)
        {
            $typeArray = self::getStatusDropDownArray();
            return $typeArray[$type];
        }

        /**
         * Get the kanban item by task
         * @param int $taskId
         * @return integer
         */
        public static function getByTask($taskId)
        {
            assert('is_int($taskId)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'task',
                    'operatorType'              => 'equals',
                    'value'                     => intval($taskId),
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where  = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            if (count($models) == 0)
            {
                return null;
            }
            elseif (count($models) > 1)
            {
                throw new NotSupportedException();
            }
            else
            {
                return $models[0];
            }
        }

        /**
         * Get maximum sort order by type
         * @param int $taskType
         * @return int
         */
        public static function getMaximumSortOrderByType($taskType)
        {
            assert('is_int($taskType)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'type',
                    'operatorType'              => 'equals',
                    'value'                     => intval($taskType),
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where  = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, 'sortOrder DESC');
            if (count($models) == 0)
            {
                return 1;
            }
            elseif (count($models) >= 1)
            {
                return intval($models[0]->sortOrder) + 1;
            }
        }

        public static function hasReadPermissionsSubscriptionOptimization()
        {
            return true;
        }
    }
?>
