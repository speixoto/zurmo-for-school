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

    class FullCalendarForCombinedView extends View
    {
        protected $dataProvider;

        public function __construct(CalendarItemsDataProvider $dataProvider)
        {
            $this->dataProvider = $dataProvider;
        }

        protected function renderContent()
        {
            $calendarItems = $this->dataProvider->getData();
            $events = null;
            for($k = 0; $k < count($calendarItems); $k++)
            {
                $calItem = $calendarItems[$k];
                $title = $calItem->getTitle();
                $events .= "{ title: '{$title}'";
                $startJsElement = $this->getJavascriptDateTimeElement($calItem->getStartDateTime());
                $events .= ", start: {$startJsElement}";
                if($calItem->getEndDateTime() != null)
                {
                    $endJsElement = $this->getJavascriptDateTimeElement($calItem->getEndDateTime());
                    $events .= ", end: {$endJsElement}";
                }
                if($k == count($calendarItems) - 1)
                {
                    $events .= " }";
                }
                else
                {
                    $events .= " },";
                }
            }
            Yii::app()->controller->widget('FullCalendar', array('inputId' => 'calendar', 'events' => $events));
            return ZurmoHtml::tag('div', array('id' => 'calendar'), '');
            //$this->dataProvider->getData() -> returns array of CalenderItems
            //$this->dataProvider->getStartDate()
            //$this->dataProvider->endStartDate()
            //$this->dataProvider->getDateRangeType()
            //@mayank, let me know the API calls we need.
        }

        protected function getJavascriptDateTimeElement($dateTime)
        {
            if(DateTimeUtil::isValidDbFormattedDateTime($dateTime))
            {
                $dateTimeArray = preg_split('/[- :]/', $dateTime);
                $month = $dateTimeArray[1] - 1;
                return "new Date({$dateTimeArray[0]},{$month},{$dateTimeArray[2]},{$dateTimeArray[3]},{$dateTimeArray[4]},{$dateTimeArray[5]})";
            }
            elseif(DateTimeUtil::isValidDbFormattedDate($dateTime))
            {
                $dateTimeArray = preg_split('/[-]/', $dateTime);
                $month = $dateTimeArray[1] - 1;
                return "new Date({$dateTimeArray[0]},{$month},{$dateTimeArray[2]})";
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>