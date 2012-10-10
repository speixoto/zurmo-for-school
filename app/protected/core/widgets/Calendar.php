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

    Yii::import('zii.widgets.jui.CJuiInputWidget');

    /**
     * Widget for displaying JuiDatePicker as visible calendar.
     */
    class Calendar extends CJuiInputWidget
    {
        /**
         * @var string the locale ID (eg 'fr', 'de') for the language to be used by the date picker.
         * If this property is not set, I18N will not be involved. That is, the date picker will show in English.
         * You can force English language by setting the language attribute as '' (empty string)
         */
        public $language;

        /**
         * @var string The i18n Jquery UI script file. It uses scriptUrl property as base url.
         */
        public $i18nScriptFile = 'jquery-ui-i18n.min.js';

        /**
         * @var array The default options called just one time per request. This options will alter every other CJuiDatePicker instance in the page.
         * It has to be set at the first call of CJuiDatePicker widget in the request.
         */
        public $defaultOptions;

        public $dayEvents = array();

        protected $dataProvider;

        public $cssFile = null;

        /**
         * Initialize the Calendar Widget
         */
        public function init()
        {
            $this->themeUrl = Yii::app()->baseUrl . '/themes';
            $this->theme    = Yii::app()->theme->name;
            parent::init();
        }

        /**
         * This function overrides the run method from CJuiDatePicker and fixes the jQuery issue for the Datepicker showing
         * wrong language in the portlet views popup.
         */
        public function run()
        {
            //Invalid HTML using a name on a div
            //list($name, $id) = $this->resolveNameID();
            if (isset($this->htmlOptions['id']))
            {
                $id = $this->htmlOptions['id'];
            }
            else
            {
                $this->htmlOptions['id'] = $id;
            }
            //Invalid HTML using a name on a div
            if (isset($this->htmlOptions['name']))
            {
                unset($this->htmlOptions['name']);
            }
            $id = $this->htmlOptions['id'] = $this->htmlOptions['id'].'_container';

            echo ZurmoHtml::tag('div', $this->htmlOptions, '');
            //renderEvents before the datePicker.
            $this->renderEvents($id);

            //Add beforeShowDate as options
            // Begin Not Coding Standard
            $this->options['beforeShowDay'] = "js:function(date) {
                var event = calendarEvents[date];
                if (event) {
                    return [true, event.className, event.text];
                }
                else {
                    return [true, '', ''];
                }
            }";
            // End Not Coding Standard
            $options = CJavaScript::encode($this->options);
            if ($this->language != '' && $this->language != 'en')
            {
                $this->registerScriptFile($this->i18nScriptFile);
                $js = "jQuery('#{$id}').datepicker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional['{$this->language}'], {$options}));";
            }
            else
            {
                $js = "jQuery('#{$id}').datepicker($options);";
            }
            $js .= 'addSpansToDatesOnCalendar("' . $id . '");';
            $cs = Yii::app()->getClientScript();
            if (isset($this->defaultOptions))
            {
                $this->registerScriptFile($this->i18nScriptFile);
                $cs->registerScript(__CLASS__,     $this->defaultOptions !== null?'jQuery.datepicker.setDefaults('.CJavaScript::encode($this->defaultOptions).');':'');
            }
            $cs->registerScript(__CLASS__. '#' . $id, $js);
            $baseScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.core.widgets.assets'));
            $cs->registerScriptFile($baseScriptUrl . '/calendar/Calendar.js', CClientScript::POS_END);
        }

        protected function renderEvents($id)
        {
            $script = "var calendarEvents = {}; \n";
            if (count($this->dayEvents) > 0)
            {
                foreach ($this->dayEvents as $event)
                {
                    $dateTimestamp = DateTimeUtil::convertDbFormatDateTimeToTimestamp($event['dbDate']);
                    $dateForJavascript = date('M j, Y', $dateTimestamp);
                    $script .= "calendarEvents[new Date('" . $dateForJavascript . "')] = new CalendarEvent('" . $event['label'] . "', '" . $event['className'] . "'); \n";
                }
            }
            $cs = Yii::app()->getClientScript();
            $cs->registerScript(__CLASS__. '#' . $id . 'dayEvents', $script);
        }
    }
?>
