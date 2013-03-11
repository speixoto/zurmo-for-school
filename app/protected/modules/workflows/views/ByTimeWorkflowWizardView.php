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
     * Class for working with the by-time workflow in the workflow wizard
     */
    class ByTimeWorkflowWizardView extends WorkflowWizardView
    {
        /**
         * @param WizardActiveForm $form
         * @return string
         */
        protected function renderContainingViews(WizardActiveForm $form)
        {
            $moduleForWorkflowWizardView        = new ModuleForWorkflowWizardView ($this->model,     $form);
            $timeTriggerForWorkflowWizardView   = new TimeTriggerForWorkflowWizardView($this->model, $form, true);
            $triggersForWorkflowWizardView      = new TriggersForWorkflowWizardView($this->model,    $form, true);
            $actionsForWorkflowWizardView       = new ActionsForWorkflowWizardView($this->model,     $form, true);
            $emailAlertsForWorkflowWizardView   = new EmailAlertsForWorkflowWizardView($this->model,     $form, true);
            $generalDataForWorkflowWizardView   = new GeneralDataForWorkflowWizardView($this->model, $form, true);

            $gridView = new GridView(6,1);
            $gridView->setView($moduleForWorkflowWizardView, 0, 0);
            $gridView->setView($timeTriggerForWorkflowWizardView, 1, 0);
            $gridView->setView($triggersForWorkflowWizardView, 2, 0);
            $gridView->setView($actionsForWorkflowWizardView, 3, 0);
            $gridView->setView($emailAlertsForWorkflowWizardView, 4, 0);
            $gridView->setView($generalDataForWorkflowWizardView, 5, 0);
            return $gridView->render();
        }

        /**
         * @param string $formName
         * @return string
         */
        protected function renderConfigSaveAjax($formName)
        {
            assert('is_string($formName)');
            return     "linkId = $('#" . $formName . "').find('.attachLoadingTarget').attr('id');
                        if(linkId == '" . ModuleForWorkflowWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                WorkflowWizardForm::TIME_TRIGGER_VALIDATION_SCENARIO . "');
                            $('#ModuleForWorkflowWizardView').hide();
                             " . $this->renderLoadTimeTriggerAttributeScriptContent($formName) . "
                            $('#TimeTriggerForWorkflowWizardView').show();

                        }
                        if(linkId == '" . TimeTriggerForWorkflowWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                WorkflowWizardForm::TRIGGERS_VALIDATION_SCENARIO . "');
                            $('#TimeTriggerForWorkflowWizardView').hide();
                            " . $this->renderTreeViewAjaxScriptContent($formName, 'TriggersForWorkflowWizardView') . "
                            $('#TriggersForWorkflowWizardView').show();

                        }
                        if(linkId == '" . TriggersForWorkflowWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                WorkflowWizardForm::ACTIONS_VALIDATION_SCENARIO . "');
                            $('#TriggersForWorkflowWizardView').hide();
                            $('#ActionsForWorkflowWizardView').show();
                        }
                        if(linkId == '" . ActionsForWorkflowWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                            WorkflowWizardForm::EMAIL_ALERTS_DATA_VALIDATION_SCENARIO . "');
                            $('#ActionsForWorkflowWizardView').hide();
                            $('#EmailAlertsForWorkflowWizardView').show();
                        }
                        if(linkId == '" . EmailAlertsForWorkflowWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                            WorkflowWizardForm::GENERAL_DATA_VALIDATION_SCENARIO . "');
                            $('#EmailAlertsForWorkflowWizardView').hide();
                            $('#GeneralDataForWorkflowWizardView').show();
                        }
                        if(linkId == '" . GeneralDataForWorkflowWizardView::getNextPageLinkId() . "')
                        {
                            " . $this->getSaveAjaxString($formName) . "
                        }
                        else
                        {
                            $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading');
                            $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading-ajax-submit');
                            $('#" . $formName . "').find('.attachLoadingTarget').removeClass('attachLoadingTarget');
                        }
            ";
        }

        protected function registerClickFlowScript()
        {
            Yii::app()->clientScript->registerScript('clickflow', "
                $('#" . ModuleForWorkflowWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . ModuleForWorkflowWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        url = '" . Yii::app()->createUrl('workflows/default/index') . "';
                        window.location.href = url;
                        return false;
                    }
                );
                $('#" . TimeTriggerForWorkflowWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . TimeTriggerForWorkflowWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                        WorkflowWizardForm::MODULE_VALIDATION_SCENARIO . "');
                        $('#" . WizardActiveForm::makeErrorsSummaryId(static::getFormId()) . "').hide();
                        $('#ModuleForWorkflowWizardView').show();
                        $('#TimeTriggerForWorkflowWizardView').hide();
                        return false;
                    }
                );
                $('#" . TriggersForWorkflowWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . TriggersForWorkflowWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                        WorkflowWizardForm::TIME_TRIGGER_VALIDATION_SCENARIO . "');
                        $('#TimeTriggerForWorkflowWizardView').show();
                        $('#TriggersForWorkflowWizardView').hide();
                        return false;
                    }
                );
                $('#" . ActionsForWorkflowWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . ActionsForWorkflowWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . WorkflowWizardForm::TRIGGERS_VALIDATION_SCENARIO . "');
                        $('#TriggersForWorkflowWizardView').show();
                        $('#EmailAlertsForWorkflowWizardView').hide();
                        return false;
                    }
                );
                $('#" . EmailAlertsForWorkflowWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . EmailAlertsForWorkflowWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . WorkflowWizardForm::ACTIONS_VALIDATION_SCENARIO . "');
                        $('#ActionsForWorkflowWizardView').show();
                        $('#EmailAlertsForWorkflowWizardView').hide();
                        return false;
                    }
                );
                $('#" . GeneralDataForWorkflowWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . GeneralDataForWorkflowWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . WorkflowWizardForm::EMAIL_ALERTS_VALIDATION_SCENARIO . "');
                        $('#EmailAlertsForWorkflowWizardView').show();
                        $('#GeneralDataForWorkflowWizardView').hide();
                        return false;
                    }
                );
            ");
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $this->registerTimeTriggerAttributeDropDownOnChangeScript();
        }

        /**
         * @param $formName
         * @return string
         */
        protected function renderLoadTimeTriggerAttributeScriptContent($formName)
        {
            assert('is_string($formName)');
            $url    =  Yii::app()->createUrl('workflows/default/getAvailableAttributesForTimeTrigger',
                array_merge($_GET, array('type' => $this->model->type)));
            $script = "
                $.ajax({
                    url : '" . $url . "',
                    type : 'POST',
                    data : $('#" . $formName . "').serialize(),
                    dataType: 'json',
                    success : function(data)
                    {
                        rebuildSelectInputFromDataAndLabels
                        ('ByTimeWorkflowWizardForm_timeTriggerAttribute', data);
                    },
                    error : function()
                    {
                        //todo: error call
                    }
                });
            ";
            return $script;
        }

        protected function registerTimeTriggerAttributeDropDownOnChangeScript()
        {
            $id         = 'ByTimeWorkflowWizardForm_timeTriggerAttribute';
            $inputDivId = 'time-trigger-container';
            $url        =  Yii::app()->createUrl('workflows/default/addOrChangeTimeTriggerAttribute',
                           array_merge($_GET, array('type' => $this->model->type)));
            // Begin Not Coding Standard
            $ajaxSubmitScript  = ZurmoHtml::ajax(array(
                'type'    => 'GET',
                'data'    => 'js:\'attributeIndexOrDerivedType=\' + $(this).val() +
                                 \'&moduleClassName=\' +
                                 $("input:radio[name=\"ByTimeWorkflowWizardForm[moduleClassName]\"]:checked").val()',
                'url'     =>  $url,
                'beforeSend' => 'js:function(){
                        $("#' . $inputDivId . '").html("<span class=\"loading z-spinner\"></span>");
                        attachLoadingSpinner("' . $inputDivId . '", true, "dark");
                        }',
                'success' => 'js:function(data){ $("#' . $inputDivId . '").html(data); }',
            ));
            $script = "$('#" . $id . "').unbind('change'); $('#" . $id . "').bind('change', function()
            {
                if($('#" . $id . "').val() == '')
                {
                    $('#" . $inputDivId . "').html('');
                }
                else
                {
                    $ajaxSubmitScript
                }
            }
            );";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('timeTriggerAttributeDropDownOnChangeScript', $script);
        }

        protected function registerModuleClassNameChangeScriptExtraPart()
        {
            $timeTriggerSelectId = get_class($this->model) . '_timeTriggerAttribute';
            return  "   $('#time-trigger-container').find('li').remove();
                        $('." . TimeTriggerForWorkflowWizardView::getZeroComponentsClassName() . "').show();
                        $('#" . $timeTriggerSelectId . "').find('option[value!=\"\"]').remove();
                    ";
        }
    }
?>