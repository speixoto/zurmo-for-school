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
            $generalDataForWorkflowWizardView   = new GeneralDataForWorkflowWizardView($this->model, $form, true);

            $gridView = new GridView(5,1);
            $gridView->setView($moduleForWorkflowWizardView, 0, 0);
            $gridView->setView($timeTriggerForWorkflowWizardView, 1, 0);
            $gridView->setView($triggersForWorkflowWizardView, 2, 0);
            $gridView->setView($actionsForWorkflowWizardView, 3, 0);
            $gridView->setView($generalDataForWorkflowWizardView, 4, 0);
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
                            " . $this->renderTreeViewAjaxScriptContent($formName, 'TimeTriggerForWorkflowWizardView') . "
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
                            " . $this->renderTreeViewAjaxScriptContent($formName, 'ActionsForWorkflowWizardView') . "
                            $('#ActionsForWorkflowWizardView').show();
                        }
                        if(linkId == '" . DisplayAttributesForWorkflowWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                WorkflowWizardForm::GENERAL_DATA_VALIDATION_SCENARIO . "');
                            $('#ActionsForWorkflowWizardView').hide();
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
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                        WorkflowWizardForm::TRIGGERS_VALIDATION_SCENARIO . "');
                        $('#TriggersForWorkflowWizardView').show();
                        $('#ActionsForWorkflowWizardView').hide();
                        return false;
                    }
                );
                $('#" . GeneralDataForWorkflowWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . GeneralDataForWorkflowWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                        WorkflowWizardForm::ACTIONS_VALIDATION_SCENARIO . "');
                        $('#ActionsForWorkflowWizardView').show();
                        $('#GeneralDataForWorkflowWizardView').hide();
                        return false;
                    }
                );
            ");
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.elements.assets')) . '/SelectInputUtils.js', CClientScript::POS_END);
            $this->registerLinkedRemovalScript();
        }
    }
?>