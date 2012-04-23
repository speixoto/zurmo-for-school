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

    class ImapHelperTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
        }

        public function testIsMessageForwarded()
        {
            $imapMessage = new ImapMessage();

            $imapMessage->subject = "";
            $this->assertFalse(ImapHelper::isMessageForwarded($imapMessage));

            $imapMessage->subject = "Test subject";
            $this->assertFalse(ImapHelper::isMessageForwarded($imapMessage));

            $imapMessage->subject = "Fwd: Test subject";
            $this->assertTrue(ImapHelper::isMessageForwarded($imapMessage));

            $imapMessage->subject = "FW: Test subject";
            $this->assertTrue(ImapHelper::isMessageForwarded($imapMessage));

            $imapMessage->subject = "fw: Test subject";
            $this->assertTrue(ImapHelper::isMessageForwarded($imapMessage));
        }

        /**
        * @depends testIsMessageForwarded
        */
        public function testGetOriginalSenderFromForwardedMessage()
        {
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->from = "test@example.com";

            // Outlook format
            $imapMessage->textBody = "
From: John Smith [mailto:john@example.com]
Sent: 02 March 2012 AM 01:23
To: 'Steve'
Subject: Hello Steve";

            $imapMessage->subject = "FW: Test subject";
            $from = ImapHelper::getOriginalSenderFromForwardedMessage($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);

            //Google format
            $imapMessage->textBody = "
From: John Smith <john@example.com>
Date: Thu, Apr 19, 2012 at 5:22 PM
Subject: Hello Steve
To: 'Steve'";

            $from = ImapHelper::getOriginalSenderFromForwardedMessage($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);

            $imapMessage->textBody = "
Date: Thu, Apr 19, 2012 at 5:22 PM
Subject: Hello Steve
To: 'Steve'";

            $from = ImapHelper::getOriginalSenderFromForwardedMessage($imapMessage);
            $this->assertFalse($from);
        }

        /**
        * @depends testGetOriginalSenderFromForwardedMessage
        */
        public function testResolveFromEmailAddress()
        {
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->from = "test@example.com";

            // Outlook format
            $imapMessage->textBody = "
From: John Smith [mailto:john@example.com]
Sent: 02 March 2012 AM 01:23
To: 'Steve'
Subject: Hello Steve";

            $from = ImapHelper::resolveFromEmailAddress($imapMessage);
            $this->assertEquals($imapMessage->from, $from);

            $imapMessage->subject = "FW: Test subject";
            $from = ImapHelper::resolveFromEmailAddress($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);

            //Google format
            $imapMessage->textBody = "
From: John Smith <john@example.com>
Date: Thu, Apr 19, 2012 at 5:22 PM
Subject: Hello Steve
To: 'Steve'";
            $from = ImapHelper::resolveFromEmailAddress($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);

            $imapMessage->textBody = "
Date: Thu, Apr 19, 2012 at 5:22 PM
Subject: Hello Steve
To: 'Steve'";

            $from = ImapHelper::resolveFromEmailAddress($imapMessage);
            $this->assertFalse($from);
        }

        /**
        * @depends testIsMessageForwarded
        */
        public function testResolveUserFromEmailAddress()
        {
            $user = UserTestHelper::createBasicUser('billy');
            $email = new Email();
            $email->emailAddress = 'info@example.com';
            $user->primaryEmail = $email;
            $this->assertTrue($user->save());

            // Message is not forwarded
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->from = "from@example.com";
            $imapMessage->to = array(
                array('email' => 'info@example.com'),
            );
            $owner = ImapHelper::resolveUserFromEmailAddress($imapMessage);
            $this->assertEquals($user->id, $owner->id);

            // Message is not forwarded
            // This test fails because user is not first in list
            // Also what do do if user is in CC or BCC
            /*
            $imapMessage = new ImapMessage();
            $imapMessage->to = array(
                array('email' => 'aa@example.com'),
                array('email' => 'info@example.com'),
            );
            $owner = ImapHelper::resolveUserFromEmailAddress($imapMessage);
            $this->assertEquals($user->id, $owner->id);
            */

            // Forwarded message, user should be in from field
            $imapMessage->subject = "Fwd: Test subject";
            $imapMessage->from = "info@example.com";
            $imapMessage->to = array(
                array('email' => 'to@example.com'),
            );
            $owner = ImapHelper::resolveUserFromEmailAddress($imapMessage);
            $this->assertEquals($user->id, $owner->id);
        }
    }
?>