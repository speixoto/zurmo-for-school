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
     * Displays first series/range inputs and second series/range inputs. Utilized in conjunction with selecting a
     * chart for reporting. @see ChartTypeRadioStaticDropDownForReportElement
     */
    class MixedChartRangeAndSeriesElement extends Element
    {
        /**
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            $startingDivStyleFirstValue         = null;
            $startingDivStyleSecondValue        = null;
            if ($this->model->type == null)
            {
                $startingDivStyleFirstValue = "display:none;";
            }

            if (!in_array($this->model->type, array(ChartRules::TYPE_STACKED_BAR_3D, ChartRules::TYPE_STACKED_COLUMN_3D)))
            {
                $startingDivStyleSecondValue = "display:none;";
            }
            $content  = ZurmoHtml::tag('span', array('class' => 'first-series-and-range-area',
                                                     'style' => $startingDivStyleFirstValue),
                                       $this->renderEditableFirstSeriesContent() .
                                       $this->renderEditableFirstRangeContent());
            $content .= ZurmoHtml::tag('span', array('class' => 'second-series-and-range-area',
                                                     'style' => $startingDivStyleSecondValue),
                                       $this->renderEditableSecondSeriesContent() .
                                       $this->renderEditableSecondRangeContent());
            return $content;
        }

        protected function renderEditableFirstSeriesContent()
        {
           $htmlOptions = array(
                'empty' => Yii::t('Default', '(None)'),
                'id'    => $this->getFirstSeriesEditableInputId(),
           );
           $label        = $this->form->labelEx($this->model, 'firstSeries',
                                                array('for' => $this->getFirstSeriesEditableInputId()));
           $content      = ZurmoHtml::dropDownList($this->getFirstSeriesEditableInputName(),
                                                   $this->model->firstSeries,
                                                   $this->model->getAvailableFirstSeriesDataAndLabels(),
                                                   $htmlOptions
                                                   );
            $error       = $this->form->error($this->model, 'firstSeries',
                           array('inputID' => $this->getFirstSeriesEditableInputId()));
            return $label . $content . $error;
        }

        protected function renderEditableFirstRangeContent()
        {
           $htmlOptions = array(
                'empty' => Yii::t('Default', '(None)'),
                'id'    => $this->getFirstRangeEditableInputId(),
           );
           $label        = $this->form->labelEx($this->model, 'firstRange',
                                                array('for' => $this->getFirstRangeEditableInputId()));
           $content      = ZurmoHtml::dropDownList($this->getFirstRangeEditableInputName(),
                                                   $this->model->firstRange,
                                                   $this->model->getAvailableFirstRangeDataAndLabels(),
                                                   $htmlOptions
                                                   );
            $error       = $this->form->error($this->model, 'firstRange',
                           array('inputID' => $this->getFirstRangeEditableInputId()));
            return $label . $content . $error;
        }

        protected function renderEditableSecondSeriesContent()
        {
           $htmlOptions = array(
                'empty' => Yii::t('Default', '(None)'),
                'id'    => $this->getSecondSeriesEditableInputId(),
           );
           $label        = $this->form->labelEx($this->model, 'secondSeries',
                                                array('for' => $this->getSecondSeriesEditableInputId()));
           $content      = ZurmoHtml::dropDownList($this->getSecondSeriesEditableInputName(),
                                                   $this->model->secondSeries,
                                                   $this->model->getAvailableSecondSeriesDataAndLabels(),
                                                   $htmlOptions
                                                   );
            $error       = $this->form->error($this->model, 'secondSeries',
                           array('inputID' => $this->getSecondSeriesEditableInputId()));
            return $label . $content . $error;
        }

        protected function renderEditableSecondRangeContent()
        {
           $htmlOptions = array(
                'empty' => Yii::t('Default', '(None)'),
                'id'    => $this->getSecondRangeEditableInputId(),
           );
           $label        = $this->form->labelEx($this->model, 'secondRange',
                                                array('for' => $this->getSecondRangeEditableInputId()));
           $content      = ZurmoHtml::dropDownList($this->getSecondRangeEditableInputName(),
                                                   $this->model->secondRange,
                                                   $this->model->getAvailableSecondRangeDataAndLabels(),
                                                   $htmlOptions
                                                   );
            $error       = $this->form->error($this->model, 'secondRange',
                           array('inputID' => $this->getSecondRangeEditableInputId()));
            return $label . $content . $error;
        }


        /**
         * Renders the attribute from the model.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function renderLabel()
        {
            $label = $this->getFormattedAttributeLabel();
            if ($this->form === null)
            {
                return $label;
            }
            return ZurmoHtml::label($label, false);
        }

        /**
         * Render during the Editable render
         * (non-PHPdoc)
         * @see Element::renderError()
         */
        protected function renderError()
        {
        }

        protected function getFirstSeriesEditableInputId()
        {
            return $this->getEditableInputId('firstSeries');
        }

        protected function getFirstRangeEditableInputId()
        {
            return $this->getEditableInputId('firstRange');
        }

        protected function getSecondSeriesEditableInputId()
        {
            return $this->getEditableInputId('secondSeries');
        }

        protected function getSecondRangeEditableInputId()
        {
            return $this->getEditableInputId('secondRange');
        }

        protected function getFirstSeriesEditableInputName()
        {
            return $this->getEditableInputName('firstSeries');
        }

        protected function getFirstRangeEditableInputName()
        {
            return $this->getEditableInputName('firstRange');
        }

        protected function getSecondSeriesEditableInputName()
        {
            return $this->getEditableInputName('secondSeries');
        }

        protected function getSecondRangeEditableInputName()
        {
            return $this->getEditableInputName('secondRange');
        }
    }
?>