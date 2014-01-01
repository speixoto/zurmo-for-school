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

    class EmailArchivingJobDocumentationTest extends ZurmoBaseTest
    {
        public static $user;
        public static $contact1;
        public static $contact2;
        public static $nonExistingUserEmail;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();

            $user = UserTestHelper::createBasicUser('steve');
            $user->primaryEmail->emailAddress = 'steve@example.com';
            $user->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            assert($user->save()); // Not Coding Standard

            $contact1 = ContactTestHelper::createContactByNameForOwner('peter', $user);
            $contact1->primaryEmail->emailAddress = 'peter@example.com';
            $contact1->secondaryEmail->emailAddress = 'peter2@example.com';
            assert($contact1->save()); // Not Coding Standard

            $contactsOrLeads = ContactSearch::getContactsByAnyEmailAddress('peter@example.com', null, null);

            $contact2 = ContactTestHelper::createContactByNameForOwner('jim', $user);
            $contact2->primaryEmail->emailAddress = 'jim@example.com';
            assert($contact2->save()); // Not Coding Standard

            $nonExistingUserEmail = 'jill@example.com';

            self::$user = $user;
            self::$contact1 = $contact1;
            self::$contact2 = $contact2;
            self::$nonExistingUserEmail = $nonExistingUserEmail;

            Yii::app()->imap->imapUsername = 'dropbox@example.com';
        }

        public function setup()
        {
            parent::setup();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            self::$user->forget();
            self::$user = User::getByUsername('steve');
        }

        /**
         * Test Case: Sending email from User to Contact and BCC archive.
         * Expected Behavior: email is archived to contact.
         */
        public function testCase1A()
        {
            EmailMessage::deleteAll();

            $imapMessage              = new ImapMessage();
            $imapMessage->fromName    = 'steve';
            $imapMessage->fromEmail   = self::$user->primaryEmail->emailAddress;
            $imapMessage->senderEmail = self::$user->primaryEmail->emailAddress;
            $imapMessage->to =
                array(
                    array(
                        'name'  => self::$contact1->firstName,
                        'email' => self::$contact1->primaryEmail->emailAddress
                    )
                );
            $imapMessage->subject  = "Test Email 1a";
            $imapMessage->htmlBody = "<strong>Email</strong> from Steve 1a";
            $imapMessage->textBody = "Email from Steve 1a";

            $emailArchivingJob = new EmailArchivingJob();
            $result = $emailArchivingJob->saveEmailMessage($imapMessage);
            $this->assertTrue($result);

            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $emailMessage = $emailMessages[0];

            $this->assertEquals($imapMessage->subject, $emailMessage->subject);
            $this->assertEquals($imapMessage->textBody, trim($emailMessage->content->textContent));
            $this->assertEquals($imapMessage->htmlBody, trim($emailMessage->content->htmlContent));
            $this->assertEquals(self::$user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(1, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertEquals(self::$contact1->primaryEmail->emailAddress, $recipient->toAddress);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }
            $this->assertEquals(EmailFolder::TYPE_ARCHIVED, $emailMessage->folder->type);
        }

        /**
         * Test Case: Sending email from User to Contact and CC archive.
         * Expected Behavior: email is archived to contact.
         */
        public function testCase1B()
        {
            EmailMessage::deleteAll();
            $imapMessage              = new ImapMessage();
            $imapMessage->fromName    = 'steve';
            $imapMessage->fromEmail   = self::$user->primaryEmail->emailAddress;
            $imapMessage->senderEmail = self::$user->primaryEmail->emailAddress;
            $imapMessage->to =
                array(
                    array(
                        'name'  => self::$contact1->firstName,
                        'email' => self::$contact1->primaryEmail->emailAddress
                    )
                );
            $imapMessage->cc =
                array(
                    array(
                        'name'  => '',
                        'email' => Yii::app()->imap->imapUsername
                    )
                );
            $imapMessage->subject  = "Test Email 1b";
            $imapMessage->htmlBody = "<strong>Email</strong> from Steve 1b";
            $imapMessage->textBody = "Email from Steve 1b";

            $emailArchivingJob = new EmailArchivingJob();
            $result = $emailArchivingJob->saveEmailMessage($imapMessage);
            $this->assertTrue($result);

            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $emailMessage = $emailMessages[0];

            $this->assertEquals($imapMessage->subject, $emailMessage->subject);
            $this->assertEquals($imapMessage->textBody, trim($emailMessage->content->textContent));
            $this->assertEquals($imapMessage->htmlBody, trim($emailMessage->content->htmlContent));
            $this->assertEquals(self::$user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(1, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertEquals(self::$contact1->primaryEmail->emailAddress, $recipient->toAddress);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }
            $this->assertEquals(EmailFolder::TYPE_ARCHIVED, $emailMessage->folder->type);
        }

        /**
         * Test Case: Sending email from User to email that doesn't exist in system and BCC archive.
         * Expected Behavior: no archive created.
         */
        public function testCase2A()
        {
            EmailMessage::deleteAll();
            $imapMessage              = new ImapMessage();
            $imapMessage->fromName    = 'steve';
            $imapMessage->fromEmail   = self::$user->primaryEmail->emailAddress;
            $imapMessage->senderEmail = self::$user->primaryEmail->emailAddress;
            $imapMessage->to =
                array(
                    array(
                        'name'  => '',
                        'email' => self::$nonExistingUserEmail
                    )
                );
            $imapMessage->subject  = "Test Email 2a";
            $imapMessage->htmlBody = "<strong>Email</strong> from Steve 2a";
            $imapMessage->textBody = "Email from Steve 2a";

            $emailArchivingJob = new EmailArchivingJob();
            $result = $emailArchivingJob->saveEmailMessage($imapMessage);
            $this->assertTrue($result);

            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $emailMessage = $emailMessages[0];

            $this->assertEquals($imapMessage->subject, $emailMessage->subject);
            $this->assertEquals($imapMessage->textBody, trim($emailMessage->content->textContent));
            $this->assertEquals($imapMessage->htmlBody, trim($emailMessage->content->htmlContent));
            $this->assertEquals(self::$user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(1, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertEquals(self::$nonExistingUserEmail, $recipient->toAddress);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }
            $this->assertEquals(EmailFolder::TYPE_ARCHIVED_UNMATCHED, $emailMessage->folder->type);
        }

        /**
         * Test Case: Sending email from User to email that doesn't exist in system and CC archive.
         * Expected Behavior: no archive created.
         */
        public function testCase2B()
        {
            EmailMessage::deleteAll();

            $imapMessage              = new ImapMessage();
            $imapMessage->fromName    = 'steve';
            $imapMessage->fromEmail   = self::$user->primaryEmail->emailAddress;
            $imapMessage->senderEmail = self::$user->primaryEmail->emailAddress;
            $imapMessage->to =
                array(
                    array(
                        'name'  => '',
                        'email' => self::$nonExistingUserEmail
                    )
                );
            $imapMessage->cc =
                array(
                    array(
                        'name'  => '',
                        'email' => Yii::app()->imap->imapUsername
                    )
                );
            $imapMessage->subject  = "Test Email 2b";
            $imapMessage->htmlBody = "<strong>Email</strong> from Steve 2b";
            $imapMessage->textBody = "Email from Steve 2b";

            $emailArchivingJob = new EmailArchivingJob();
            $result = $emailArchivingJob->saveEmailMessage($imapMessage);
            $this->assertTrue($result);

            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $emailMessage = $emailMessages[0];

            $this->assertEquals($imapMessage->subject,  $emailMessage->subject);
            $this->assertEquals($imapMessage->textBody, trim($emailMessage->content->textContent));
            $this->assertEquals($imapMessage->htmlBody, trim($emailMessage->content->htmlContent));
            $this->assertEquals(self::$user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(1, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertEquals(self::$nonExistingUserEmail, $recipient->toAddress);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }
            $this->assertEquals(EmailFolder::TYPE_ARCHIVED_UNMATCHED, $emailMessage->folder->type);
        }

        /**
         * Test Case: email from Contact to User, and User forward it to archive.
         * Expected Behavior: email is archived to contact record
         */
        public function testCase3A()
        {
            EmailMessage::deleteAll();

            $imapMessage              = new ImapMessage();
            $imapMessage->fromName    = 'steve';
            $imapMessage->fromEmail   = self::$user->primaryEmail->emailAddress;
            $imapMessage->senderEmail = self::$user->primaryEmail->emailAddress;
            $imapMessage->to =
                array(
                    array(
                        'name'  => '',
                        'email' => Yii::app()->imap->imapUsername
                    )
                );
            $imapMessage->subject  = "Test Email 3a";
            $imapMessage->htmlBody = "<strong>Email</strong> from Steve 3a";
            $imapMessage->textBody = "
            From: John Smith <" . self::$contact1->primaryEmail->emailAddress . ">
            Date: Thu, Apr 19, 2012 at 5:22 PM
            Subject: Hello Steve";

            $emailArchivingJob = new EmailArchivingJob();
            $result = $emailArchivingJob->saveEmailMessage($imapMessage);
            $this->assertTrue($result);

            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $emailMessage = $emailMessages[0];

            $this->assertEquals($imapMessage->subject, $emailMessage->subject);
            $this->assertEquals(trim($imapMessage->textBody), trim($emailMessage->content->textContent));
            $this->assertEquals(self::$contact1->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(1, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertEquals(self::$user->primaryEmail->emailAddress, $recipient->toAddress);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }
            $this->assertEquals(EmailFolder::TYPE_ARCHIVED, $emailMessage->folder->type);
        }

        /**
         * Test Case: email message from email that is not in system to User, and User forward it to archive.
         * Expected Behavior: email is stored in system and marked as unmatched.
         */
        public function testCase3B()
        {
            EmailMessage::deleteAll();

            $imapMessage              = new ImapMessage();
            $imapMessage->fromName    = 'steve';
            $imapMessage->fromEmail   = self::$user->primaryEmail->emailAddress;
            $imapMessage->senderEmail = self::$user->primaryEmail->emailAddress;
            $imapMessage->to =
                array(
                    array(
                        'name'  => '',
                        'email' => Yii::app()->imap->imapUsername
                    )
                );
            $imapMessage->subject  = "Test Email 3b";
            $imapMessage->htmlBody = "<strong>Email</strong> from Steve 3b";
            $imapMessage->textBody = "
            From: John Smith <" . self::$nonExistingUserEmail . ">
            Date: Thu, Apr 19, 2012 at 5:22 PM
            Subject: Hello Steve";

            $emailArchivingJob = new EmailArchivingJob();
            $result = $emailArchivingJob->saveEmailMessage($imapMessage);
            $this->assertTrue($result);

            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $emailMessage = $emailMessages[0];

            $this->assertEquals($imapMessage->subject, $emailMessage->subject);
            $this->assertEquals(trim($imapMessage->textBody), trim($emailMessage->content->textContent));
            $this->assertEquals(self::$nonExistingUserEmail, $emailMessage->sender->fromAddress);

            $this->assertEquals(1, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertEquals(self::$user->primaryEmail->emailAddress, $recipient->toAddress);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }
            $this->assertEquals(EmailFolder::TYPE_ARCHIVED_UNMATCHED, $emailMessage->folder->type);
        }

        /**
         * Test Case: email from User to Contact and email address that is not in system, BCC archive.
         * Expected Behavior: Email is archived to contact record.
         */
        public function testCase5A()
        {
            EmailMessage::deleteAll();

            $imapMessage              = new ImapMessage();
            $imapMessage->fromName    = 'steve';
            $imapMessage->fromEmail   = self::$user->primaryEmail->emailAddress;
            $imapMessage->senderEmail = self::$user->primaryEmail->emailAddress;
            $imapMessage->to =
                array(
                    array(
                        'name'  => self::$contact1->firstName,
                        'email' => self::$contact1->primaryEmail->emailAddress
                    ),
                    array(
                        'name'  => '',
                        'email' => self::$nonExistingUserEmail
                    )
                );
            $imapMessage->subject  = "Test Email 5a";
            $imapMessage->htmlBody = "<strong>Email</strong> from Steve 5a";
            $imapMessage->textBody = "Email from Steve 5a";

            $emailArchivingJob = new EmailArchivingJob();
            $result = $emailArchivingJob->saveEmailMessage($imapMessage);
            $this->assertTrue($result);

            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $emailMessage = $emailMessages[0];

            $this->assertEquals($imapMessage->subject, $emailMessage->subject);
            $this->assertEquals($imapMessage->textBody, trim($emailMessage->content->textContent));
            $this->assertEquals($imapMessage->htmlBody, trim($emailMessage->content->htmlContent));
            $this->assertEquals(self::$user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(2, count($emailMessage->recipients));
            $this->assertEquals(self::$contact1->primaryEmail->emailAddress, $emailMessage->recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[0]->type);
            $this->assertEquals(self::$nonExistingUserEmail, $emailMessage->recipients[1]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[1]->type);

            $this->assertEquals(EmailFolder::TYPE_ARCHIVED, $emailMessage->folder->type);
        }

        /**
         * Test Case: email from User to Contact and email address that is not in system, CC archive.
         * Expected Behavior: Email is archived to contact record.
         */
        public function testCase5B()
        {
            EmailMessage::deleteAll();

            $imapMessage              = new ImapMessage();
            $imapMessage->fromName    = 'steve';
            $imapMessage->fromEmail   = self::$user->primaryEmail->emailAddress;
            $imapMessage->senderEmail = self::$user->primaryEmail->emailAddress;
            $imapMessage->to =
                array(
                    array(
                        'name'  => self::$contact1->firstName,
                        'email' => self::$contact1->primaryEmail->emailAddress
                    ),
                    array(
                        'name'  => '',
                        'email' => self::$nonExistingUserEmail
                    )
                );
            $imapMessage->cc =
                array(
                    array(
                        'name'  => '',
                        'email' => Yii::app()->imap->imapUsername
                    )
                );
            $imapMessage->subject  = "Test Email 5b";
            $imapMessage->htmlBody = "<strong>Email</strong> from Steve 5b";
            $imapMessage->textBody = "Email from Steve 5b";

            $emailArchivingJob = new EmailArchivingJob();
            $result = $emailArchivingJob->saveEmailMessage($imapMessage);
            $this->assertTrue($result);

            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $emailMessage = $emailMessages[0];

            $this->assertEquals($imapMessage->subject, $emailMessage->subject);
            $this->assertEquals($imapMessage->textBody, trim($emailMessage->content->textContent));
            $this->assertEquals($imapMessage->htmlBody, trim($emailMessage->content->htmlContent));
            $this->assertEquals(self::$user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(2, count($emailMessage->recipients));
            $this->assertEquals(self::$contact1->primaryEmail->emailAddress, $emailMessage->recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[0]->type);
            $this->assertEquals(self::$nonExistingUserEmail, $emailMessage->recipients[1]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[1]->type);

            $this->assertEquals(EmailFolder::TYPE_ARCHIVED, $emailMessage->folder->type);
        }

        /**
         * Test Case: email from User to Contact and another Contact BCC archive.
         * Expected Behavior:  Email is archived to both contact records.
         */
        public function testCase6A()
        {
            EmailMessage::deleteAll();

            $imapMessage              = new ImapMessage();
            $imapMessage->fromName    = 'steve';
            $imapMessage->fromEmail   = self::$user->primaryEmail->emailAddress;
            $imapMessage->senderEmail = self::$user->primaryEmail->emailAddress;
            $imapMessage->to =
                array(
                    array(
                        'name'  => self::$contact1->firstName,
                        'email' => self::$contact1->primaryEmail->emailAddress
                    ),
                    array(
                        'name'  => self::$contact2->firstName,
                        'email' => self::$contact2->primaryEmail->emailAddress
                    )
                );
            $imapMessage->subject  = "Test Email 6a";
            $imapMessage->htmlBody = "<strong>Email</strong> from Steve 6a";
            $imapMessage->textBody = "Email from Steve 6a";

            $emailArchivingJob = new EmailArchivingJob();
            $result = $emailArchivingJob->saveEmailMessage($imapMessage);
            $this->assertTrue($result);

            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $emailMessage = $emailMessages[0];

            $this->assertEquals($imapMessage->subject, $emailMessage->subject);
            $this->assertEquals($imapMessage->textBody, trim($emailMessage->content->textContent));
            $this->assertEquals($imapMessage->htmlBody, trim($emailMessage->content->htmlContent));
            $this->assertEquals(self::$user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(2, count($emailMessage->recipients));
            $this->assertEquals(self::$contact1->primaryEmail->emailAddress, $emailMessage->recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[0]->type);
            $this->assertEquals(self::$contact2->primaryEmail->emailAddress, $emailMessage->recipients[1]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[1]->type);

            $this->assertEquals(EmailFolder::TYPE_ARCHIVED, $emailMessage->folder->type);
        }

        /**
         * Test Case: email from User to Contact and CC to another Contact and BCC archive.
         * Expected Behavior:  Email is archived to both contact records.
         */
        public function testCase6B()
        {
            EmailMessage::deleteAll();

            $imapMessage              = new ImapMessage();
            $imapMessage->fromName    = 'steve';
            $imapMessage->fromEmail   = self::$user->primaryEmail->emailAddress;
            $imapMessage->senderEmail = self::$user->primaryEmail->emailAddress;
            $imapMessage->to =
                array(
                    array(
                        'name'  => self::$contact1->firstName,
                        'email' => self::$contact1->primaryEmail->emailAddress
                    )
                );
            $imapMessage->cc =
                array(
                    array(
                        'name'  => self::$contact2->firstName,
                        'email' => self::$contact2->primaryEmail->emailAddress
                    )
                );
            $imapMessage->subject  = "Test Email 6b";
            $imapMessage->htmlBody = "<strong>Email</strong> from Steve 6b";
            $imapMessage->textBody = "Email from Steve 6b";

            $emailArchivingJob = new EmailArchivingJob();
            $result = $emailArchivingJob->saveEmailMessage($imapMessage);
            $this->assertTrue($result);

            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $emailMessage = $emailMessages[0];

            $this->assertEquals($imapMessage->subject, $emailMessage->subject);
            $this->assertEquals($imapMessage->textBody, trim($emailMessage->content->textContent));
            $this->assertEquals($imapMessage->htmlBody, trim($emailMessage->content->htmlContent));
            $this->assertEquals(self::$user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(2, count($emailMessage->recipients));
            $this->assertEquals(self::$contact1->primaryEmail->emailAddress, $emailMessage->recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[0]->type);
            $this->assertEquals(self::$contact2->primaryEmail->emailAddress, $emailMessage->recipients[1]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_CC, $emailMessage->recipients[1]->type);

            $this->assertEquals(EmailFolder::TYPE_ARCHIVED, $emailMessage->folder->type);
        }
    }
?>
