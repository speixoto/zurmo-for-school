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
     * Class for working with the on-save workflow in the workflow wizard
     */
    class OnSaveWorkflowWizardView extends WorkflowWizardView
    {
        /**
         * @return string
         */
        public function getTitle()
        {
            return parent::getTitle() . ' - ' . Zurmo::t('WorkflowsModule', 'On-Save');
        }

        /**
         * @param WizardActiveForm $form
         * @return string
         */
        protected function renderContainingViews(WizardActiveForm $form)
        {
            $moduleForWorkflowWizardView        = new ModuleForWorkflowWizardView ($this->model,     $form);
            $triggersForWorkflowWizardView      = new TriggersForWorkflowWizardView($this->model,    $form, true);
            $actionsForWorkflowWizardView       = new ActionsForWorkflowWizardView($this->model,     $form, true);
            $emailMessagesForWorkflowWizardView   = new EmailMessagesForWorkflowWizardView($this->model,     $form, true);
            $generalDataForWorkflowWizardView   = new GeneralDataForWorkflowWizardView($this->model, $form, true);

            $gridView = new GridView(5, 1);
            $gridView->setView($moduleForWorkflowWizardView, 0, 0);
            $gridView->setView($triggersForWorkflowWizardView, 1, 0);
            $gridView->setView($actionsForWorkflowWizardView, 2, 0);
            $gridView->setView($emailMessagesForWorkflowWizardView, 3, 0);
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

                        if (linkId == '" . ModuleForWorkflowWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                WorkflowWizardForm::TRIGGERS_VALIDATION_SCENARIO . "');
                            $('#ModuleForWorkflowWizardView').hide();
                            " . $this->renderTreeViewAjaxScriptContent($formName, 'TriggersForWorkflowWizardView') . "
                            $('#TriggersForWorkflowWizardView').show();
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('40%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                        }
                        if (linkId == '" . TriggersForWorkflowWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                WorkflowWizardForm::ACTIONS_VALIDATION_SCENARIO . "');
                            $('#TriggersForWorkflowWizardView').hide();
                            $('#ActionsForWorkflowWizardView').show();
                            var actionsList = $('#ActionsForWorkflowWizardView').find('ul:first').children();
                            $.each(actionsList, function()
                            {
                                if ( $(this).hasClass('expanded-row') )
                                {
                                    $(this).toggleClass('expanded-row');
                                    $('.edit-dynamic-row-link', this).toggle();
                                    $('.toggle-me', this).toggle();
                                }
                            });
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('60%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                        }
                        if (linkId == '" . ActionsForWorkflowWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                WorkflowWizardForm::EMAIL_MESSAGES_VALIDATION_SCENARIO . "');
                            $('#ActionsForWorkflowWizardView').hide();
                            $('#EmailMessagesForWorkflowWizardView').show();
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('80%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                        }
                        if (linkId == '" . EmailMessagesForWorkflowWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                            WorkflowWizardForm::GENERAL_DATA_VALIDATION_SCENARIO . "');
                            $('#EmailMessagesForWorkflowWizardView').hide();
                            $('#GeneralDataForWorkflowWizardView').show();
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('100%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                        }

                        var rowData = $('#" . $formName . "').find('.attachLoadingTarget').data() || {};
                        if (rowData.purpose === 'validate-action')
                        {
                            $('#' + rowData.row.toString()).toggleClass('expanded-row');
                            $('#' + rowData.row.toString() + ' .toggle-me').toggle();
                            $('#' + rowData.row.toString() + ' .edit-dynamic-row-link').toggle();
                            $('#' + rowData.row.toString()).siblings().show();
                            $('#actionsNextLink').parent().parent().show();
                            $('#actionType').removeAttr('disabled');
                        }
                        if (linkId == '" . GeneralDataForWorkflowWizardView::getNextPageLinkId() . "')
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
                $('#" . TriggersForWorkflowWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . TriggersForWorkflowWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . WorkflowWizardForm::MODULE_VALIDATION_SCENARIO . "');
                        $('#" . WizardActiveForm::makeErrorsSummaryId(static::getFormId()) . "').hide();
                        $('#ModuleForWorkflowWizardView').show();
                        $('#TriggersForWorkflowWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('20%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );
                $('#" . ActionsForWorkflowWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . ActionsForWorkflowWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . WorkflowWizardForm::TRIGGERS_VALIDATION_SCENARIO . "');
                        $('#TriggersForWorkflowWizardView').show();
                        $('#ActionsForWorkflowWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('40%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );
                $('#" . EmailMessagesForWorkflowWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . EmailMessagesForWorkflowWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . WorkflowWizardForm::ACTIONS_VALIDATION_SCENARIO . "');
                        $('#ActionsForWorkflowWizardView').show();
                        $('#EmailMessagesForWorkflowWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('60%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );
                $('#" . GeneralDataForWorkflowWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . GeneralDataForWorkflowWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . WorkflowWizardForm::EMAIL_MESSAGES_VALIDATION_SCENARIO . "');
                        $('#EmailMessagesForWorkflowWizardView').show();
                        $('#GeneralDataForWorkflowWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('80%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );
            ");
        }
    }
?>