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

    class EmailAccountTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
        }

        public function testResolveAndGetByUserAndName()
        {
            //Test a user that not have a Primary Email Address
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailAccount = EmailAccount::resolveAndGetByUserAndName($super);
            $this->assertEquals('Default', $emailAccount->name);
            $this->assertEquals($super, $emailAccount->user);
            $this->assertEquals($super->getFullName(), $emailAccount->fromName);
            $this->assertEquals($super->primaryEmail->emailAddress, $emailAccount->fromAddress);
            $this->assertEquals(0, $emailAccount->useCustomOutboundSettings);
            $this->assertEquals('smtp', $emailAccount->outboundType);
            $emailAccountId = $emailAccount->id;
            $emailAccount = EmailAccount::resolveAndGetByUserAndName($super);
            $this->assertNotEquals($emailAccountId, $emailAccount->id);
            $emailAccount->save();
            $this->assertEquals($emailAccount->getError('fromAddress'), 'From Address cannot be blank.');
            $emailAccount->fromAddress = 'super@zurmo.org';
            $emailAccount->save();
            $emailAccountId = $emailAccount->id;
            $emailAccount = EmailAccount::resolveAndGetByUserAndName($super);
            $this->assertEquals($emailAccountId, $emailAccount->id);
            $this->assertEquals('Default', $emailAccount->name);
            $this->assertEquals($super, $emailAccount->user);
            $this->assertEquals($super->getFullName(), $emailAccount->fromName);
            $this->assertEquals('super@zurmo.org', $emailAccount->fromAddress);
            $this->assertEquals(0, $emailAccount->useCustomOutboundSettings);
            $this->assertEquals('smtp', $emailAccount->outboundType);
        }

        /**
         * @depends testResolveAndGetByUserAndName
         */
        public function testGetByUserAndName()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailAccount = EmailAccount::getByUserAndName($super);
            $this->assertEquals('Default', $emailAccount->name);
            $this->assertEquals($super, $emailAccount->user);
            $this->assertEquals($super->getFullName(), $emailAccount->fromName);
            $this->assertEquals('super@zurmo.org', $emailAccount->fromAddress);
            $this->assertEquals(0, $emailAccount->useCustomOutboundSettings);
            $this->assertEquals('smtp', $emailAccount->outboundType);
        }

        public function testCrudForHasOneAndHasManyEmailAccountRelations()
        {
            $super          = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailAccount   = EmailAccount::resolveAndGetByUserAndName($super);
            $emailAccountId = $emailAccount->id;
            $emailAccount->forgetAll();

            //Check read hasOne relation
            $emailAccount       = EmailAccount::getById($emailAccountId);
            $user               = $emailAccount->user;
            $this->assertEquals($super->username, $user->username);

            //Check update hasOne relation
            $user               = User::getByUsername('billy');
            $emailAccount->user = $user;
            $this->assertTrue($emailAccount->save());
            $emailAccount->forgetAll();
            $emailAccount       = EmailAccount::getById($emailAccountId);
            $this->assertEquals('billy', $emailAccount->user->username);

            //Check delete hasOne relation
            $emailAccount->user = null;
            $this->assertTrue($emailAccount->save());
            $emailAccount->forgetAll();
            $emailAccount       = EmailAccount::getById($emailAccountId);
            $this->assertLessThan(0, $emailAccount->user->id);

            //Check create and read hasMany relation model
            $emailMessage       = EmailMessageTestHelper::
                                        createDraftSystemEmail('first test email', $user);
            $emailAccount->messages->add($emailMessage);
            $this->assertTrue($emailAccount->save());
            $emailAccount->forgetAll();
            $emailAccount       = EmailAccount::getById($emailAccountId);
            $this->assertCount(1, $emailAccount->messages);
            $this->assertEquals('first test email', $emailAccount->messages[0]->subject);

            //Check update hasMany relation
            $emailMessage          = $emailAccount->messages[0];
            $emailMessage->subject = 'first test email modified';
            $this->assertTrue($emailAccount->save());
            $emailAccount->forgetAll();
            $emailAccount          = EmailAccount::getById($emailAccountId);
            $this->assertCount(1, $emailAccount->messages);
            $this->assertEquals($emailMessage->subject, $emailAccount->messages[0]->subject);

            //Check add and read another hasMany relation model
            $emailMessage2        = EmailMessageTestHelper::
                                        createDraftSystemEmail('second test email', $user);
            $emailAccount->messages->add($emailMessage2);
            $this->assertTrue($emailAccount->save());
            $emailAccount->forgetAll();
            $emailAccount         = EmailAccount::getById($emailAccountId);
            $this->assertCount(2, $emailAccount->messages);
            $this->assertEquals($emailMessage2->subject, $emailAccount->messages[1]->subject);

            //Check delete hasMany relation first model
            $emailAccount->messages->remove($emailMessage);
            $this->assertTrue($emailAccount->save());
            $emailAccount->forgetAll();
            $emailAccount         = EmailAccount::getById($emailAccountId);
            $this->assertCount(1, $emailAccount->messages);
            $this->assertEquals($emailMessage2->subject, $emailAccount->messages[0]->subject);

            //Check delete last hasMany relation model
            $emailAccount->messages->remove($emailMessage2);
            $this->assertTrue($emailAccount->save());
            $emailAccount->forgetAll();
            $emailAccount         = EmailAccount::getById($emailAccountId);
            $this->assertCount(0, $emailAccount->messages);
        }
    }
?>