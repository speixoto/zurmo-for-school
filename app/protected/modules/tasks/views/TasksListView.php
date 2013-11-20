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

    class TasksListView extends SecuredListView
    {
        /**
         * Form that has the information for status filter
         */
        protected $configurationForm = 'TasksConfigurationForm';

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text', 'isLink' => true),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'dueDateTime', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'owner', 'type' => 'User'),
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

        /**
         * Constructs a list view specifying the controller as
         * well as the model that will have its details displayed.isDisplayAttributeACalculationOrModifier
         */
        public function __construct(
            $controllerId,
            $moduleId,
            $modelClassName,
            $dataProvider,
            $selectedIds,
            $gridIdSuffix = null,
            $gridViewPagerParams = array(),
            $listAttributesSelector = null,
            $kanbanBoard            = null
        )
        {
            parent::__construct($controllerId, $moduleId, $modelClassName,
                                $dataProvider, $selectedIds, $gridIdSuffix,
                                $gridViewPagerParams, $listAttributesSelector, null);
            $this->uniquePageId = get_called_class();
            $this->configurationForm = TasksUtil::getConfigurationFormWithStatusAsStickyData();
        }

        /**
         * Renders content
         * @return string
         */
        protected function renderContent()
        {
            $content = $this->renderConfigurationForm();
            TasksUtil::resolveShouldOpenToTask($this->getGridViewId());
            $content .= parent::renderContent();
            return $content;
        }

        /**
         * @return string
         */
        protected function renderConfigurationForm()
        {
            $formName   = 'task-status-form';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id' => $formName,
                )
            );
            $content  = $formStart;
            $content .= $this->renderConfigurationFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $this->registerConfigurationFormLayoutScripts($form);
            return $content;
        }

        /**
         * @param ProductsConfigurationForm $form
         * @return string
         */
        protected function renderConfigurationFormLayout($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $content      = null;
            $innerContent = null;
            $element                   = new TaskStatusFilterRadioElement($this->configurationForm,
                                                                                          'filteredByStatus',
                                                                                          $form);
            $element->editableTemplate =  '<div id="TasksConfigurationForm_filteredByStatus_area">{content}</div>';
            $statusFilterContent       = $element->render();
            $innerContent             .= $statusFilterContent;
            if ($innerContent != null)
            {
                $content .= '<div class="filter-portlet-model-bar">';
                $content .= $innerContent;
                $content .= '</div>' . "\n";
            }
            return $content;
        }

        /**
         * @param ProductsConfigurationForm $form
         */
        protected function registerConfigurationFormLayoutScripts($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $urlScript = Yii::app()->createUrl('tasks/default/list', array('ajax' => 'filter-list-view')); // Not Coding Standard
            $ajaxSubmitScript = ZurmoHtml::ajax(array(
                    'type'       => 'GET',
                    'data'       => 'js:$("#' . $form->getId() . '").serialize()',
                    'url'        =>  $urlScript,
                    'update'     => '#TasksListView',
                    'beforeSend' => 'js:function(){$(this).makeSmallLoadingSpinner(true, "#' . $this->getGridViewId() . '"); '
                . '                                 $("#' . $form->getId() . '").parent().children(".cgrid-view").addClass("loading");}',
                    'complete'   => 'js:function()
                    {
                                        $("#' . $form->getId() . '").parent().children(".cgrid-view").removeClass("loading");
                    }'
            ));
            Yii::app()->clientScript->registerScript($this->getGridViewId() . '-statusfilter', "
            var filterarea = $('#TasksConfigurationForm_filteredByStatus_area');
            filterarea.buttonset();
            filterarea.change(function()
                {
                    " . $ajaxSubmitScript . "
                }
            );
            ");
        }
    }
?>
