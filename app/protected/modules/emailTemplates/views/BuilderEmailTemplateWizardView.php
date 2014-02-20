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

    class BuilderEmailTemplateWizardView extends EmailTemplateWizardView
    {
        /**
         * @return string
         */
        public function getTitle()
        {
            return parent::getTitle() . ' - ' . Zurmo::t('EmailTemplatesModule', 'Builder');
        }

        protected function resolveContainingViews(WizardActiveForm $form)
        {
            $views              = array();
            $views[]            = new GeneralDataForEmailTemplateWizardView($this->model, $form);
            $views[]            = new SelectBaseTemplateForEmailTemplateWizardView($this->model, $form, true);
            $views[]            = new BuilderCanvasWizardView($this->model, $form, true);
            return $views;
        }

        protected function renderGeneralDataNextPageLinkScript($formName)
        {
            return "
                    if (linkId == '" . GeneralDataForEmailTemplateWizardView::getNextPageLinkId() . "')
                    {
                        " . $this->getSaveAjaxString($formName, false, GeneralDataForEmailTemplateWizardView::resolveAdditionalAjaxOptions($formName)) . "
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                                            BuilderEmailTemplateWizardForm::SELECT_BASE_TEMPLATE_VALIDATION_SCENARIO. "');
                        $('#GeneralDataForEmailTemplateWizardView').hide();
                        $('#SelectBaseTemplateForEmailTemplateWizardView').show();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('66%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                    }
                    $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading');
                    $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading-ajax-submit');
                    $('#" . $formName . "').find('.attachLoadingTarget').removeClass('attachLoadingTarget');
                    ";
        }

        protected function renderPreGeneralDataNextPageLinkScript($formName)
        {
            return "
                    if (linkId == '" . SelectBaseTemplateForEmailTemplateWizardView::getNextPageLinkId() . "')
                    {
                        " . $this->getSaveAjaxString($formName, false, SelectBaseTemplateForEmailTemplateWizardView::resolveAdditionalAjaxOptions($formName)) . "
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                                            BuilderEmailTemplateWizardForm::SERIALIZED_DATA_VALIDATION_SCENARIO . "');
                        $('#SelectBaseTemplateForEmailTemplateWizardView').hide();
                        $('#BuilderCanvasWizardView').show();
                        initEmailTemplateEditor();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('100%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                    }
                    if (linkId == '" . BuilderCanvasWizardView::getNextPageLinkId() . "')
                    {
                        " . $this->getSaveAjaxString($formName, false, BuilderCanvasWizardView::resolveAdditionalAjaxOptions($formName)) . "
                    }
                    if (linkId == '" . BuilderCanvasWizardView::getFinishLinkId() . "')
                    {
                        " . $this->getSaveAjaxString($formName, true, BuilderCanvasWizardView::resolveAdditionalAjaxOptions($formName)) . "
                    }
                    ";
        }

        protected function registerPostGeneralDataPreviousLinkScript()
        {
            Yii::app()->clientScript->registerScript('clickflow.selectBaseTemplatePreviousLink', "
                $('#" . SelectBaseTemplateForEmailTemplateWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . SelectBaseTemplateForEmailTemplateWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . BuilderEmailTemplateWizardForm::GENERAL_DATA_VALIDATION_SCENARIO . "');
                        $('#GeneralDataForEmailTemplateWizardView').show();
                        $('#SelectBaseTemplateForEmailTemplateWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('33%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );");
            Yii::app()->clientScript->registerScript('clickflow.builderCanvasPreviousLink', "
                $('#" . BuilderCanvasWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . BuilderCanvasWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . BuilderEmailTemplateWizardForm::SELECT_BASE_TEMPLATE_VALIDATION_SCENARIO . "');
                        $('#SelectBaseTemplateForEmailTemplateWizardView').show();
                        $('#BuilderCanvasWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('66%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );");
        }
    }
?>