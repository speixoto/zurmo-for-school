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

    class CalendarsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            CalendarTestHelper::createSavedCalendarByName("My Cal 1", '#315AB0');
            CalendarTestHelper::createSavedCalendarByName("My Cal 2", '#66367b');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $this->runControllerWithRedirectExceptionAndGetContent('calendars/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('calendars/default/combinedDetails');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $calendars          = SavedCalendar::getAll();
            $this->assertEquals(2, count($calendars));
            $superCalId     = self::getModelIdByModelNameAndName('SavedCalendar', 'My Cal 1');
            $superCalId2    = self::getModelIdByModelNameAndName('SavedCalendar', 'My Cal 2');

            $this->setGetArray(array('id' => $superCalId));
            $this->runControllerWithNoExceptionsAndGetContent('calendars/default/edit');
            //Save product.
            $superCal       = SavedCalendar::getById($superCalId);
            $this->setPostArray(array('SavedCalendar' => array('name' => 'My New Cal 1')));

            //Test having a failed validation on the saved calendar during save.
            $this->setGetArray (array('id'      => $superCalId));
            $this->setPostArray(array('SavedCalendar' => array('name' => ''),
                                      'ajax' => 'edit-form',
                                      'RowsAndColumnsReportWizardForm' => array('filtersStructure' => '',
                                                                                'Filters' => array(
                                                                                                    array('attributeIndexOrDerivedType' => 'createdDateTime',
                                                                                                    'structurePosition'  => '1',
                                                                                                    'valueType'          => 'After',
                                                                                                    'value'              => '2/18/2014',
                                                                                                    'availableAtRunTime' => '0')
                                                                                                  ))));
            $content = $this->runControllerWithExitExceptionAndGetContent('calendars/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Filter validation
            $this->setGetArray (array('id'      => $superCalId));
            $this->setPostArray(array('SavedCalendar' => array('name' => 'Test'),
                                      'ajax' => 'edit-form',
                                      'RowsAndColumnsReportWizardForm' => array('filtersStructure' => '',
                                                                                'Filters' => array(
                                                                                                    array('attributeIndexOrDerivedType' => 'createdDateTime',
                                                                                                    'structurePosition'  => '1',
                                                                                                    'valueType'          => 'After',
                                                                                                    'value'              => '',
                                                                                                    'availableAtRunTime' => '0')
                                                                                                  ))));
            $content = $this->runControllerWithExitExceptionAndGetContent('calendars/default/edit');

            //Valid case
            $this->setGetArray (array('id'      => $superCalId));
            $this->setPostArray(array('SavedCalendar' => array('name' => 'Test'),
                                      'ajax' => 'edit-form',
                                      'RowsAndColumnsReportWizardForm' => array('filtersStructure' => '',
                                                                                'Filters' => array(
                                                                                                    array('attributeIndexOrDerivedType' => 'createdDateTime',
                                                                                                    'structurePosition'  => '1',
                                                                                                    'valueType'          => 'After',
                                                                                                    'value'              => '2/18/2014',
                                                                                                    'availableAtRunTime' => '0')
                                                                                                  ))));
            $content = $this->runControllerWithExitExceptionAndGetContent('calendars/default/edit');

            //Load Model Detail Views
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('calendars/default/details');

            $this->resetGetArray();
            $this->runControllerWithNoExceptionsAndGetContent('calendars/default/modalList');
        }

        public function testSuperUserDeleteAction()
        {
            $super                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;

            $calendar                   = CalendarTestHelper::createSavedCalendarByName("My Cal 2", '#66367b');

            //Delete a product
            $this->setGetArray(array('id' => $calendar->id));
            $this->resetPostArray();
            $calendars                  = SavedCalendar::getAll();
            $this->assertEquals(3, count($calendars));
            $this->runControllerWithNoExceptionsAndGetContent('calendars/default/delete');
            $calendars                  = SavedCalendar::getAll();
            $this->assertEquals(2, count($calendars));
            try
            {
                SavedCalendar::getById($calendar->id);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }
    }
?>