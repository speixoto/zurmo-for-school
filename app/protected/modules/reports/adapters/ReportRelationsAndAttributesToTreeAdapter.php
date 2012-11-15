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
        protected $treeType;

        public function __construct(ModelRelationsAndAttributesToReportAdapter $modelToReportAdapter, $treeType)
        {
            assert('is_string($treeType)');
            $this->modelToReportAdapter = $modelToReportAdapter;
            $this->treeType             = $treeType;
        }

        public function getData()
        {
            $data                = array();
            $data[0]             = array('text' => 'Top Node');
            $childrenNodeData    = $this->getChildrenNodeData();
            if(!empty($childrenNodeData))
            {
                $data[0]['children'] = $childrenNodeData;
            }
            return $data;
        }

        protected function getChildrenNodeData(RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            $childrenNodeData        = array();
            $selectableRelationsData = $this->modelToReportAdapter->
                                       getSelectableRelationsData($precedingModel, $precedingRelation);

            foreach($selectableRelationsData as $relation => $relationData)
            {
                $relationNode       = array('text'=> $relationData['label']);
                //todo: get children
                $childrenNodeData[] = $relationNode;
            }

            $attributesData = $this->getAttributesData($precedingModel, $precedingRelation);
            foreach($attributesData as $relation => $attributeData)
            {
                $attributeNode      = array('text'        => $attributeData['label'],
                                            'htmlOptions' => array('class' => 'attribute-to-place'));
                $childrenNodeData[] = $attributeNode;
            }
            return $childrenNodeData;
        }

        protected function getAttributesData(RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            if($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_FILTERS)
            {
                return $this->modelToReportAdapter->getAttributesForFilters($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_DISPLAY_ATTRIBUTES)
            {
                return $this->modelToReportAdapter->getAttributesForDisplayAttributes($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_ORDER_BYS)
            {
                return $this->modelToReportAdapter->getAttributesForOrderBys($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_GROUP_BYS)
            {
                return $this->modelToReportAdapter->getAttributesForGroupBys($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES)
            {
                return $this->modelToReportAdapter->getForDrillDownAttributes($precedingModel, $precedingRelation);
            }
            else
            {
                throw new NotSupportedException();
            }
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