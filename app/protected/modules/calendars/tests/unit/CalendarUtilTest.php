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

    class CalendarUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ProductTestHelper::createProductByNameForOwner('First Product', User::getByUsername('super'));
            ProductTestHelper::createProductByNameForOwner('Second Product', User::getByUsername('super'));
        }

        public function setup()
        {
            parent::setup();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetStartDateTime()
        {
            $startDateTime = CalendarUtil::getStartDateTime(SavedCalendar::DATERANGE_TYPE_MONTH);
            $this->assertEquals(date('Y-m-01') . ' 00:00:00', $startDateTime);
            $startDateTime = CalendarUtil::getStartDateTime(SavedCalendar::DATERANGE_TYPE_WEEK);
            $this->assertEquals(date('Y-m-d H:i:s', strtotime('last monday', strtotime('tomorrow'))), $startDateTime);
            $startDateTime = CalendarUtil::getStartDateTime(SavedCalendar::DATERANGE_TYPE_DAY);
            $this->assertEquals(date('Y-m-d') . ' 00:00:00', $startDateTime);
        }

        public function testGetEndDateTime()
        {
            $endDateTime = CalendarUtil::getEndDateTime(SavedCalendar::DATERANGE_TYPE_MONTH);
            $this->assertEquals(date('Y-m-d', strtotime('first day of next month')) . ' 00:00:00', $endDateTime);
            $endDateTime = CalendarUtil::getEndDateTime(SavedCalendar::DATERANGE_TYPE_WEEK);
            $this->assertEquals(date('Y-m-d', strtotime('first day of next week')) . ' 00:00:00', $endDateTime);
            $endDateTime = CalendarUtil::getEndDateTime(SavedCalendar::DATERANGE_TYPE_DAY);
            $this->assertEquals(date('Y-m-d', strtotime('tomorrow')) . ' 00:00:00', $endDateTime);
        }

        public function testGetUserSavedCalendars()
        {
            $savedCalendar                      = new SavedCalendar();
            $savedCalendar->name                = 'Test Cal';
            $savedCalendar->timeZone            = 'America/Chicago';
            $savedCalendar->location            = 'Newyork';
            $savedCalendar->moduleClassName     = 'ProductsModule';
            $savedCalendar->startAttributeName  = 'createdDateTime';
            $savedCalendar->save();
            $calendars                          = CalendarUtil::getUserSavedCalendars(Yii::app()->user->userModel);
            $this->assertCount(1, $calendars);
        }

        public function testProcessUserCalendarsAndGetDataProviderForCombinedView()
        {
            $dp = CalendarUtil::processUserCalendarsAndGetDataProviderForCombinedView();
            $calendarItems = $dp->getData();
            $this->assertCount(2, $calendarItems);
            $this->assertEquals('First Product', $calendarItems[0]->getTitle());
            $this->assertEquals('Second Product', $calendarItems[1]->getTitle());
        }
    }
?>