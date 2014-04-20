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
     * Home Module Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class HomeSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            ContactsModule::loadStartingData();
        }

        public function testSuperUserWelcomeActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //test that the welcome screen appears
            $content = $this->runControllerWithNoExceptionsAndGetContent('home/default/welcome');
            $this->assertFalse(strpos($content, 'Go to the dashboard') === false);

            //Change setting so user ignores welcome view.
            $form                    = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($super);
            $form->hideWelcomeView   = true;
            UserConfigurationFormAdapter::setConfigurationFromFormForCurrentUser($form);

            //Now the welcome screen should not appear
            $this->runControllerWithRedirectExceptionAndGetContent('home/default/welcome');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            //Set the current user as the super user.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('home/default');
            $this->runControllerWithNoExceptionsAndGetContent('home/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('home/default/createDashboard');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $superDashboard = Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            $this->setGetArray(array('id' => $superDashboard->id));
            $this->runControllerWithNoExceptionsAndGetContent('home/default/editDashboard');
            //Save dashboard.
            $superDashboard = Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            $this->assertEquals('Dashboard', $superDashboard->name);
            $this->setPostArray(array('Dashboard' => array('name' => '456765421')));
            $this->runControllerWithRedirectExceptionAndGetContent('home/default/editDashboard');
            $superDashboard = Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            $this->assertEquals('456765421', $superDashboard->name);
            //Test having a failed validation on the dashboard during save.
            $this->setGetArray (array('id'      => $superDashboard->id));
            $this->setPostArray(array('Dashboard' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('home/default/editDashboard');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => -1));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/default/dashboardDetails');
            $this->setGetArray(array('id' => $superDashboard->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/default/dashboardDetails');

            //Add second dashboard for use in deletion tests.
            $secondDashboard   = DashboardTestHelper::createDashboardByNameForOwner('Dashboard2', $super);
            $this->assertTrue($secondDashboard->isDefault == 0);
            $this->assertFalse($secondDashboard->isDefault === 0); //Just to prove it does not evaluate to this.
            //Attempt to delete the default dashboard and have it through an exception.
            $dashboards = Dashboard::getRowsByUserId($super->id);
            $this->assertEquals(2, count($dashboards));
            $this->setGetArray(array('dashboardId' => $superDashboard->id));
            $this->resetPostArray();
            $this->runControllerWithNotSupportedExceptionAndGetContent('home/default/deleteDashboard');

            //Delete dashboard that you can delete.
            $dashboards = Dashboard::getRowsByUserId($super->id);
            $this->assertEquals(2, count($dashboards));
            $this->setGetArray(array('dashboardId' => $secondDashboard->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('home/default/deleteDashboard');
            $dashboards = Dashboard::getRowsByUserId($super->id);
            $this->assertEquals(1, count($dashboards));

            //Add a dashboard via the create dashboard action.
            $this->assertEquals(1, Dashboard::getCount());
            $this->resetGetArray();
            $this->setPostArray(array('Dashboard' => array(
                'name'    => 'myTestDashboard',
                'layoutType' => '50,50'))); // Not Coding Standard
            $this->runControllerWithRedirectExceptionAndGetContent('home/default/createDashboard');
            $dashboards = Dashboard::getAll();
            $this->assertEquals(2, count($dashboards));
            $this->assertEquals('myTestDashboard', $dashboards[1]->name);
            $this->assertEquals($super, $dashboards[1]->owner);
            $this->assertEquals('50,50', $dashboards[1]->layoutType); // Not Coding Standard

            //Portlet Controller Actions
            $uniqueLayoutId = 'HomeDashboard' . $superDashboard->layoutId;
            $this->setGetArray(array(
                'dashboardId'    => $superDashboard->id,
                'uniqueLayoutId' => $uniqueLayoutId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/addList');

            //Add AccountsMyList Portlet to dashboard
            $this->setGetArray(array(
                'dashboardId'    => $superDashboard->id,
                'portletType'    => 'AccountsMyList',
                'uniqueLayoutId' => $uniqueLayoutId));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('home/defaultPortlet/add');

            //Save a layout change. Collapse all portlets
            //At this point portlets for this view should be created because we have already loaded the 'details' page in a request above.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition($uniqueLayoutId, $super->id, array());
            $this->assertEquals(4, count($portlets[1]));
            $this->assertEquals(4, count($portlets[2]));
            $portletPostData = array();
            $portletCount = 0;
            foreach ($portlets as $column => $columnPortlets)
            {
                foreach ($columnPortlets as $position => $portlet)
                {
                    $this->assertEquals('0', $portlet->collapsed);
                    $portletPostData['HomeDashboard1_' . $portlet->id] = array(
                        'collapsed' => 'true',
                        'column'    => 0,
                        'id'        => 'HomeDashboard1_' . $portlet->id,
                        'position'  => $portletCount,
                    );
                    $portletCount++;
                }
            }
            //There should have been a total of 3 portlets. Checking positions as 4 will confirm this.
            $this->assertEquals(8, $portletCount);
            $this->resetGetArray();
            $this->setPostArray(array(
                'portletLayoutConfiguration' => array(
                    'portlets' => $portletPostData,
                    'uniqueLayoutId' => $uniqueLayoutId,
                )
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/saveLayout', true);
            //Now test that all the portlets are collapsed.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                            $uniqueLayoutId, $super->id, array());
            $this->assertEquals (8, count($portlets[1])         );
            $this->assertFalse  (array_key_exists(8, $portlets) );
            foreach ($portlets as $column => $columns)
            {
                foreach ($columns as $position => $positionPortlets)
                {
                    $this->assertEquals('1', $positionPortlets->collapsed);
                }
            }

            //Load up modal config edit view.
            $this->assertTrue($portlets[1][5]->id > 0);
            $this->assertEquals('AccountsMyList', $portlets[1][5]->viewType);
            $this->setGetArray(array(
                'portletId'    => $portlets[1][5]->id,
                'uniqueLayoutId' => $uniqueLayoutId,
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigEdit');

            //Now validate the form.
            $this->setGetArray(array(
                'portletId'    => $portlets[1][5]->id,
                'uniqueLayoutId' => $uniqueLayoutId,
            ));
            $this->setPostArray(array(
                'ajax'    => 'modal-edit-form',
                'AccountsSearchForm' => array()));
            $this->runControllerWithExitExceptionAndGetContent('home/defaultPortlet/modalConfigEdit');

            //save changes to the portlet title
            $this->setGetArray(array(
                'portletId'      => $portlets[1][5]->id,
                'uniqueLayoutId' => $uniqueLayoutId,
            ));
            $this->setPostArray(array(
                'MyListForm'         => array('title' => 'something new'),
                'AccountsSearchForm' => array()));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigSave');
            //Now confirm the title change was successful.
            $portlet = Portlet::getById($portlets[1][5]->id);
            $this->assertEquals('something new', $portlet->getView()->getTitle());

            //Refresh a portlet modally.
            $this->setGetArray(array(
                'portletId'    => $portlets[1][5]->id,
                'uniqueLayoutId' => $uniqueLayoutId,
                'redirectUrl' => 'home/default'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalRefresh');
            //Load Home Dashboard View again to make sure everything is ok after the layout change.
            $this->resetGetArray();
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/default');

            //task sorting issue
            //check whether tasks portlet render or not
            $this->assertTrue($portlets[1][3]->id > 0);
            $this->assertEquals('TasksMyList', $portlets[1][3]->viewType);

            //to sort task list
            $this->setGetArray(array(
                'Task_sort'      => 'name',
                'portletId'      => $portlets[1][3]->id,
                'uniqueLayoutId' => $uniqueLayoutId,
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/default');

            //to sort task list after portlet has been edited
            $this->resetGetArray();
            $this->setGetArray(array(
                'Task_sort'      => 'dueDateTime',
                'portletId'      => $portlets[1][3]->id,
                'uniqueLayoutId' => $uniqueLayoutId,
                'ajax'           => 'list-view' . $uniqueLayoutId . '_' . $portlets[1][3]->id
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/default');
        }

        /**
         * Strange it fails in ui but passes in the test. It should fail in both ways.
         */
        public function testSuperUserDateSorting()
        {
/**
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superDashboard = Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            $uniqueLayoutId = 'HomeDashboard' . $superDashboard->layoutId;
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition($uniqueLayoutId, $super->id, array());

            $this->setGetArray(array(
                'Task_sort'      => 'date',
                'portletId'      => $portlets[1][4]->id,
                'uniqueLayoutId' => $uniqueLayoutId,
                'ajax'           => 'list-view' . $uniqueLayoutId . '_' . $portlets[1][4]->id
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/default');
*/
        }

        public function testAddPortletOnTwoColumnsDashboard()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superDashboard1 = Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            //load details view
            $this->setGetArray(array('id' => $superDashboard1->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/default/dashboardDetails');

            //Portlet Controller Actions
            $uniqueLayoutId = 'HomeDashboard' . $superDashboard1->layoutId;
            $this->setGetArray(array(
                'dashboardId'    => $superDashboard1->id,
                'uniqueLayoutId' => $uniqueLayoutId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/addList');

            //Add AccountsMyList Portlet to dashboard
            $this->setGetArray(array(
                'dashboardId'    => $superDashboard1->id,
                'portletType'    => 'AccountsMyList',
                'uniqueLayoutId' => $uniqueLayoutId));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('home/defaultPortlet/add');

            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition($uniqueLayoutId, $super->id, array());
            $this->assertEquals(8, count($portlets[1]));
            $this->assertEquals(1, count($portlets[2]));
            $portletPostData = array();
            $portletCount = 0;
            foreach ($portlets as $column => $columnPortlets)
            {
                foreach ($columnPortlets as $position => $portlet)
                {
                    $portletPostData['HomeDashboard1_' . $portlet->id] = array(
                        'collapsed' => 'true',
                        'column'    => 0,
                        'id'        => 'HomeDashboard1_' . $portlet->id,
                        'position'  => $portletCount,
                    );
                    $portletCount++;
                }
            }
            //There should have been a total of 3 portlets. Checking positions as 4 will confirm this.
            $this->assertEquals(9, $portletCount);
            $this->resetGetArray();
            $this->setPostArray(array(
                'portletLayoutConfiguration' => array(
                    'portlets' => $portletPostData,
                    'uniqueLayoutId' => $uniqueLayoutId,
                )
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/saveLayout', true);

            //Add ContactsMyList Portlet to dashboard

            $this->setGetArray(array(
                'dashboardId'    => $superDashboard1->id,
                'portletType'    => 'ContactsMyList',
                'uniqueLayoutId' => $uniqueLayoutId));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('home/defaultPortlet/add');
        }

        public function testAddProductsMyListPortletToDashboard()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superDashboard1 = Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            //load details view
            $this->setGetArray(array('id' => $superDashboard1->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/default/dashboardDetails');

            //Portlet Controller Actions
            $uniqueLayoutId = 'HomeDashboard' . $superDashboard1->layoutId;
            $this->setGetArray(array(
                'dashboardId'    => $superDashboard1->id,
                'uniqueLayoutId' => $uniqueLayoutId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/addList');

            //Add ProductsMyList Portlet to dashboard
            $this->setGetArray(array(
                'dashboardId'    => $superDashboard1->id,
                'portletType'    => 'ProductsMyList',
                'uniqueLayoutId' => $uniqueLayoutId));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('home/defaultPortlet/add');
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition($uniqueLayoutId, $super->id, array());
            $this->assertEquals(9, count($portlets[1]));
            $this->assertEquals(2, count($portlets[2]));
            $portletPostData = array();
            $portletCount = 0;
            foreach ($portlets as $column => $columnPortlets)
            {
                foreach ($columnPortlets as $position => $portlet)
                {
                    $portletPostData['HomeDashboard1_' . $portlet->id] = array(
                        'collapsed' => 'true',
                        'column'    => 0,
                        'id'        => 'HomeDashboard1_' . $portlet->id,
                        'position'  => $portletCount,
                    );
                    $portletCount++;
                }
            }
            $this->assertEquals(11, $portletCount);
            $this->resetGetArray();
            $this->setPostArray(array(
                'portletLayoutConfiguration' => array(
                    'portlets' => $portletPostData,
                    'uniqueLayoutId' => $uniqueLayoutId,
                )
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/saveLayout', true);

            //load details view
            $this->setGetArray(array('id' => $superDashboard1->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/default/dashboardDetails');
        }

        public function testCreateAndEditDashboardByChangingLayoutType()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $dashboardCount = Dashboard::getCount();
            //Add new dashboard using dashboard add action
            $this->resetGetArray();
            $this->setPostArray(array('Dashboard' => array('name'       => 'myDataDashboard',
                                                           'layoutType' => '50,50'))); // Not Coding Standard
            $this->runControllerWithRedirectExceptionAndGetContent('home/default/createDashboard');
            $dashboards = Dashboard::getAll();
            $this->assertEquals(intval($dashboardCount + 1), count($dashboards));
            $myDataDashboard = $dashboards[$dashboardCount];
            $this->assertEquals('myDataDashboard', $myDataDashboard->name);
            $this->assertEquals($super, $myDataDashboard->owner);
            $this->assertEquals('50,50', $myDataDashboard->layoutType); // Not Coding Standard
            //Add portlet on 2nd column of recently added dashboard
            $uniqueLayoutId = 'HomeDashboard' . $myDataDashboard->layoutId;
            $this->setGetArray(array(
                'dashboardId'    => $myDataDashboard->id,
                'portletType'    => 'ContactsMyList',
                'uniqueLayoutId' => $uniqueLayoutId));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('home/defaultPortlet/add');
            //Edit dashboard and change it to one column layout
            $this->resetGetArray();
            $this->setGetArray(array('id' => $myDataDashboard->id));
            $this->runControllerWithNoExceptionsAndGetContent('home/default/editDashboard');
            $this->setPostArray(array('Dashboard' => array('layoutType' => '100')));
            $editActionContent = $this->runControllerWithRedirectExceptionAndGetContent('home/default/editDashboard');
            $this->assertTrue(strpos($editActionContent, 'Undefined variable: maxPositionInColumn1') === false);
            $this->resetGetArray();
            $this->setGetArray(array('id' => $myDataDashboard->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/default/dashboardDetails');
            $this->assertTrue(strpos($editActionContent, 'Undefined offset: 2') === false);
        }
    }
?>