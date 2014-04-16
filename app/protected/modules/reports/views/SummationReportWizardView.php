<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Class for working with the summation reports in the report wizard
     */
    class SummationReportWizardView extends ReportWizardView
    {
        /**
         * @return string
         */
        public function getTitle()
        {
            return parent::getTitle() . ' - ' . Zurmo::t('ReportsModule', 'Summation');
        }

        /**
         * @param WizardActiveForm $form
         * @return string
         */
        protected function renderContainingViews(WizardActiveForm $form)
        {
            $moduleForReportWizardView            = new ModuleForReportWizardView ($this->model, $form);
            $filtersForReportWizardView           = new FiltersForReportWizardView($this->model, $form, true);
            $groupBysForReportWizardView          = new GroupBysForReportWizardView($this->model, $form, true);
            $displayAttributesForReportWizardView = new DisplayAttributesForReportWizardView($this->model, $form, true);
            $drillDownDisplayAttributesForReportWizardView = new DrillDownDisplayAttributesForReportWizardView($this->model, $form, true);
            $orderBysForReportWizardView          = new OrderBysForReportWizardView($this->model, $form, true);
            $chartForReportWizardView             = new ChartForReportWizardView($this->model, $form, true);
            $generalDataForReportWizardView       = new GeneralDataForReportWizardView($this->model, $form, true);

            $gridView = new GridView(8, 1);
            $gridView->setView($moduleForReportWizardView, 0, 0);
            $gridView->setView($filtersForReportWizardView, 1, 0);
            $gridView->setView($groupBysForReportWizardView, 2, 0);
            $gridView->setView($displayAttributesForReportWizardView, 3, 0);
            $gridView->setView($drillDownDisplayAttributesForReportWizardView, 4, 0);
            $gridView->setView($orderBysForReportWizardView, 5, 0);
            $gridView->setView($chartForReportWizardView, 6, 0);
            $gridView->setView($generalDataForReportWizardView, 7, 0);
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
                        if (linkId == '" . ModuleForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::FILTERS_VALIDATION_SCENARIO . "');
                            $('#ModuleForReportWizardView').hide();
                            " . static::renderTreeViewAjaxScriptContent($formName, 'FiltersForReportWizardView', $this->model->type) . "
                            $('#FiltersForReportWizardView').show();
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('25%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                        }
                        if (linkId == '" . FiltersForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::GROUP_BYS_VALIDATION_SCENARIO . "');
                            $('#FiltersForReportWizardView').hide();
                            " . static::renderTreeViewAjaxScriptContent($formName, 'GroupBysForReportWizardView', $this->model->type) . "
                            $('#GroupBysForReportWizardView').show();
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('37.5%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                        }
                        if (linkId == '" . GroupBysForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO . "');
                            $('#GroupBysForReportWizardView').hide();
                            " . static::renderTreeViewAjaxScriptContent($formName, 'DisplayAttributesForReportWizardView', $this->model->type) . "
                            $('#DisplayAttributesForReportWizardView').show();
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('50%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                        }
                        if (linkId == '" . DisplayAttributesForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::DRILL_DOWN_DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO . "');
                            $('#DisplayAttributesForReportWizardView').hide();
                            " . static::renderTreeViewAjaxScriptContent($formName, 'DrillDownDisplayAttributesForReportWizardView', $this->model->type) . "
                            $('#DrillDownDisplayAttributesForReportWizardView').show();
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('62.5%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                        }
                        if (linkId == '" . DrillDownDisplayAttributesForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::ORDER_BYS_VALIDATION_SCENARIO . "');
                            $('#DrillDownDisplayAttributesForReportWizardView').hide();
                            " . static::renderTreeViewAjaxScriptContent($formName, 'OrderBysForReportWizardView', $this->model->type) . "
                            $('#OrderBysForReportWizardView').show();
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('75%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                        }
                        if (linkId == '" . OrderBysForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::CHART_VALIDATION_SCENARIO . "');
                            $('#OrderBysForReportWizardView').hide();
                            " . static::renderLoadChartSeriesAndRangesScriptContent($formName) . "
                            $('#ChartForReportWizardView').show();
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('87.5%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                        }
                        if (linkId == '" . ChartForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::GENERAL_DATA_VALIDATION_SCENARIO . "');
                            $('#ChartForReportWizardView').hide();
                            $('#GeneralDataForReportWizardView').show();
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('100%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                        }
                        if (linkId == '" . GeneralDataForReportWizardView::getNextPageLinkId() . "')
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
                        url = document.referrer;
                        window.location.href = url;
                        return false;
                    }
                );
                $('#" . FiltersForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . FiltersForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                        ReportWizardForm::MODULE_VALIDATION_SCENARIO . "');
                        $('#" . WizardActiveForm::makeErrorsSummaryId(static::getFormId()) . "').hide();
                        $('#ModuleForReportWizardView').show();
                        $('#FiltersForReportWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('12.5%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );
                $('#" . GroupBysForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . GroupBysForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                        ReportWizardForm::FILTERS_VALIDATION_SCENARIO . "');
                        $('#FiltersForReportWizardView').show();
                        $('#GroupBysForReportWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('25%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );
                $('#" . DisplayAttributesForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . DisplayAttributesForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                        ReportWizardForm::GROUP_BYS_VALIDATION_SCENARIO . "');
                        $('#GroupBysForReportWizardView').show();
                        $('#DisplayAttributesForReportWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('37.5%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );
                $('#" . DrillDownDisplayAttributesForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . DrillDownDisplayAttributesForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                        ReportWizardForm::DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO . "');
                        $('#DisplayAttributesForReportWizardView').show();
                        $('#DrillDownDisplayAttributesForReportWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('50%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );
                $('#" . OrderBysForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . OrderBysForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                        ReportWizardForm::DRILL_DOWN_DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO . "');
                        $('#DrillDownDisplayAttributesForReportWizardView').show();
                        $('#OrderBysForReportWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('62.5%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );
                $('#" . ChartForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . ChartForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                        ReportWizardForm::ORDER_BYS_VALIDATION_SCENARIO . "');
                        $('#OrderBysForReportWizardView').show();
                        $('#ChartForReportWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('75%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );
                $('#" . GeneralDataForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . GeneralDataForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                        ReportWizardForm::CHART_VALIDATION_SCENARIO . "');
                        $('#ChartForReportWizardView').show();
                        $('#GeneralDataForReportWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('87.5%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );
            ");
        }

        /**
         * @param $formName
         * @return string
         */
        protected function renderLoadChartSeriesAndRangesScriptContent($formName)
        {
            assert('is_string($formName)');
            $url    =  Yii::app()->createUrl(static::getModuleId() . '/' . static::getControllerId() . '/getAvailableSeriesAndRangesForChart',
                       array_merge($_GET, array('type' => $this->model->type)));
            // Begin Not Coding Standard
            $script = "
                $.ajax({
                    url : '" . $url . "',
                    type : 'POST',
                    data : $('#" . $formName . "').serialize(),
                    dataType: 'json',
                    success : function(data)
                    {
                        rebuildSelectInputFromDataAndLabels
                        ('SummationReportWizardForm_ChartForReportForm_firstSeries', data.firstSeriesDataAndLabels);
                        rebuildSelectInputFromDataAndLabels
                        ('SummationReportWizardForm_ChartForReportForm_firstRange', data.firstRangeDataAndLabels);
                        rebuildSelectInputFromDataAndLabels
                        ('SummationReportWizardForm_ChartForReportForm_secondSeries', data.secondSeriesDataAndLabels);
                        rebuildSelectInputFromDataAndLabels
                        ('SummationReportWizardForm_ChartForReportForm_secondRange', data.secondRangeDataAndLabels);
                    },
                    error : function()
                    {
                        //todo: error call
                    }
                });
            ";
            // End Not Coding Standard
            return $script;
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.elements.assets')) . '/SelectInputUtils.js', CClientScript::POS_END);
            $this->registerLinkedRemovalScript();
        }

        protected function registerLinkedRemovalScript()
        {
            Yii::app()->clientScript->registerScript('linkedRemovalScript', "
                //When a group by is removed, remove the corresponding display column and/or order by column if
                //necessary
                $('#GroupBysForReportWizardView').find('.remove-dynamic-row-link').live('click', function()
                    {
                        var inputIdBeingRemoved = $(this).prev().find('input').first().val();
                        $('#DisplayAttributesForReportWizardView').find('.dynamic-row').each(function()
                            {
                                if (inputIdBeingRemoved == $(this).find('input').first().val())
                                {
                                    $(this).parent().remove();
                                }
                            }
                        );
                        $('#OrderBysForReportWizardView').find('.dynamic-row').each(function()
                            {
                                if (inputIdBeingRemoved == $(this).find('input').first().val())
                                {
                                    $(this).parent().remove();
                                }
                            }
                        );
                    }
                );
            ");
        }

        protected function registerModuleClassNameChangeScriptExtraPart()
        {
            return  "   $('#OrderBysForReportWizardView').find('.dynamic-rows').find('ul:first').find('li').remove();
                        $('#OrderBysTreeArea').html('');
                        $('." . OrderBysForReportWizardView::getZeroComponentsClassName() . "').show();
                        $('#GroupBysForReportWizardView').find('.dynamic-rows').find('ul:first').find('li').remove();
                        $('#GroupBysTreeArea').html('');
                        $('." . GroupBysForReportWizardView::getZeroComponentsClassName() . "').show();
                        $('#DrillDownDisplayAttributesForReportWizardView').find('.dynamic-rows').find('ul:first').find('li').remove();
                        $('#DrillDownDisplayAttributesTreeArea').html('');
                        $('." . DrillDownDisplayAttributesForReportWizardView::getZeroComponentsClassName() . "').show();
                        $('input:radio[name=\"SummationReportWizardForm[ChartForReportForm][type]\"]').filter('[value=\"\"]').attr('checked', true)
                        onChangeChartType($('.chart-selector:checked'));
                    ";
        }
    }
?>