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
    /**
     * Kanban view for tasks related to account/contact/lead/opportunity
     */
    abstract class TasksForRelatedKanbanView extends SecuredRelatedListView
    {
        /**
         * Override to have the default
         * @var bool
         */
        protected $renderViewToolBarDuringRenderContent = true;

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'        => 'RelatedKanbanViewDetailsMenu',
                                  'iconClass'   => 'icon-details',
                                  'id'          => 'RelatedKanbanViewActionMenu',
                                  'itemOptions' => array('class' => 'hasDetailsFlyout'),
                                  'model'       => 'eval:$this->params["relationModel"]',
                            ),
                            array('type'            => 'CreateTaskFromRelatedKanbanModalLink',
                                  'routeModuleId'   => 'eval:$this->moduleId',
                                  'routeParameters' => 'eval:$this->getCreateLinkRouteParameters()',
                                  'ajaxOptions'     => 'eval:TasksUtil::resolveAjaxOptionsForEditModel("Create")',
                                  'uniqueLayoutId'  => 'eval:$this->uniqueLayoutId',
                                  'modalContainerId'=> 'eval:TasksUtil::getModalContainerId()'
                            ),

                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * Constructor for the view
         * @param string $controllerId
         * @param string $moduleId
         * @param string $modelClassName
         * @param object $dataProvider
         * @param array $params
         * @param string $gridIdSuffix
         * @param array $gridViewPagerParams
         * @param object $kanbanBoard
         */
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
            $this->gridId                 = $this->getGridId();
            $this->setKanbanBoard($kanbanBoard);
            $this->params                 = $params;
            $this->modelId                = $params["relationModel"]->id;
        }

        /**
         * Renders content for a list view. Utilizes a CActiveDataprovider
         * and a CGridView widget.
         * and form layout.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content     = $this->renderKanbanViewTitleWithActionBars();
            $content    .= TasksUtil::renderViewModalContainer();
            //Check for zero count
            if($this->getDataProvider()->getTotalItemCount() > 0)
            {
                $content    .= $cClipWidget->getController()->clips['ListView'] . "\n";
                if ($this->getRowsAreSelectable())
                {
                    $content .= ZurmoHtml::hiddenField($this->gridId . $this->gridIdSuffix .
                                                        '-selectedIds', implode(",", $this->selectedIds)) . "\n"; // Not Coding Standard
                }
            }
            else
            {
                $zeroModelView = new ZeroTasksForRelatedModelYetView($this->controllerId,
                                                                     $this->moduleId, 'Task',
                                                                     get_class($this->params['relationModel']));
                $content .= $zeroModelView->render();
            }
            $content .= $this->renderScripts();
            return $content;
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
            //todo: return array(), not using this anymore
            $controllerId = $this->controllerId;
            $moduleId     = $this->moduleId;
            return array('name'                 => array('value'  => $this->getLinkString('$data->name', 'name'), 'class' => 'task-name'),
                         'requestedByUser'      => array('value'  => $this->getRelatedLinkString('$data->requestedByUser', 'requestedByUser', 'users'), 'class'  => 'requestedByUser-name'),
                         'status'               => array('value' => 'TasksUtil::resolveActionButtonForTaskByStatus(intval($data->status), "' . $controllerId . '", "' . $moduleId . '", $data->id)', 'class' => 'task-status'),
                         'subscribe'            => array('value' => array('TasksUtil', 'getKanbanSubscriptionLink'), 'class' => 'task-subscription'),
                         'completed'            => array('value' => 'TasksUtil::renderCompletionProgressBar($data)', 'class' => 'task-completion')
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

        /**
         * Gets module class name for the view
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'TasksModule';
        }

        /**
         * Resolves pagination params
         * @return array
         */
        protected function resolvePaginationParams()
        {
            return GetUtil::getData();
        }

        /**
         * Makes search attribute data
         * @return array
         */
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
            return array($this, 'resolveLinkString');
        }

        /**
         * Resolves the link string for task detail modal view
         * @param array $data
         * @param int $row
         * @return string
         */
        public function resolveLinkString($data, $row)
        {
            $taskUtil    = new TasksUtil();
            $content     = $taskUtil->getLinkForViewModal($data, $row, $this->controllerId,
                                                          $this->moduleId, $this->getActionModuleClassName());
            return $content;
        }

        /**
         * Gets relation attribute name
         * @return null
         */
        protected function getRelationAttributeName()
        {
            return null;
        }

        /**
         * @return null
         */
        public function renderPortletHeadContent()
        {
            return null;
        }

        /**
         * Gets title for kanban board
         * @return string
         */
        public function getTitle()
        {
            return $this->getKanbanBoardTitle();
        }

        /**
         * Render a toolbar above the form layout. This includes buttons and/or
         * links to go to different views or process actions such as save or delete
         * @param boolean $renderedInForm
         * @return A string containing the element's content.
         *
         */
        protected function renderActionElementBar($renderedInForm)
        {
            $kanbanActive = false;
            if($this->params['relationModuleId'] == 'projects')
            {
                $kanbanActive = true;
            }
            else
            {
                $getData = GetUtil::getData();
                if(isset($getData['kanbanBoard']) && $getData['kanbanBoard'] == 1)
                {
                   $kanbanActive = true;
                }
            }

            if($kanbanActive)
            {
               $content = parent::renderActionElementBar($renderedInForm);
            }
            return $content;
        }

        /**
         * @return string
         */
        protected function getGridViewWidgetPath()
        {
            return $this->getKanbanBoard()->getGridViewWidgetPath();
        }

        /**
         * @return string
         */
        protected function getCGridViewParams()
        {
            $params = parent::getCGridViewParams();
            $params = array_merge($params, $this->getKanbanBoard()->getGridViewParams());
            return array_merge($params, $this->resolveExtraParamsForKanbanBoard());
        }

        /**
         * Get grid id
         * @return string
         */
        protected function getGridId()
        {
            return $this->getRelationAttributeName() . '-tasks-kanban-view';
        }

        /**
         * Override to pass the sourceId
         * @return type
         */
        protected function getCreateLinkRouteParameters()
        {
            return array_merge( array('sourceId' => $this->getGridViewId()),
                                parent::getCreateLinkRouteParameters());
        }

        /**
         * Renders kanban view with action bars
         * @return string
         */
        protected function renderKanbanViewTitleWithActionBars()
        {
            $content                 = $this->renderTitleContent();
            $actionElementBarContent = $this->renderActionElementBar(false);
            $actionBarContent        = ZurmoHtml::tag('nav', array('class' => 'pillbox clearfix'),
                                                      $actionElementBarContent);
            $secondActionBarContent  = $this->renderSecondActionElementBar(false);
            $secondActionBarContent .= $this->resolveShouldRenderActionBarLinksForKanbanBoard();
            $actionBarContent .= ZurmoHtml::tag('nav', array('class' => 'pillbox clearfix'), $secondActionBarContent);
            $content .= ZurmoHtml::tag('div', array('class' => 'view-toolbar-container clearfix'), $actionBarContent);
            return $content;
        }

        protected function resolveShouldRenderActionBarLinksForKanbanBoard()
        {
            if($this->shouldRenderActionBarLinksForKanbanBoard())
            {
                return ZurmoDefaultViewUtil::renderActionBarLinksForKanbanBoard($this->controllerId,
                    $this->params['relationModuleId'],
                    (int)$this->params['relationModel']->id,
                    true);
            }
        }

        protected function shouldRenderActionBarLinksForKanbanBoard()
        {
            return true;
        }

        /**
         * Modify the grid template for kanban view
         * @return string
         */
        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>';
            $items     = '<div class="items-wrapper">{items}</div>';
            return "{summary}" . $items . "{pager}" . $preloader;
        }
    }
?>