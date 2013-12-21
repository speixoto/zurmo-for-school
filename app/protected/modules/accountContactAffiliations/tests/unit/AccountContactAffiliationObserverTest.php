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

    class AccountContactAffiliationObserverTest extends ZurmoBaseTest
    {
        protected static $accountContactAffiliationObserver;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
            self::$accountContactAffiliationObserver = new AccountContactAffiliationObserver();
            self::$accountContactAffiliationObserver->init(); //runs init();
        }

        public function testChangingContactOnAccountFromContactSideThatObservationTakesPlace()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $account = AccountTestHelper::createAccountByNameForOwner('firstAccount', $super);
            $contact  = ContactTestHelper::createContactWithAccountByNameForOwner('firstContact', $super, $account);
            $contact2 = ContactTestHelper::createContactByNameForOwner('secondContact', $super);

            //Now make a second account and add the first contact to it. This would switch the contact->account to account2
            $account2 = AccountTestHelper::createAccountByNameForOwner('secondAccount', $super);
            $account2->contacts->add($contact);
            $this->assertTrue($account2->contacts->contains($contact));

            echo 'bam -- after this it should show 2 as the new account id and 1 as the old'. "\n";
            $this->assertTrue ($account2->save()); //todo: should trigger observer change
            $this->assertTrue ($account2->contacts->contains($contact));
            $this->assertFalse($account->contacts->contains($contact));
            echo 'the contact related account after switching is: ' . $contact->account->id . "\n";
            echo 'bamX'. "\n";
            //Now test removing the contact from the second account
            $account2->contacts->remove($contact);
            echo 'bamZam'. "\n";
            $this->assertTrue($account2->save()); //todo: should trigger observer change
            $this->assertTrue($contact->account->id < 0);
            echo 'after we removed from account2 con->acc id: ' . $contact->account->id . "\n";

            //Contact is no longer connected to either account at this point.
            $this->assertFalse($account->contacts->contains($contact));
            $this->assertFalse($account2->contacts->contains($contact));

            //todo: we could test that the AccountContactAffiliation models get created etc.... changed.
        }


    }
?>
