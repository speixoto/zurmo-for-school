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
     * View class for the chart component for the report wizard user interface
     */
    class ChartForReportWizardView extends ComponentForReportWizardView
    {
        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('ReportsModule', 'Select a Chart');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'chartPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'chartNextLink';
        }

        public function registerScripts()
        {
            parent::registerScripts();
            $chartTypesRequiringSecondInputs = ChartRules::getChartTypesRequiringSecondInputs();
            $script = '
                if($(".chart-selector:checked").val() != "")
                {
                    $("#series-and-range-areas").detach().insertAfter( $(".chart-selector:checked").parent()).removeClass("hidden-element");
                }
                $(".chart-selector").live("change", function()
                    {
                        onChangeChartType(this);
                    }
                );
                function onChangeChartType(changedChartObject)
                {
                    $("#series-and-range-areas").detach().insertAfter( $(changedChartObject).parent()  ).removeClass("hidden-element");
                    arr = ' . CJSON::encode($chartTypesRequiringSecondInputs) . ';
                    if($(changedChartObject).val() == "")
                    {
                        $("#series-and-range-areas").addClass("hidden-element")
                        $(".first-series-and-range-area").hide();
                        $(".first-series-and-range-area").find("select option:selected").removeAttr("selected");
                        $(".first-series-and-range-area").find("select").prop("disabled", true);
                    }
                    else
                    {
                        $(".first-series-and-range-area").show();
                        $(".first-series-and-range-area").find("select").prop("disabled", false);
                    }
                    if ($.inArray($(changedChartObject).val(), arr) != -1)
                    {
                        $(".second-series-and-range-area").show();
                        $(".second-series-and-range-area").find("select").prop("disabled", false);
                    }
                    else
                    {
                        $(".second-series-and-range-area").hide();
                        $(".second-series-and-range-area").find("select option:selected").removeAttr("selected");
                        $(".second-series-and-range-area").find("select").prop("disabled", true);
                    }
                }
            ';
            Yii::app()->getClientScript()->registerScript('ChartChangingScript', $script);
        }

        /**
         * @return bool
         */
        protected function isListContentSortable()
        {
            return true;
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $inputPrefixData   = array(get_class($this->model), get_class($this->model->chart));
            $this->form->setInputPrefixData($inputPrefixData);
            $params            = array('inputPrefix' => $inputPrefixData);
            $content           = '<div class="attributesContainer">';
            $element           = new ChartTypeRadioStaticDropDownForReportElement($this->model->chart, 'type', $this->form,
                array_merge($params, array('addBlank' => true)));
            $leftSideContent   = $element->render();
            $element           = new MixedChartRangeAndSeriesElement($this->model->chart, null, $this->form, $params);
            $content          .= ZurmoHtml::tag('div', array('class' => 'panel'), $leftSideContent);
            $rightSideContent  = ZurmoHtml::tag('div', array(), $element->render());
            $rightSideContent  = ZurmoHtml::tag('div', array('class' => 'buffer'), $rightSideContent);
            $content          .= ZurmoHtml::tag('div', array('id' => 'series-and-range-areas', 'class' => 'right-side-edit-view-panel hidden-element'), $rightSideContent);
            $content          .= '</div>';
            $this->form->clearInputPrefixData();
            $this->registerScripts();
            return $content;
        }
    }
?>