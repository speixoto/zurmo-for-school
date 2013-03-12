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

    class WorkflowEmailAlertRecipientFormTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('bobby');
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }


        public function testStringifiedModelForValue()
        {
             $form = new StaticUserWorkflowEmailAlertRecipientForm('ReportModelTestItem', Workflow::TYPE_ON_SAVE);
             $form->userId = Yii::app()->user->userModel->id;
             $this->assertEquals('Clark Kent', $form->stringifiedModelForValue);

             //Now switch userId, and the stringifiedModelForValue should clear out.
             $bobby = User::getByUsername('bobby');
             $form->userId = $bobby->id;
             $this->assertEquals('bobby bobbyson', $form->stringifiedModelForValue);
             //test setting via setAttributes, it should ignore it.
             $form->setAttributes(array('stringifiedModelForValue' => 'should not set'));
             $this->assertEquals('bobby bobbyson', $form->stringifiedModelForValue);
        }

        public function test()
        {
            //todo: each subForm return value of getTypeValuesAndLabels to get complete coverage
            //todo test validation of each subform based on specific validations for each subform
            //getTypeValuesAndLabels($isCreatingNewModel, $isRequired)
            $this->fail();
        }

    }
?>