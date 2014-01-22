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

    class CombinedCalendarView extends ConfigurableMetadataView
    {
        protected $dataProvider;

        protected $savedCalendarSubscriptions;

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
//                            array('type'        => 'CampaignsDetailsMenu',
//                                  'iconClass'   => 'icon-details',
//                                  'htmlOptions' => array('id' => 'ListViewDetailsActionMenu'),
//                                  'model'       => 'eval:$this->model',
//                                  'itemOptions' => array('class' => 'hasDetailsFlyout')
//                            ),
//                            array('type'        => 'CampaignsOptionsMenu',
//                                  'iconClass'   => 'icon-edit',
//                                  'htmlOptions' => array('id' => 'ListViewOptionsActionMenu')
//                            )
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public function __construct(CalendarItemsDataProvider $dataProvider)
        {
            $this->dataProvider               = $dataProvider;
            $this->savedCalendarSubscriptions = $this->dataProvider->getSavedCalendarSubscriptions();
        }

        protected function renderContent()
        {
            $content  = $this->renderSmallCalendarContent();
            $content .= $this->renderMyCalendarsContent();
            $content .= $this->renderSubscribedToCalendarsContent();
            $left = ZurmoHtml::tag('div', array('class' => 'left-column'), $content);
            $right = ZurmoHtml::tag('div', array('class' => 'right-column'), $this->renderFullCalendarContent());
            $this->registerMyCalendarSelectScript();
            return ZurmoHtml::tag('div', array('class' => 'calendar-view'), $left . $right);
        }

        protected function renderSmallCalendarContent()
        {
            Yii::app()->clientScript->registerScript('smallcalendarscript', '$( "#smallcalendar" ).datepicker();', ClientScript::POS_END);
            return ZurmoHtml::tag('div', array('id' => 'smallcalendar'), '');
        }

        protected function renderMyCalendarsContent()
        {
            $content = ZurmoHtml::tag('h3', array(), Zurmo::t('CalendarsModule', 'My Calendars'));
            $data    = array();
            $selected = '';
            foreach($this->savedCalendarSubscriptions->getMySavedCalendarsAndSelected() as $savedCalendarAndSelected)
            {
                if($savedCalendarAndSelected[1] === true)
                {
                    $selected = $savedCalendarAndSelected[0]->id;
                }
                $data[$savedCalendarAndSelected[0]->id] = $savedCalendarAndSelected[0]->name;
            }
            $content .= ZurmoHtml::radioButtonList('mycalendar', $selected, $data, array('class' => 'mycalendar'));
            return ZurmoHtml::tag('div', array('class' => 'calendars-list my-calendars'), $content);
        }

        protected function renderSubscribedToCalendarsContent()
        {
            //todo: render labels/checkboxes, then ajax action on change... to call action to update sticky.
            foreach($this->savedCalendarSubscriptions->getSubscribedToSavedCalendarsAndSelected() as $savedCalendarAndSelected)
            {
                //$savedCalendarAndSelected[0] is a SavedCalendar
                //$savedCalendarAndSelected[1] is a Boolean whether selected to view or not
            }
            //todo: add the area where you can selecte from other shared calendars. so probably a MODEL type-ahead on
            //todo: SavedCalendar would work i think... (but need to exclude your ones you own and ones you already have shared?)
            //todo: then on adding, need to call ajax to refresh the subscribedToDiv... (so maybe this needs to be its own div. this entire method..
            $content = 'todo shared calendar content';
            return ZurmoHtml::tag('div', array('class' => 'calendars-list my-calendars'), $content);
        }

        protected function renderFullCalendarContent()
        {
            $view = new FullCalendarForCombinedView($this->dataProvider);
            return $view->render();
        }

        protected function registerMyCalendarSelectScript()
        {
            //refer to http://stackoverflow.com/questions/9801095/jquery-fullcalendar-send-custom-parameter-and-refresh-calendar-with-json
            $url    = Yii::app()->createUrl('calendars/default/getEvents');
            Yii::app()->clientScript->registerScript('mycalendarselectscript', "$('.mycalendar').on('change', function(){
                    var selectedCal = $(this).val();
                    var events = {
                        url : '$url',
                        data :function()
                        {
                            return {
                                selectedId : selectedCal,
                                }
                        }
                    };
                    $('#calendar').fullCalendar('removeEventSource', events);
                    $('#calendar').fullCalendar('addEventSource', events);
                    $('#calendar').fullCalendar('refetchEvents');
                });");
        }
    }
?>