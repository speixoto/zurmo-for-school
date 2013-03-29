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

    /**
     * View for selecting which module to manage workflow sequences for
     */
    class WorkflowManageOrderView extends MetadataView
    {
        /**
         * @return string
         */
        public static function getFormId()
        {
            return 'edit-form';
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $content  = $this->renderForm();
            $this->renderLoadModuleOrderScriptContent();
            return $content;
        }

        /**
         * @return string
         */
        protected function renderForm()
        {
            $content  = '<div class="wrapper">';
            $content .= $this->renderTitleContent();
            $content .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id' => static::getFormId(),
                    'action' => $this->getFormActionUrl(),
                    'enableAjaxValidation' => true,
                    'clientOptions' => array(
                        'validateOnSubmit'  => true,
                        'validateOnChange'  => false,
                        'beforeValidate'    => 'js:beforeValidateAction',
                        'afterValidate'     => 'js:afterValidateAjaxAction',
                        'afterValidateAjax' => $this->renderConfigSaveAjax(static::getFormId()),
                    ),
                )
            );
            $content .= $formStart;
            $content .= $this->renderNoModuleSelectedContentAndWrapper();
            $content .= $this->renderNoWorkflowsToOrderContentAndWrapper();
            $content .= $this->renderModuleSelectorContentAndWrapper($form);
            $content .= $this->renderWorkflowOrderContentAndWrapper();
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div></div>';
            return $content;
        }

        /**
         * @return string
         */
        protected function getNoModuleSelectedContent()
        {
            return '<div class="large-icon"></div><h2>' . Zurmo::t('WorkflowsModule', 'Select a module to order workflow rules') . '</h2>';
        }

        protected function renderNoModuleSelectedContentAndWrapper()
        {
            return ZurmoHtml::tag('div', array('class' => 'select-module-view'), $this->getNoModuleSelectedContent());
        }

        protected function getNoWorkflowsToOrderContent()
        {
            return '<div class="large-icon"></div><h2>' . Zurmo::t('WorkflowsModule', 'This module does not have any workflows to order') . '</h2>';
        }

        protected function renderNoWorkflowsToOrderContentAndWrapper()
        {
            return ZurmoHtml::tag('div', array('class' => 'no-workflows-to-order-view', 'style' => "display:none;"),
                                  $this->getNoWorkflowsToOrderContent());
        }

        protected function renderModuleSelectorContentAndWrapper($form)
        {
            $element                    = new ModuleForWorkflowStaticDropDownElement(new SavedWorkflow(),
                                          'moduleClassName', $form, array('addBlank' => true));
            $element->editableTemplate  = '{content}{error}';
            return ZurmoHtml::tag('div', array('class' => 'workflow-order-module-selector-container'), $element->render());
        }

        protected function renderWorkflowOrderContentAndWrapper()
        {
            $content =             $content = ZurmoHtml::tag('div', array('class' => 'dynamic-rows'), '');
            return ZurmoHtml::tag('div', array('id' => 'workflow-order-container'), $content . $this->renderSaveLinkContent());
        }

        protected function renderSaveLinkContent()
        {
            $params                = array();
            $params['label']       = Zurmo::t('Core', 'Save');
            $params['htmlOptions'] = array('id'      => 'save-order',
                                           'onclick' => 'js:$(this).addClass("attachLoadingTarget");',
                                           'style'   => "display:none;");
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }

        /**
         * @return mixed
         */
        protected function getFormActionUrl()
        {
            return Yii::app()->createUrl('workflows/default/saveOrder');
        }

        /**
         * @return array
         */
        protected function getClientOptions()
        {
            return array(
                'validateOnSubmit'  => true,
                'validateOnChange'  => false,
                'beforeValidate'    => 'js:beforeValidateAction',
                'afterValidate'     => 'js:afterValidateAjaxAction',
                'afterValidateAjax' => $this->renderConfigSaveAjax(static::getFormId()),
            );
        }

        protected function renderConfigSaveAjax($formName)
        {
            return ZurmoHtml::ajax(array(
                'type'       => 'POST',
                'dataType'   => 'json',
                'data'       => 'js:$("#' . $formName . '").serialize()',
                'url'        =>  $this->getFormActionUrl(),
                'complete'   => 'js:function(){detachLoadingOnSubmit("' . static::getFormId() . '");}',
                'success'    => 'function(data){$("#FlashMessageBar").jnotifyAddMessage({
                                 text: data.message, permanent: false, showIcon: true, type: data.type
                                 });}',
            ));
        }

        /**
         * @param $formName
         * @return string
         */
        protected function renderLoadModuleOrderScriptContent()
        {
            $id         = 'SavedWorkflow_moduleClassName_value';
            $inputDivId = 'dynamic-rows';
            $url        =  Yii::app()->createUrl('workflows/default/loadOrderByModule');
            // Begin Not Coding Standard
            $ajaxSubmitScript  = ZurmoHtml::ajax(array(
                'type'     => 'GET',
                'dataType' => 'json',
                'data'     => 'js:\'moduleClassName=\' + $(this).val()',
                'url'      =>  $url,
                'success'  => 'js:function(data){
                                if(data.dataToOrder == "true")
                                {
                                    $(".no-workflows-to-order-view").hide();
                                    $(".select-module-view").hide(); $("#save-order").show();
                                    $(".' . $inputDivId . '").html(data.content);
                                }
                                else
                                {
                                    $(".select-module-view").hide();
                                    $("#save-order").hide();
                                    $(".' . $inputDivId . '").html("");
                                    $(".no-workflows-to-order-view").show();
                                }}',
            ));
            $script = "$('#" . $id . "').unbind('change'); $('#" . $id . "').bind('change', function()
            {

                if($('#" . $id . "').val() == '')
                {
                    $('.no-workflows-to-order-view').hide();
                    $('.select-module-view').show(); $('#save-order').hide();
                    $('." . $inputDivId . "').html('');
                }
                else
                {
                    $ajaxSubmitScript
                }
            });";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('timeTriggerAttributeDropDownOnChangeScript', $script);
        }
    }
?>