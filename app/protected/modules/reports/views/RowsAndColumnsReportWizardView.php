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
     * Class for working with the row and column reports in the report wizard
     */
    class RowsAndColumnsReportWizardView extends ReportWizardView
    {
        /**
         * @param WizardActiveForm $form
         * @return string
         */
        protected function renderContainingViews(WizardActiveForm $form)
        {
            $moduleForReportWizardView            = new ModuleForReportWizardView ($this->model, $form);
            $filtersForReportWizardView           = new FiltersForReportWizardView($this->model, $form, true);
            $displayAttributesForReportWizardView = new DisplayAttributesForReportWizardView($this->model, $form, true);
            $orderBysForReportWizardView          = new OrderBysForReportWizardView($this->model, $form, true);
            $generalDataForReportWizardView       = new GeneralDataForReportWizardView($this->model, $form, true);

            $gridView = new GridView(5,1);
            $gridView->setView($moduleForReportWizardView, 0, 0);
            $gridView->setView($filtersForReportWizardView, 1, 0);
            $gridView->setView($displayAttributesForReportWizardView, 2, 0);
            $gridView->setView($orderBysForReportWizardView, 3, 0);
            $gridView->setView($generalDataForReportWizardView, 4, 0);
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
                        if(linkId == '" . ModuleForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::FILTERS_VALIDATION_SCENARIO . "');
                            $('#ModuleForReportWizardView').hide();
                            " . $this->renderTreeViewAjaxScriptContent($formName, 'FiltersForReportWizardView') . "
                            $('#FiltersForReportWizardView').show();

                        }
                        if(linkId == '" . FiltersForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO . "');
                            $('#FiltersForReportWizardView').hide();
                            " . $this->renderTreeViewAjaxScriptContent($formName, 'DisplayAttributesForReportWizardView') . "
                            $('#DisplayAttributesForReportWizardView').show();

                        }
                        if(linkId == '" . DisplayAttributesForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::ORDER_BYS_VALIDATION_SCENARIO . "');
                            $('#DisplayAttributesForReportWizardView').hide();
                            " . $this->renderTreeViewAjaxScriptContent($formName, 'OrderBysForReportWizardView') . "
                            $('#OrderBysForReportWizardView').show();
                        }
                        if(linkId == '" . OrderBysForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::GENERAL_DATA_VALIDATION_SCENARIO . "');
                            $('#OrderBysForReportWizardView').hide();
                            $('#GeneralDataForReportWizardView').show();
                        }
                        if(linkId == '" . GeneralDataForReportWizardView::getNextPageLinkId() . "')
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
                $('#" . ModuleForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . ModuleForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        url = '" . Yii::app()->createUrl('reports/default/index') . "';
                        window.location.href = url;
                        return false;
                    }
                );
                $('#" . FiltersForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . FiltersForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . ReportWizardForm::MODULE_VALIDATION_SCENARIO . "');
                        $('#" . WizardActiveForm::makeErrorsSummaryId(static::getFormId()) . "').hide();
                        $('#ModuleForReportWizardView').show();
                        $('#FiltersForReportWizardView').hide();
                        return false;
                    }
                );
                $('#" . DisplayAttributesForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . DisplayAttributesForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . ReportWizardForm::FILTERS_VALIDATION_SCENARIO . "');
                        $('#FiltersForReportWizardView').show();
                        $('#DisplayAttributesForReportWizardView').hide();
                        return false;
                    }
                );
                $('#" . OrderBysForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . OrderBysForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . ReportWizardForm::DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO . "');
                        $('#DisplayAttributesForReportWizardView').show();
                        $('#OrderBysForReportWizardView').hide();
                        return false;
                    }
                );
                $('#" . GeneralDataForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . GeneralDataForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . ReportWizardForm::ORDER_BYS_VALIDATION_SCENARIO . "');
                        $('#OrderBysForReportWizardView').show();
                        $('#GeneralDataForReportWizardView').hide();
                        return false;
                    }
                );
            ");
        }
    }
?>