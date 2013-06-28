<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Opportunities Module Walkthrough spefically testing the kanban board list and updating the opportunity sales
     * stage when you would drag a card from one column to another
     */
    class OpportunitiesSuperUserKanbanBoardWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected static $opportunity;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner        ('superAccount',  $super);
            ContactTestHelper::createContactWithAccountByNameForOwner        ('superContact',  $super, $account);
            OpportunityTestHelper::createOpportunityStagesIfDoesNotExist     ();
            self::$opportunity = OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp',      $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp2',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp3',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp4',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp5',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp6',     $super, $account);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
        }

        public function testSuperUserKanbanBoardListAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->setGetArray(array('kanbanBoard' => '1'));
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default');
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/index');

            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/list');
            $this->assertFalse(strpos($content, 'anyMixedAttributes') === false);
            $this->assertFalse(strpos($content, 'kanban-board-options-link') === false);

            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('kanbanBoard' => '1', 'ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->resetGetArray();

            //Test without kanbanBoard explicitly set and the option should still be there because it is sticky
            $this->resetGetArray();
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/list');
            $this->assertFalse(strpos($content, 'anyMixedAttributes') === false);
            $this->assertFalse(strpos($content, 'kanban-board-options-link') === false);

            //Now explicity declare grid and it should be missing
            $this->setGetArray(array('kanbanBoard' => ''));
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/list');
            $this->assertFalse(strpos($content, 'anyMixedAttributes') === false);
            $this->assertTrue(strpos($content, 'kanban-board-options-link') === false);
        }

        /**
         * @depends testSuperUserKanbanBoardListAction
         */
        public function testUpdateAttributeValueAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->assertEquals('Negotiating', self::$opportunity->stage->value);

            //actionUpdateAttributeValue($id, $attribute, $value)
            $this->setGetArray(array('id' => self::$opportunity->id, 'attribute' => 'stage', 'value' => 'Verbal'));
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/updateAttributeValue', true);
            $id      = self::$opportunity->id;
            self::$opportunity->forget();
            self::$opportunity = Opportunity::getById($id);
            $this->assertEquals('Verbal', self::$opportunity->stage->value);
        }
    }
?>