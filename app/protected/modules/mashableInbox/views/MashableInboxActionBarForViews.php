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

        private $listViewModelClassName;

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'          => 'MashableInboxCreate',
                                'htmlOptions'     => array('class' => 'icon-create'),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public function __construct($controllerId, $moduleId, $listViewModelClassName, Array $actionViewOptions, MashableInboxForm $mashableInboxForm)
        {
            $this->controllerId              = $controllerId;
            $this->moduleId                  = $moduleId;
            $this->listViewModelClassName    = $listViewModelClassName;
            $this->actionViewOptions         = $actionViewOptions;
            $this->mashableInboxForm         = $mashableInboxForm;
        }

        protected function renderContent()
        {
            $content  = '<div class="view-toolbar-container clearfix"><div class="view-toolbar">';
            $content .= $this->renderActionElementBar(false);
            $content .= $this->renderMashableInboxModels();
            $content .= '</div></div>';
            $content .= $this->renderMashableInboxForm();
            return $content;
        }

        private function renderMashableInboxForm()
        {
            $formName   = 'mashable-inbox-form';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id' => $formName,
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
            $element      = new MashableInboxOptionsByModelRadioElement($model, 'optionForModel', $form, array(), $this->getArrayForByModelRadioElement());
            $element->editableTemplate =  '<div id="MashableInboxForm_optionForModel_area">{content}</div>';
            $content     .= $element->render();
            $element      = new MashableInboxStatusRadioElement($model, 'filteredBy', $form);
            $element->editableTemplate =  '<div id="MashableInboxForm_filteredBy_area">{content}</div>';
            $content     .= $element->render();
            return $content;
        }

        private function renderMashableInboxModels()
        {
            $unreadCount           = MashableUtil::getUnreadCountMashableInboxForCurrentUser();
            $url                   = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/list');
            $label                 = Zurmo::t('MashableInboxModule', 'Combined');
            $content               = ZurmoHtml::link($label . ' (' . $unreadCount . ')', $url);
            $combinedInboxesModels = MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface');
            foreach ($combinedInboxesModels as $modelClassName => $modelLabel)
            {
                $unreadCount = MashableUtil::getUnreadCountForCurrentUserByModelClassName($modelClassName);
                $url         = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/list',
                                                     array('modelClassName' => $modelClassName));
                $content    .= ZurmoHtml::link($modelLabel . ' (' . $unreadCount . ')', $url);
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

            $url = "";
            $ajaxSubmitScript = ZurmoHtml::ajax(array(
                        'type'       => 'GET',
                        'data'       => 'js:$("#' . $form->getId() . '").serialize()',
                        'url'        =>  $url,
                        'update'     => '.ListView',
                        'beforeSend' => 'js:function(){makeSmallLoadingSpinner(); $(".ListView").addClass("loading");}',
                        'complete'   => 'js:function()
                                            {
                                                $(".ListView").removeClass("loading");
                                            }'
                    ));
            $script = "
                    $('#MashableInboxForm_optionForModel_area').buttonset();
                    $('#MashableInboxForm_filteredBy_area').buttonset();
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
    }
?>