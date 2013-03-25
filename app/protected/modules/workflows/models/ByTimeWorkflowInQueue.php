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

    class ByTimeWorkflowInQueue extends Item
    {
        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'modelClassName',
                    'processDateTime',
                ),
                'relations' => array(
                    'modelItem'     => array(RedBeanModel::HAS_ONE, 'Item',          RedBeanModel::NOT_OWNED),
                    'savedWorkflow' => array(RedBeanModel::HAS_ONE, 'SavedWorkflow', RedBeanModel::NOT_OWNED),
                ),
                'rules' => array(
                    array('modelClassName',   'required'),
                    array('modelClassName',   'type',   'type' => 'string'),
                    array('modelClassName',   'length', 'max'  => 64),
                    array('modelItem',        'required'),
                    array('processDateTime',  'required'),
                    array('processDateTime',  'type', 'type' => 'datetime'),
                    array('savedWorkflow',    'required'),
                ),
                'elements' => array(
                    'processDateTime' => 'DateTime'
                ),
                'defaultSortAttribute' => 'processDateTime',
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

        public static function resolveByWorkflowIdAndModel(SavedWorkflow $savedWorkflow, RedBeanModel $model)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'modelItem',
                    'operatorType'         => 'equals',
                    'value'                => $model->getClassId('Item')
                ),
                2 => array(
                    'attributeName'        => 'savedWorkflow',
                    'operatorType'         => 'equals',
                    'value'                => $savedWorkflow->id
                ),
                3 => array(
                    'attributeName'        => 'modelClassName',
                    'operatorType'         => 'equals',
                    'value'                => get_class($model)
                ),
            );
            $searchAttributeData['structure'] = '1 and 2 and 3';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('ByTimeWorkflowInQueue');
            $where             = RedBeanModelDataProvider::makeWhere('ByTimeWorkflowInQueue', $searchAttributeData,
                                                                     $joinTablesAdapter);
            $models            = self::getSubset($joinTablesAdapter, null, null, $where, null);
            if(count($models) > 1)
            {
                throw new NotSupportedException();
            }
            elseif(count($models) == 1)
            {
                return $models[0];
            }
            else
            {
                $byTimeWorkflowInQueue                 = new ByTimeWorkflowInQueue();
                $byTimeWorkflowInQueue->modelClassName = get_class($model);
                $byTimeWorkflowInQueue->modelItem      = $model;
                $byTimeWorkflowInQueue->savedWorkflow  = $savedWorkflow;
                return $byTimeWorkflowInQueue;
            }
        }
    }
?>