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

    class ModelsRollUpTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            $super = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            $headquarters = AccountTestHelper::createAccountByNameForOwner('Headquarters', $super);
            $division1 = AccountTestHelper::createAccountByNameForOwner('Division1', $super);
            $division2 = AccountTestHelper::createAccountByNameForOwner('Division2', $super);
            $ceo = ContactTestHelper::createContactWithAccountByNameForOwner('ceo', $super, $headquarters);
            $div1President = ContactTestHelper::createContactWithAccountByNameForOwner(
                                'div1 President', $super, $division1);
            $div2President = ContactTestHelper::createContactWithAccountByNameForOwner(
                                'div2 President', $super, $division2);
            $opportunity = OpportunityTestHelper::createOpportunityWithAccountByNameForOwner(
                                'big opp', $super, $headquarters);
            $opportunityDiv1 = OpportunityTestHelper::createOpportunityWithAccountByNameForOwner(
                                'div1 small opp', $super, $division1);
            $opportunityDiv2 = OpportunityTestHelper::createOpportunityWithAccountByNameForOwner(
                                'div2 small opp', $super, $division2);

            //attach divisions to headquarters
            $headquarters->accounts->add($division1);
            $headquarters->accounts->add($division2);
            assert($headquarters->save()); // Not Coding Standard

            //attach opportunities to contacts
            $opportunity->contacts->add($ceo);
            assert($opportunity->save()); // Not Coding Standard

            //Forget models to ensure relations are known on the next retrieval
            $headquarters->forget();
            $division1->forget();
            $division2->forget();
            $ceo->forget();
        }

        public function testGetItemIdsByModelAndUser()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $headquarters    = Account::getByName('Headquarters');
            $headquarters    = $headquarters[0];
            $division1       = Account::getByName('Division1');
            $division1       = $division1[0];
            $division2       = Account::getByName('Division2');
            $division2       = $division2[0];

            $ceo             = Contact::getByName('ceo ceoson');
            $ceo             = $ceo[0];
            $div1President   = Contact::getByName('div1 President div1 Presidentson');
            $div1President   = $div1President[0];
            $div2President   = Contact::getByName('div2 President div2 Presidentson');
            $div2President   = $div2President[0];

            $opportunity     = Opportunity::getByName('big opp');
            $opportunity     = $opportunity[0];
            $opportunityDiv1 = Opportunity::getByName('div1 small opp');
            $opportunityDiv1 = $opportunityDiv1[0];
            $opportunityDiv2 = Opportunity::getByName('div2 small opp');
            $opportunityDiv2 = $opportunityDiv2[0];

            //Headquarter rollup should include all items created so far.
            $this->assertEquals(2, $headquarters->accounts->count());
            $itemIds = ModelRollUpUtil::getItemIdsByModelAndUser($headquarters, $super);
            $compareItemIds = array();
            $this->assertEquals(9, count($itemIds));
            $this->assertTrue(in_array($headquarters->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($division1->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($division2->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($ceo->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($div1President->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($div2President->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($opportunity->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($opportunityDiv1->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($opportunityDiv2->getClassId('Item'), $itemIds));

            //Ceo rollup would only include the ceo and his opportunity
            $itemIds = ModelRollUpUtil::getItemIdsByModelAndUser($ceo, $super);
            $compareItemIds = array();
            $this->assertEquals(2, count($itemIds));
            $this->assertTrue(in_array($ceo->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($opportunity->getClassId('Item'), $itemIds));

            //Big Opp rollup will only include big opp and ceo
            $itemIds = ModelRollUpUtil::getItemIdsByModelAndUser($opportunity, $super);
            $compareItemIds = array();
            $this->assertEquals(2, count($itemIds));
            $this->assertTrue(in_array($ceo->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($opportunity->getClassId('Item'), $itemIds));

            //Division 1 rollup will only include things related to division 1
            $itemIds = ModelRollUpUtil::getItemIdsByModelAndUser($division1, $super);
            $compareItemIds = array();
            $this->assertEquals(3, count($itemIds));
            $this->assertTrue(in_array($division1->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($div1President->getClassId('Item'), $itemIds));
            $this->assertTrue(in_array($opportunityDiv1->getClassId('Item'), $itemIds));
        }
    }
?>
