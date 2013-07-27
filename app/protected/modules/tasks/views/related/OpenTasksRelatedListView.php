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

    abstract class OpenTasksRelatedListView extends SecuredRelatedListView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'perUser' => array(
                    'title' => "eval:Zurmo::t('TasksModule', 'Open TasksModulePluralLabel', LabelUtil::getTranslationParamsForAllModules())",
                ),
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array(  'type'            => 'CreateFromRelatedModalLink',
                                    'portletId'       => 'eval:$this->params["portletId"]',
                                    'routeModuleId'   => 'eval:$this->moduleId',
                                    'routeParameters' => 'eval:$this->getCreateLinkRouteParameters()',
                                    'ajaxOptions'     => 'eval:$this->resolveAjaxOptionsForSelectingModel()',
                                    'uniqueLayoutId'  => 'eval:$this->uniqueLayoutId'
                                 ),
                        ),
                    ),
                    'rowMenu' => array(
                        'elements' => array(
                            array('type' => 'EditLink'),
                            array('type' => 'CopyLink'),
                            array('type' => 'RelatedDeleteLink'),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'CloseTaskCheckBox',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime',
                    ),
                    'gridViewType' => RelatedListView::GRID_VIEW_TYPE_STACKED,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'CloseTaskCheckBox'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text', 'isLink' => true),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
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
                ),
                2 => array(
                    'attributeName'        => 'completed',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1
                ),
                3 => array(
                    'attributeName'        => 'completed',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                )
            );
            $searchAttributeData['structure'] = '(1 and (2 OR 3))';
            return $searchAttributeData;
        }

        public static function getModuleClassName()
        {
            return 'TasksModule';
        }

        protected function resolveAjaxOptionsForSelectingModel()
        {
            $title = $this->getModalTitleForSelectingModel();
            return   ModalView::getAjaxOptionsForModalLink($title, $this->getModalContainerId());
        }

        protected function getModalTitleForSelectingModel()
        {
            $module              = Yii::app()->getModule('tasks');
            $moduleSingularLabel = $module->getModuleLabelByTypeAndLanguage('Singular');
            return Zurmo::t('Core', 'Create {moduleSingularLabel}',
                                      array('{moduleSingularLabel}' => $moduleSingularLabel));
        }

        protected function getModalContainerId()
        {
            return ModalLinkActionElement::RELATED_MODAL_CONTAINER_PREFIX . '-open-tasks';
        }

        protected function getViewModalContainerId()
        {
            return ModalLinkActionElement::RELATED_MODAL_CONTAINER_PREFIX . '-view-task';
        }

        protected function renderContent()
        {
            $content = parent::renderContent();
            $content .= $this->renderModalContainer();
            $content .= $this->renderViewModalContainer();
            return $content;
        }

        protected function renderModalContainer()
        {
            return ZurmoHtml::tag('div', array('id' => $this->getModalContainerId()), '');
        }

        protected function renderViewModalContainer()
        {
            return ZurmoHtml::tag('div', array('id' => $this->getViewModalContainerId()), '');
        }

        /*TODO this needs to be removed if modal refresh would not be called*/
        protected function getCGridViewPagerParams()
        {
            $getData = GetUtil::getData();
            if(isset($getData['uniqueLayoutId']))
            {
                unset($getData['uniqueLayoutId']);
            }
            if(isset($getData['redirectUrl']))
            {
                unset($getData['redirectUrl']);
            }
            if(isset($getData['portletParams']))
            {
                unset($getData['portletParams']);
            }
            return array(
                    'firstPageLabel' => '<span>first</span>',
                    'prevPageLabel'  => '<span>previous</span>',
                    'nextPageLabel'  => '<span>next</span>',
                    'lastPageLabel'  => '<span>last</span>',
                    'class'          => 'SimpleListLinkPager',
                    'paginationParams' => array_merge($getData, array('portletId' => $this->params['portletId'])),
                    'route'         => 'defaultPortlet/details',
                );
        }

        /**
         * Override to handle security/access resolution on links.
         */
        public function getLinkString($attributeString, $attribute)
        {
            return array($this, 'resolveLinkString');
        }

        protected function resolveViewAjaxOptionsForSelectingModel()
        {
            $title = $this->getViewModalTitleForSelectingModel();
            return   ModalView::getAjaxOptionsForModalLink($title, $this->getViewModalContainerId());
        }

        public function resolveLinkString($data, $row)
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            $title = Zurmo::t('TasksModule', $data->name, $params);
            $ajaxOptions = $this->resolveViewAjaxOptionsForSelectingModel();
            $params = array('label' => $title, 'routeModuleId' => 'tasks', 'ajaxOptions' => $ajaxOptions);
            $viewFromRelatedModalLinkActionElement = new ViewFromRelatedModalLinkActionElement($this->controllerId, $this->moduleId, $data->id, $params);
            $linkContent = $viewFromRelatedModalLinkActionElement->render();
            $string      = TaskActionSecurityUtil::resolveViewLinkToModelForCurrentUser($data, $this->getActionModuleClassName(), $linkContent);
            return $string;
        }

        protected function getViewModalTitleForSelectingModel()
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            $title = Zurmo::t('TasksModule', 'View TasksModuleSingularLabel', $params);
            return $title;
        }
    }
?>