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
     * Accounts Module Super User Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class MarketingListsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            MarketingListTestHelper::createMarketingListByName('MarketingListName', 'MarketingList Description');
            MarketingListTestHelper::createMarketingListByName('MarketingListName2', 'MarketingList Description2');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');

            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertFalse(strpos($content, 'anyMixedAttributes') === false);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->resetGetArray();

            $marketingList = MarketingList::getAll();
            $this->assertEquals(2, count($marketingList));

            $marketingListNameId = self::getModelIdByModelNameAndName ('MarketingList', 'MarketingListName');
            $this->setGetArray(array('id' => $marketingListNameId));
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/edit');

            //Save account.
            $marketingListName = MarketingList::getById($marketingListNameId);
            $this->assertEquals('MarketingList Description', $marketingListName->description);
            $this->setPostArray(array('MarketingList' => array('description' => 'Edited Description')));
            //Test having a failed validation on the account during save.
            $this->setGetArray (array('id'      => $marketingListNameId));
            $this->setPostArray(array('MarketingList' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);
        }
    }
?>
