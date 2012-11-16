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

    class ReportRelationsAndAttributesToTreeAdapter
    {
        protected $report;

        protected $treeType;

        public function __construct(Report $report, $treeType)
        {
            assert('is_string($treeType)');
            $this->report   = $report;
            $this->treeType = $treeType;
        }

        public function getTreeType()
        {
            return $this->treeType;
        }

        public function getData()
        {
            $moduleClassName                = $this->report->getModuleClassName();
            $previousModelClassNamesInChain = array();
            $data                           = array();
            $data[0]                        = array('text' => $moduleClassName::getModuleLabelByTypeAndLanguage('Singular'));
            $childrenNodeData               = $this->getChildrenNodeData(
                                                $previousModelClassNamesInChain,
                                                $this->makeModelRelationsAndAttributesToReportAdapter(
                                                    $moduleClassName, $moduleClassName::getPrimaryModelName()));
            if(!empty($childrenNodeData))
            {
                $data[0]['children'] = $childrenNodeData;
            }
            return $data;
        }

        protected function getChildrenNodeData(Array $previousModelClassNamesInChain,
                                               ModelRelationsAndAttributesToReportAdapter $modelToReportAdapter,
                                               RedBeanModel $precedingModel = null,
                                               $precedingRelation = null, $depth = 0)
        {
            if(!in_array(get_class($modelToReportAdapter->getModel()), $previousModelClassNamesInChain) &&
               !$modelToReportAdapter->getModel() instanceof OwnedModel)
            {
                $previousModelClassNamesInChain[] = get_class($modelToReportAdapter->getModel());
            }
            $childrenNodeData        = array();
            $selectableRelationsData = $modelToReportAdapter->
                                       getSelectableRelationsData($precedingModel, $precedingRelation);
            foreach($selectableRelationsData as $relation => $relationData)
            {
                $relationModelClassName       = $modelToReportAdapter->getRelationModelClassName($relation);
                $relationModuleClassName      = $relationModelClassName::getModuleClassName();
                if($relationModuleClassName == null)
                {
                    throw new NotSupportedException();
                }
                if(!in_array($relationModelClassName, $previousModelClassNamesInChain) ||
                   $relationModelClassName == end($previousModelClassNamesInChain))
                {
                    $relationNode                 = array('text'=> $relationData['label'], 'expanded' => false);
                    if($depth == 0)
                    {
                    $relationModelToReportAdapter = $this->makeModelRelationsAndAttributesToReportAdapter(
                                                                           $relationModuleClassName, $relationModelClassName);
                    $relationChildrenNodeData     = $this->getChildrenNodeData($previousModelClassNamesInChain,
                                                                           $relationModelToReportAdapter,
                                                                           $modelToReportAdapter->getModel(), $relation, 1);
                    if(!empty($relationChildrenNodeData))
                    {
                        $relationNode['children'] = $relationChildrenNodeData;
                    }
                    }
                    $childrenNodeData[]           = $relationNode;
                }

            }
            $attributesData = $this->getAttributesData($modelToReportAdapter, $precedingModel, $precedingRelation);
            foreach($attributesData as $relation => $attributeData)
            {
                $attributeNode      = array('text'        => $attributeData['label'],
                                            'htmlOptions' => array('class' => 'attribute-to-place'));
                $childrenNodeData[] = $attributeNode;
            }
            return $childrenNodeData;
        }

        protected function getAttributesData(ModelRelationsAndAttributesToReportAdapter $modelToReportAdapter,
                                             RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            if($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_FILTERS)
            {
                return $modelToReportAdapter->getAttributesForFilters($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_DISPLAY_ATTRIBUTES)
            {
                return $modelToReportAdapter->getAttributesForDisplayAttributes($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_ORDER_BYS)
            {
                return $modelToReportAdapter->getAttributesForOrderBys($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_GROUP_BYS)
            {
                return $modelToReportAdapter->getAttributesForGroupBys($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES)
            {
                return $modelToReportAdapter->getForDrillDownAttributes($precedingModel, $precedingRelation);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function makeModelRelationsAndAttributesToReportAdapter($moduleClassName, $modelClassName)
        {
            assert('is_string($moduleClassName)');
            $rules                     = ReportRules::makeByModuleClassName($moduleClassName);
            $model                     = new $modelClassName(false);
            if($this->report->getType() == Report::TYPE_ROWS_AND_COLUMNS)
            {
                $adapter       = new ModelRelationsAndAttributesToRowsAndColumnsReportAdapter($model, $rules, $this->report);
            }
            elseif($this->report->getType() == Report::TYPE_SUMMATION)
            {
                $adapter       = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $this->report);
            }
            elseif($this->report->getType() == Report::TYPE_MATRIX)
            {
                $adapter       = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $this->report);
            }
            else
            {
                throw new NotSupportedException();
            }
            return $adapter;
        }
/**
        protected function getSomething()
        {
            $dataTree = array(
                        array(
                                'text'=>'Grampa',
                        'id'=>'grandpa-id',
                                'children'=>array(
                        array(
                                                'text'=>'Father',
                                'id'=>'father-id',
                                                'children'=>array(
                                                        array('text'=>'me', 'id'=>'me-id', 'htmlOptions' => array('class' => 'attribute-to-place')),
                                                        array('text'=>'big sis', 'id'=>'sis-id'),
                                                        array('text'=>'little brother', 'id'=>'bro-id'),
                                                )
                                        ),

                                )
                        )

                );
        }
        **/
    }

?>