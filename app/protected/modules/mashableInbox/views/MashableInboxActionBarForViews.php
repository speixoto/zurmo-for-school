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

    class MashableInboxActionBarForViews extends ConfigurableMetadataView
    {
        private $actionViewOptions;

        private $listView;

        private $formName = 'search-form';

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'          => 'MashableInboxCreate',
                                  'htmlOptions'   => array('class' => 'icon-create'),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public function __construct($controllerId,
                                    $moduleId,
                                    $listView,
                                    Array $actionViewOptions,
                                    MashableInboxForm $mashableInboxForm,
                                    $modelClassName)
        {
            $this->controllerId              = $controllerId;
            $this->moduleId                  = $moduleId;
            $this->listView                  = $listView;
            $this->actionViewOptions         = $actionViewOptions;
            $this->mashableInboxForm         = $mashableInboxForm;
            $this->modelClassName            = $modelClassName;
            $this->cssClasses                = array_merge($this->cssClasses, array("GridView")); //Todo: Move this into a gridview
        }

        protected function renderContent()
        {
            $content  = '<div class="view-toolbar-container clearfix"><div class="view-toolbar">';
            $content .= $this->renderActionElementBar(false);
            $content .= $this->renderMashableInboxModelsToolbar();
            $content .= $this->renderMassActionElement();
            $content .= '</div></div>';
            $content .= $this->renderMashableInboxForm();
            $content .= $this->listView->render();
            return $content;
        }

        private function renderMassActionElement()
        {
            $params = array('type'           => 'MashableInboxMass',
                            'htmlOptions'    => array('class' => 'icon-create'),
                            'listViewGridId' => $this->listView->getGridViewId(),
                            'modelClassName' => $this->modelClassName,
                            'formName'       => $this->formName,
                        );
            $massActionElement = new MashableInboxMassActionElement($this->controllerId, $this->moduleId, 'MashableInboxForm', $params);
            return $massActionElement->render();
        }

        private function renderMashableInboxForm()
        {
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id' => $this->formName,
                )
            );
            $content  = $formStart;
            $content .= $this->renderMashableInboxFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $this->registerFormScript($form);
            return $content;
        }

        protected function renderMashableInboxFormLayout($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $content      = null;
            $model        = $this->mashableInboxForm;
            $content      = $this->renderSearchView($model, $form);
            $element      = new MashableInboxOptionsByModelRadioElement($model, 'optionForModel', $form, array(), $this->getArrayForByModelRadioElement());
            $element->editableTemplate =  '<div id="MashableInboxForm_optionForModel_area">{content}</div>';
            $content     .= '<div class="filters-bar">';
            $content     .= $element->render();
            $element      = new MashableInboxStatusRadioElement($model, 'filteredBy', $form);
            $element->editableTemplate =  '<div id="MashableInboxForm_filteredBy_area">{content}</div>';
            $content     .= $element->render();
            $content     .= '</div>';
            $content     .= ZurmoHtml::activeHiddenField($model, 'selectedIds');
            $content     .= ZurmoHtml::activeHiddenField($model, 'massAction');
            return $content;
        }

        private function renderSummaryCloneContent()
        {
            return "<div class='list-view-items-summary-clone'></div>";
        }

        private function renderSearchView($model, $form)
        {
            $params   = array('listViewGridId' => $this->listView->getGridViewId());
            $element  = new MashableInboxSearchElement($model, 'searchTerm', $form, $params);
            $content  = $element->render();
            $content .= $this->renderSummaryCloneContent();
            return ZurmoHtml::tag('div', array('class' => 'search-view-0'), $content);
        }

        private function renderMashableInboxModelsToolbar()
        {
            $activeClass           = null;
            if ($this->modelClassName == null)
            {
                $activeClass = "active";
            }
            $unreadCount           = MashableUtil::getUnreadCountMashableInboxForCurrentUser();
            $url                   = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/list');
            $label                 = Zurmo::t('MashableInboxModule', 'Combined');
            $span                  = ZurmoHtml::tag('span', array("class" => "unread-count"),$unreadCount);
            $zLabel                = ZurmoHtml::tag('span', array("class" => "z-label"), $label . $span);
            $content               = ZurmoHtml::link($zLabel, $url, array('class' => 'icon-combined ' . $activeClass));
            $combinedInboxesModels = MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface');
            foreach ($combinedInboxesModels as $modelClassName => $modelLabel)
            {
                $activeClass       = null;
                if ($this->modelClassName == $modelClassName)
                {
                    $activeClass = "active";
                }
                $unreadCount = MashableUtil::getUnreadCountForCurrentUserByModelClassName($modelClassName);
                $url         = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/list',
                                                     array('modelClassName' => $modelClassName));
                $span        = ZurmoHtml::tag('span', array("class" => "unread-count"), $unreadCount);
                $zLabel      = ZurmoHtml::tag('span', array("class" => "z-label"), $modelLabel . $span);
                $content    .= ZurmoHtml::link($zLabel, $url, array('class' => 'icon-' . strtolower($modelClassName) . ' '  . $activeClass));
            }
            return $content;
        }

        private function getArrayForByModelRadioElement()
        {
            $options = array();
            foreach ($this->actionViewOptions as $option)
            {
                $options[$option['type']] = $option['label'];
            }
            return $options;
        }

        private function registerFormScript($form)
        {
            $listViewId       = $this->listView->getGridViewId();
            $ajaxSubmitScript = "$('#{$listViewId}').yiiGridView('update', {data: $('#" . $form->getId() . "').serialize()});";
            $script = "
                    $('#MashableInboxForm_optionForModel_area').find('input:checked').next().addClass('ui-state-active');
                    $('#MashableInboxForm_filteredBy_area').find('input:checked').next().addClass('ui-state-active');
                    " . $this->getScriptForButtonset() . "
                    $('#MashableInboxForm_optionForModel_area').change(
                        function()
                        {
                            " . $ajaxSubmitScript . "
                        }
                    );
                    $('#MashableInboxForm_filteredBy_area').change(
                        function()
                        {
                            " . $ajaxSubmitScript . "
                        }
                    );
                ";
             Yii::app()->clientScript->registerScript('MashableInboxForm', $script);
        }

        private function getScriptForButtonset()
        {
            $script = "
                    $('#MashableInboxForm_filteredBy_area').find('label').each(
                                function(){
                                    \$(this).click(function(){
                                        $('#MashableInboxForm_filteredBy_area').find('label').each(function(){\$(this).removeClass('ui-state-active')});
                                        \$(this).addClass('ui-state-active');
                                     })
                                }
                            );
                    $('#MashableInboxForm_optionForModel_area').find('label').each(
                                function(){
                                    \$(this).click(function(){
                                        $('#MashableInboxForm_optionForModel_area').find('label').each(function(){\$(this).removeClass('ui-state-active')});
                                        \$(this).addClass('ui-state-active');
                                     })
                                }
                            );
                ";
            return $script;
        }
    }
?>