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

            $imapMessage->subject = "Forward subject";
            $this->assertFalse(ImapHelper::isMessageForwarded($imapMessage));

            $imapMessage->subject = "Fwd: Test subject";
            $this->assertTrue(ImapHelper::isMessageForwarded($imapMessage));

            $imapMessage->subject = "FW: Test subject";
            $this->assertTrue(ImapHelper::isMessageForwarded($imapMessage));

            $imapMessage->subject = "fw: Test subject";
            $this->assertTrue(ImapHelper::isMessageForwarded($imapMessage));

            $imapMessage->subject = "Fw: Test subject";
            $this->assertTrue(ImapHelper::isMessageForwarded($imapMessage));
        }

        /**
        * @depends testIsMessageForwarded
        */
        public function testResolveEmailSenderFromForwardedEmailMessage()
        {
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "test@example.com";

            // Outlook format
            $imapMessage->textBody = "
From: John Smith [mailto:john@example.com]
Sent: 02 March 2012 AM 01:23
To: 'Steve'
Subject: Hello Steve";

            $imapMessage->subject = "FW: Test subject";
            $sender = ImapHelper::resolveEmailSenderFromForwardedEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $sender['email']);
            $this->assertEquals('John Smith', $sender['name']);

            //Google format
            $imapMessage->textBody = "
From: John Smith <john@example.com>
Date: Thu, Apr 19, 2012 at 5:22 PM
Subject: Hello Steve
To: 'Steve'";

            $sender = ImapHelper::resolveEmailSenderFromForwardedEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $sender['email']);
            $this->assertEquals('John Smith', $sender['name']);

            $imapMessage->textBody = "
Date: Thu, Apr 19, 2012 at 5:22 PM
Subject: Hello Steve
To: 'Steve'";

            $sender = ImapHelper::resolveEmailSenderFromForwardedEmailMessage($imapMessage);
            $this->assertFalse($sender);
        }

        /**
        * @depends testResolveEmailSenderFromForwardedEmailMessage
        */
        public function testResolveEmailSenderFromEmailMessage()
        {
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "test@example.com";

            // Outlook format
            $imapMessage->textBody = "
From: John Smith [mailto:john@example.com]
Sent: 02 March 2012 AM 01:23
To: 'Steve'
Subject: Hello Steve";

            $from = ImapHelper::resolveEmailSenderFromEmailMessage($imapMessage);
            $this->assertEquals($imapMessage->fromEmail, $from);

            $imapMessage->subject = "FW: Test subject";
            $from = ImapHelper::resolveEmailSenderFromEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);

            //Google format
            $imapMessage->textBody = "
From: John Smith <john@example.com>
Date: Thu, Apr 19, 2012 at 5:22 PM
Subject: Hello Steve
To: 'Steve'";
            $from = ImapHelper::resolveEmailSenderFromEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);

            $imapMessage->textBody = "
Date: Thu, Apr 19, 2012 at 5:22 PM
Subject: Hello Steve
To: 'Steve'";
            $from = ImapHelper::resolveEmailSenderFromEmailMessage($imapMessage);
            $this->assertFalse($from);

            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Fwd: Test subject";
            $imapMessage->htmlBody = "

            -------- Original Message --------
            Subject:	Test
            Date:	Mon, 28 May 2012 15:43:39 +0200
            From:	John Smith <john@example.com>
            To: 'Steve'
            ";
            $from = ImapHelper::resolveEmailSenderFromEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);

            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Fwd: Test subject";
            $imapMessage->textBody = "
-------- Original Message --------
Subject: 	Test
Date: 	Mon, 28 May 2012 15:43:39 +0200
From: 	John Smith <john@example.com>
To: 'Steve'
";
            $from = ImapHelper::resolveEmailSenderFromEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);
        }

        /**
        * @depends testIsMessageForwarded
        */
        public function testResolveEmailReceiverFromEmailMessage()
        {
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "test@example.com";
            $imapMessage->to = array(
                array('email' => 'info@example.com')
            );

            $receivers = ImapHelper::resolveEmailReceiversFromEmailMessage($imapMessage);
            $this->assertEquals($imapMessage->to, $receivers);

            // Check with multiple receivers.
            $imapMessage->to = array(
                array('email' => 'info2@example.com'),
                array('email' => 'info@example.com')
            );
            $receivers = ImapHelper::resolveEmailReceiversFromEmailMessage($imapMessage);
            $this->assertEquals($imapMessage->to, $receivers);

            $imapMessage->subject = "FW: Test subject";
            $receivers = ImapHelper::resolveEmailReceiversFromEmailMessage($imapMessage);
            $this->assertEquals(array('email' => $imapMessage->fromEmail, 'name' => ''), $receivers);
        }

        /**
        * @depends testIsMessageForwarded
        */
        public function testResolveOwnerOfEmailMessage()
        {
            $user = UserTestHelper::createBasicUser('billy');
            $email = new Email();
            $email->emailAddress = 'billy@example.com';
            $user->primaryEmail = $email;
            $this->assertTrue($user->save());

            // User send message to dropbox, via additional to field
            // This shouldn't be done in practice, but we need to cover it.
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "billy@example.com";
            $imapMessage->to = array(
                array('email' => 'info@example.com'),
                array('email' => 'dropbox@example.com'),
            );
            $owner = ImapHelper::resolveOwnerOfEmailMessage($imapMessage);
            $this->assertEquals($user->id, $owner->id);

            // User sent CC copy of his email to dropbox
            // This also shouldn't be done in practice, because email receipt will see dropbox email account,
            // but we need to cover it.
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "billy@example.com";
            $imapMessage->to = array(
                array('email' => 'info@example.com'),
            );
            $imapMessage->cc = array(
                array('email' => 'dropbox@example.com'),
            );
            $owner = ImapHelper::resolveOwnerOfEmailMessage($imapMessage);
            $this->assertEquals($user->id, $owner->id);

            // User sent BCC copy of his email to dropbox
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "billy@example.com";
            $imapMessage->to = array(
                array('email' => 'info@example.com'),
            );
            $imapMessage->bcc = array(
                array('email' => 'dropbox@example.com'),
            );
            $owner = ImapHelper::resolveOwnerOfEmailMessage($imapMessage);
            $this->assertEquals($user->id, $owner->id);

            // Forwarded message, user should be in from field
            $imapMessage->subject = "Fwd: Test subject";
            $imapMessage->fromEmail = "billy@example.com";
            $imapMessage->to = array(
                array('email' => 'dropbox@example.com'),
            );
            $owner = ImapHelper::resolveOwnerOfEmailMessage($imapMessage);
            $this->assertEquals($user->id, $owner->id);
        }
    }
?>