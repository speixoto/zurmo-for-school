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
     * Widget for displaying full calendar.
     */
    class FullCalendar extends ZurmoWidget
    {
        public $inputId;

        public $startDate;

        public $endDate;

        public $defaultView;

        /**
         * Initialize the Calendar Widget
         */
        public function init()
        {
            parent::init();
        }

        public function run()
        {
            $defaultView   = ZurmoConfigurationUtil::getByUserAndModuleName(Yii::app()->user->userModel,
                                                                                        'CalendarsModule', 'myCalendarDateRangeType');
            $startDate     = $this->startDate;
            $endDate       = $this->endDate;
            $url           = Yii::app()->createUrl('calendars/default/getEvents');
            $cs            = Yii::app()->getClientScript();
            $baseScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.core.widgets.assets'));
            $cs->registerScriptFile($baseScriptUrl . '/fullCalendar/fullcalendar.min.js', ClientScript::POS_END);
            $cs->registerCssFile($baseScriptUrl . '/fullCalendar/fullcalendar.css');
            $inputId       = $this->inputId;
            $script        = "$(document).on('ready', function() {
                                    $('#{$inputId}').fullCalendar({
                                                                    editable: true,
                                                                    header: {
                                                                                left: 'prev,next today',
                                                                                center: 'title',
                                                                                right: 'month,agendaWeek,agendaDay'
                                                                            },
                                                                    /*events: function(start, end, callback) {
                                                                                        var selectedMyCalendars = getSelectedCalendars('.mycalendar');
                                                                                        var selectedSharedCalendars = getSelectedCalendars('.sharedcalendar');
                                                                                        getCalendarEvents('$url', '{$startDate}', '{$endDate}', callback);
                                                                                    },*/
                                                                    events:  {
                                                                                url : '$url',
                                                                                data :function()
                                                                                {
                                                                                    var selectedMyCalendars = getSelectedCalendars('.mycalendar');
                                                                                    var selectedSharedCalendars = getSelectedCalendars('.sharedcalendar');
                                                                                    return {
                                                                                        selectedMyCalendarIds : selectedMyCalendars,
                                                                                        selectedSharedCalendarIds : selectedSharedCalendars,
                                                                                        startDate      : '{$startDate}',
                                                                                        endDate        : '{$endDate}'
                                                                                        }
                                                                                }
                                                                            },
                                                                    loading: function(bool)
                                                                            {
                                                                               if (bool)
                                                                               {
                                                                                   $(this).makeLargeLoadingSpinner(true, '#calendar');
                                                                               }
                                                                               else
                                                                               {
                                                                                   $(this).makeLargeLoadingSpinner(false, '#calendar');
                                                                               }
                                                                            },
                                                                     defaultView: '{$defaultView}'
                                                                    });

                                        });";
            $nextPreviousScript = "$('body').on('click', '.fc-button-next span', function(){
                                                    var startDate = getCalendarStartDate('{$inputId}');
                                                    var endDate = getCalendarEndDate('{$inputId}');
                                                    refreshCalendarEvents('{$url}', '{$startDate}', '{$endDate}');
                                                   });


                                    $('body').on('click', '.fc-button-prev span', function(){
                                                    var startDate = getCalendarStartDate('{$inputId}');
                                                    var endDate = getCalendarEndDate('{$inputId}');
                                                    refreshCalendarEvents('{$url}', '{$startDate}', '{$endDate}');
                                                   });";
          $cs->registerScript('loadCalendarScript', $script, ClientScript::POS_END);
          $cs->registerScript('nextPrevCalendarScript', $nextPreviousScript, ClientScript::POS_END);
        }
    }
?>
