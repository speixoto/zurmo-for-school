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
     * Displays a date/time localized
     * display.
     */
    class DateTimeElement extends Element
    {
        /**
         * Render a datetime JUI widget
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            $htmlOptionsFromParams   = $this->getHtmlOptions();
            $htmlOptions             = $this->resolveHtmlOptions();
            $htmlOptions             = array_merge($htmlOptionsFromParams, $htmlOptions);
            $themePath = Yii::app()->themeManager->baseUrl . '/' . Yii::app()->theme->name;
            $value     = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                            $this->model->{$this->attribute},
                            DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH,
                            DateTimeUtil::DATETIME_FORMAT_TIME_WIDTH,
                            true);
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("EditableDateTimeElement");
            $cClipWidget->widget('application.core.widgets.ZurmoJuiDateTimePicker', array(
                'attribute'   => $this->attribute,
                'value'       => $value,
                'htmlOptions' => $htmlOptions,
                'options'     => $this->resolveDatePickerOptions()
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['EditableDateTimeElement'];
            return ZurmoHtml::tag('div', array('class' => 'has-date-select'), $content);
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            if ($this->model->{$this->attribute} != null)
            {
                $content = DateTimeUtil::
                           convertDbFormattedDateTimeToLocaleFormattedDisplay(
                               $this->model->{$this->attribute});
                return ZurmoHtml::encode($content);
            }
        }

        /**
         * Resolve html options
         * @return array
         */
        protected function resolveHtmlOptions()
        {
            return array(
                    'id'              => $this->getEditableInputId(),
                    'name'            => $this->getEditableInputName(),
                    'disabled'        => $this->getDisabledValue(),
                );
        }

        /**
         * Resolve datepicker options
         * @return array
         */
        protected function resolveDatePickerOptions()
        {
            if ($this->getDisabledValue() && $this->isDatePickerDisabled())
            {
                return array('disabled' => true);
            }
            else
            {
                return array();
            }
        }

        /**
         * Check if datepicker is disabled
         * @return boolean
         */
        protected function isDatePickerDisabled()
        {
            if (isset($this->params['datePickerDisabled']))
            {
                return $this->params['datePickerDisabled'];
            }
            return true;
        }
    }
?>