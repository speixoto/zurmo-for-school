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

    class TasksForOpportunityKanbanView extends SecuredRelatedListView
    {
        public function __construct(
            $controllerId,
            $moduleId,
            $modelClassName,
            $dataProvider,
            $params,
            $gridIdSuffix = null,
            $gridViewPagerParams = array(),
            $kanbanBoard            = null
        )
        {
            assert('is_string($modelClassName)');
            assert('is_array($this->gridViewPagerParams)');
            assert('$kanbanBoard === null || $kanbanBoard instanceof $kanbanBoard');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->modelClassName         = $modelClassName;
            $this->dataProvider           = $dataProvider;
            $this->gridIdSuffix           = $gridIdSuffix;
            $this->gridViewPagerParams    = $gridViewPagerParams;
            $this->gridId                 = 'kanban-view';
            $this->kanbanBoard            = $kanbanBoard;
            $this->params                 = $params;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            return $metadata;
        }

        /**
         * Resolve extra parameters for kanban board
         * @return array
         */
        protected function resolveExtraParamsForKanbanBoard()
        {
            return array('cardColumns' => $this->getCardColumns());
        }

        /**
         * @return array
         */
        protected function getCardColumns()
        {
            return array('name'                 => array('value'  => $this->getLinkString('$data->name', 'name'), 'class' => 'task-name'),
                         'requestedByUser'      => array('value'  => $this->getRelatedLinkString('$data->requestedByUser', 'requestedByUser', 'users'), 'class'  => 'requestedByUser-name'),
                         'status'
                        );
        }

        /**
         * @return array
         */
        protected function getCGridViewColumns()
        {
            $columns = array();
            return $columns;
        }

        protected function getRelationAttributeName()
        {
            return 'Opportunity';
        }

        public static function getModuleClassName()
        {
            return 'TasksModule';
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'firstPageLabel'    => '<span>first</span>',
                    'prevPageLabel'     => '<span>previous</span>',
                    'nextPageLabel'     => '<span>next</span>',
                    'lastPageLabel'     => '<span>last</span>',
                    'class'             => 'SimpleListLinkPager',
                    'paginationParams'  => GetUtil::getData(),
                    'route'             => 'default/details',
                );
        }

        protected function makeSearchAttributeData()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'activityItems',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => (int)$this->params['relationModel']->getClassId('Item'),
                )
            );
            $searchAttributeData['structure'] = '1';
            return $searchAttributeData;
        }

        /**
         * Override to handle security/access resolution on links.
         */
        public function getLinkString($attributeString, $attribute)
        {
            $string  = 'ActionSecurityUtil::resolveLinkToModelForCurrentUser("' . $attributeString . '", ';
            $string .= '$data, "' . $this->getActionModuleClassName() . '", ';
            $string .= '"' . $this->getGridViewActionRoute('details') . '", (int)$offset)';
            return $string;
        }
    }
?>