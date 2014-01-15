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

        public $events;

        /**
         * Initialize the Calendar Widget
         */
        public function init()
        {
            parent::init();
        }

        public function run()
        {
            $events = $this->events;
            $cs = Yii::app()->getClientScript();
            $baseScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.core.widgets.assets'));
            $cs->registerScriptFile($baseScriptUrl . '/fullCalendar/fullcalendar.min.js', ClientScript::POS_END);
            $cs->registerCssFile($baseScriptUrl . '/fullCalendar/fullcalendar.css');
            $inputId = $this->inputId;
            $cs->registerScript('loadcalendar',
                                "$(document).ready(function() {
                                    $('#{$inputId}').fullCalendar({
                                editable: true,
                                events: [{$events}],
                                header: {
                                            left: 'prev,next today',
                                            center: 'title',
                                            right: 'month,agendaWeek,agendaDay'
                                        },
                                }); });", ClientScript::POS_END);
            $cs->registerCss('calendarcss', '
                                                #FullCalendarForCombinedView .fc-content
                                                {
                                                  clear:none;
                                                  zoom:1;
                                                }
                                                .wrapper{
                                                        position: relative;
                                                    }
                                                .view-toolbar-container{
                                                    position: absolute;
                                                    top: 6px;
                                                    right: 0;
                                                }
                                                #calendar {
                                                    width: 100%;
                                                    margin: 0 auto;
                                                }
                                                .fc-header{
                                                    text-transform: capitalize;
                                                }
                                                #calendar td{
                                                    padding: 0;
                                                }
                                                .calendar-view .left-column{
                                                    width: 25%;
                                                }
                                                .calendar-view .right-column{
                                                    width: 75%;
                                                }
                                                .calendars-list{
                                                    margin-top: 30px;
                                                }
                                                .calendars-list ul{
                                                    list-style: none;
                                                    padding: 0;
                                                    margin: 0;
                                                }
                                                .calendars-list > ul > li{
                                                    display: block;
                                                    padding: 5px 0;
                                                    line-height: 1;
                                                }
                                                .calendars-list .edit-row-menu > li > a:before{
                                                    line-height: 12px;
                                                }
                                                .calendars-list  a{
                                                    color: #545454 !important;
                                                }
                                                .cal-color{
                                                    display: inline-block;
                                                    width: 12px;
                                                    height: 12px;
                                                    border-radius: 3px;
                                                    background: #3A87AD;
                                                    margin-right: 4px;
                                                    position: relative;
                                                    top: 1px;
                                                }
                                                .cal-color.outline{
                                                    background: none;
                                                    border:1px solid salmon;
                                                }
                                                .calendars-list + .calendars-list{
                                                    margin-top:30px;
                                                }
                                                .calendars-list + .calendars-list .cal-color{
                                                    background: darkgoldenrod;
                                                }
                                                .calendars-list + .calendars-list .cal-color.outline{
                                                    background: none;
                                                    border:1px solid rosybrown;
                                                }
                                                .task-subscribers{
                                                    border-top:none;
                                                    padding: 0 0 0 16px;
                                                    margin-top:5px;
                                                    box-shadow: none;
                                                }
                                                .quick-insert{
                                                    padding: 0;
                                                    margin-top: 30px;
                                                }
                                                .quick-insert .details-item-editable{
                                                    padding-left: 0;
                                                    padding-bottom: 5px;
                                                }
                                                .quick-insert .simple-link{
                                                    margin: 5px 0 0 0;
                                                    display: block;
                                                }
                                                .symbly{
                                                     font-weight:normal !important;
                                                     font-size:20px;
                                                     font-family: "zurmo_gamification_symbly_rRg";
                                                }');
        }
    }
?>
