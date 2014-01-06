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

    class AccountListViewMergeUtilTest extends ListViewMergeUtilTest
    {
        public $modelClass = 'Account';

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            UserTestHelper::createBasicUser('Steven');
        }

        protected function setFirstModel()
        {
            $user                                   = User::getByUsername('steven');
            $account                                = AccountListViewMergeTestHelper::getFirstModel($user);
            $this->selectedModels[]                 = $account;
        }

        protected function setSecondModel()
        {
            $user                                   = User::getByUsername('steven');
            $account                                = AccountListViewMergeTestHelper::getSecondModel($user);
            $this->selectedModels[]                 = $account;
        }

        protected function setRelatedModels()
        {
            $this->addProject();
            $this->addProduct();
            $this->addContact();
            $this->addOpportunity();
            $this->addTask();
            $this->addNote();
            $this->addMeeting();
        }

        protected function validatePrimaryModelData()
        {
            $this->assertEmpty(Account::getByName('Test Account2'));
            $primaryModel = $this->getPrimaryModel();
            $this->assertEquals(1, count($primaryModel->projects));
        }

        private function addProject()
        {
            $project = ProjectTestHelper::createProjectByNameForOwner('Account Project', Yii::app()->user->userModel);
            $project->accounts->add($this->getPrimaryModel());
            assert($project->save());
        }

        private function addProduct()
        {
            $product = ProductTestHelper::createProductByNameForOwner('Account Product', Yii::app()->user->userModel);
            $product->account = $this->getPrimaryModel();
            $product->save();
        }

        private function addContact()
        {
            $contact = ContactTestHelper::createContactByNameForOwner('Allan Turner', Yii::app()->user->userModel);
            $contact->account = $this->getPrimaryModel();
            $contact->save();
        }

        private function addOpportunity()
        {
            $opportunity = OpportunityTestHelper::createOpportunityByNameForOwner('UI Services', Yii::app()->user->userModel);
            $opportunity->account = $this->getPrimaryModel();
            $opportunity->save();
        }

        private function addMeeting()
        {
            MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount('First Meeting', Yii::app()->user->userModel, $this->getPrimaryModel());
        }

        private function addNote()
        {
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('First Meeting', Yii::app()->user->userModel, $this->getPrimaryModel());
        }

        private function addTask()
        {
            TaskTestHelper::createTaskWithOwnerAndRelatedAccount('First Task', Yii::app()->user->userModel, $this->getPrimaryModel());
        }

        protected function setSelectedModels()
        {
            $accounts = Account::getByName('Test Account1');
            $this->selectedModels[] = $accounts[0];

            $accounts = Account::getByName('Test Account2');
            $this->selectedModels[] = $accounts[0];
        }
    }
?>