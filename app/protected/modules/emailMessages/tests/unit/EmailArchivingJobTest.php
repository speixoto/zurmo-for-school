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

    class EmailArchivingJobTest extends BaseTest
    {
        public static $userMailer;
        public static $userImap;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('steve');

            Yii::app()->imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
            Yii::app()->imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
            Yii::app()->imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
            Yii::app()->imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
            Yii::app()->imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
            Yii::app()->imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];
            Yii::app()->imap->setInboundSettings();
            Yii::app()->imap->init();

            Yii::app()->emailHelper->outboundHost     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundHost'];
            Yii::app()->emailHelper->outboundPort     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPort'];
            Yii::app()->emailHelper->outboundUsername = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundUsername'];
            Yii::app()->emailHelper->outboundPassword = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPassword'];
            Yii::app()->emailHelper->sendEmailThroughTransport = true;
            Yii::app()->emailHelper->setOutboundSettings();
            Yii::app()->emailHelper->init();


            $userSmtpMailer = new EmailHelperForTesting();
            $userSmtpMailer->outboundHost     = Yii::app()->params['emailTestAccounts']['userSmtpSettings']['outboundHost'];
            $userSmtpMailer->outboundPort     = Yii::app()->params['emailTestAccounts']['userSmtpSettings']['outboundPort'];
            $userSmtpMailer->outboundUsername = Yii::app()->params['emailTestAccounts']['userSmtpSettings']['outboundUsername'];
            $userSmtpMailer->outboundPassword = Yii::app()->params['emailTestAccounts']['userSmtpSettings']['outboundPassword'];
            $userSmtpMailer->setOutboundSettings();
            $userSmtpMailer->init();
            $userSmtpMailer->sendEmailThroughTransport = true;
            self::$userMailer = $userSmtpMailer;
        }

        public function setup(){
            parent::setup();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $user = User::getByUsername('steve');
            $user->primaryEmail->emailAddress = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'];
            $this->assertTrue($user->save());
        }

        /**
         * Test case when user send email to somebody, and to dropbox(via to field)
         * This shouldn't happen in reality, because recipient will see that message is sent to dropbox folder too
         */
        public function testRunCaseOne()
        {
            $super = User::getByUsername('super');
            $user = User::getByUsername('steve');
            Yii::app()->imap->connect();
            Yii::app()->imap->expungeMessages();

            // Check if there are no emails in dropbox
            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, count(EmailMessage::getAll()));
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);

            //Now user send email to another user, and to dropbox
            $pathToFiles = Yii::getPathOfAlias('application.modules.emailMessages.tests.unit.files');
            $filePath_1    = $pathToFiles . DIRECTORY_SEPARATOR . 'table.csv';
            $filePath_2    = $pathToFiles . DIRECTORY_SEPARATOR . 'image.png';
            $filePath_3    = $pathToFiles . DIRECTORY_SEPARATOR . 'text.txt';

            Yii::app()->emailHelper->sendRawEmail("Email from Steve",
                                                  $user->primaryEmail->emailAddress,
                                                  array(
                                                      Yii::app()->params['emailTestAccounts']['testEmailAddress'],
                                                      Yii::app()->imap->imapUsername
                                                  ),
                                                  'Email from Steve',
                                                  '<strong>Email</strong> from Steve',
                                                  null,
                                                  null,
                                                  array($filePath_1, $filePath_2, $filePath_3)
            );

            sleep(30);

            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());

            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(1, $imapStats->Nmsgs);
            $this->assertEquals(1, count(EmailMessage::getAll()));
            $emailMessages = EmailMessage::getAll();
            $emailMessage = $emailMessages[0];

            $this->assertEquals('Email from Steve', $emailMessage->subject);
            $this->assertEquals('Email from Steve', trim($emailMessage->content->textContent));
            $this->assertEquals('<strong>Email</strong> from Steve', trim($emailMessage->content->htmlContent));
            $this->assertEquals($user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);
            $this->assertEquals(2, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertTrue(in_array($recipient->toAddress, array(Yii::app()->params['emailTestAccounts']['testEmailAddress'], Yii::app()->imap->imapUsername)));
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }

            $this->assertEquals(3, count($emailMessage->files));
            foreach ($emailMessage->files as $attachment)
            {
                $this->assertTrue(in_array($attachment->name, array('table.csv', 'image.png', 'text.txt')));
                $this->assertTrue($attachment->size > 0);
            }
        }

        /**
        * Test case when user send email to somebody, and cc to dropbox
        * This shouldn't happen in reality, because recipient will see that message is sent to dropbox folder too
        */
        public function testRunCaseTwo()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $user = User::getByUsername('steve');
            Yii::app()->imap->connect();

            $messages = EmailMessage::getAll();
            foreach ($messages as $message)
            {
                $message->delete();
            }
            Yii::app()->imap->expungeMessages();

            // Check if there are no emails in dropbox
            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, count(EmailMessage::getAll()));
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);

            //Now user send email to another user, and to dropbox
            $pathToFiles = Yii::getPathOfAlias('application.modules.emailMessages.tests.unit.files');
            $filePath_1    = $pathToFiles . DIRECTORY_SEPARATOR . 'table.csv';
            $filePath_2    = $pathToFiles . DIRECTORY_SEPARATOR . 'image.png';
            $filePath_3    = $pathToFiles . DIRECTORY_SEPARATOR . 'text.txt';

            Yii::app()->emailHelper->sendRawEmail("Email from Steve",
                                                  $user->primaryEmail->emailAddress,
                                                  Yii::app()->params['emailTestAccounts']['testEmailAddress'],
                                                  'Email from Steve',
                                                  '<strong>Email</strong> from Steve',
                                                  array(Yii::app()->imap->imapUsername),
                                                  null,
                                                  array($filePath_1, $filePath_2, $filePath_3)
            );

            sleep(30);

            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());

            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(1, $imapStats->Nmsgs);
            $this->assertEquals(1, count(EmailMessage::getAll()));
            $emailMessages = EmailMessage::getAll();
            $emailMessage = $emailMessages[0];

            $this->assertEquals('Email from Steve', $emailMessage->subject);
            $this->assertEquals('Email from Steve', trim($emailMessage->content->textContent));
            $this->assertEquals('<strong>Email</strong> from Steve', trim($emailMessage->content->htmlContent));
            $this->assertEquals($user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(1, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertEquals($recipient->toAddress, Yii::app()->params['emailTestAccounts']['testEmailAddress']);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }
            // To-Do: Check CC fields

            $this->assertEquals(3, count($emailMessage->files));
            foreach ($emailMessage->files as $attachment)
            {
                $this->assertTrue(in_array($attachment->name, array('table.csv', 'image.png', 'text.txt')));
                $this->assertTrue($attachment->size > 0);
            }
        }

        /**
        * Test case when user send email to somebody, and bcc to dropbox
        * This is best practictice to be used in reality, because other recipients will not see that user
        * bcc-ed email to dropbox
        */
        public function testRunCaseThree()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $user = User::getByUsername('steve');
            Yii::app()->imap->connect();

            $messages = EmailMessage::getAll();
            foreach ($messages as $message)
            {
                $message->delete();
            }
            Yii::app()->imap->expungeMessages();

            // Check if there are no emails in dropbox
            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, count(EmailMessage::getAll()));
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);

            //Now user send email to another user, and to dropbox
            $pathToFiles = Yii::getPathOfAlias('application.modules.emailMessages.tests.unit.files');
            $filePath_1    = $pathToFiles . DIRECTORY_SEPARATOR . 'table.csv';
            $filePath_2    = $pathToFiles . DIRECTORY_SEPARATOR . 'image.png';
            $filePath_3    = $pathToFiles . DIRECTORY_SEPARATOR . 'text.txt';

            Yii::app()->emailHelper->sendRawEmail("Email from Steve",
                                                  $user->primaryEmail->emailAddress,
                                                  Yii::app()->params['emailTestAccounts']['testEmailAddress'],
                                                  'Email from Steve',
                                                  '<strong>Email</strong> from Steve',
                                                  null,
                                                  array(Yii::app()->imap->imapUsername),
                                                  array($filePath_1, $filePath_2, $filePath_3)
            );

            sleep(30);

            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());

            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(1, $imapStats->Nmsgs);
            $this->assertEquals(1, count(EmailMessage::getAll()));
            $emailMessages = EmailMessage::getAll();
            $emailMessage = $emailMessages[0];

            $this->assertEquals('Email from Steve', $emailMessage->subject);
            $this->assertEquals('Email from Steve', trim($emailMessage->content->textContent));
            $this->assertEquals('<strong>Email</strong> from Steve', trim($emailMessage->content->htmlContent));
            $this->assertEquals($user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(1, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertEquals($recipient->toAddress, Yii::app()->params['emailTestAccounts']['testEmailAddress']);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }
            // To-Do: Check CC fields

            $this->assertEquals(3, count($emailMessage->files));
            foreach ($emailMessage->files as $attachment)
            {
                $this->assertTrue(in_array($attachment->name, array('table.csv', 'image.png', 'text.txt')));
                $this->assertTrue($attachment->size > 0);
            }
        }

        /**
        * Test case when somebody send email to Zurmo user, and user forward it to dropbox
        */
        public function testRunCaseFour()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $user = User::getByUsername('steve');
            Yii::app()->imap->connect();

            $messages = EmailMessage::getAll();
            foreach ($messages as $message)
            {
                $message->delete();
            }
            Yii::app()->imap->expungeMessages();

            // Check if there are no emails in dropbox
            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, count(EmailMessage::getAll()));
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);

            //Now user send email to another user, and to dropbox
            $pathToFiles = Yii::getPathOfAlias('application.modules.emailMessages.tests.unit.files');
            $filePath_1    = $pathToFiles . DIRECTORY_SEPARATOR . 'table.csv';
            $filePath_2    = $pathToFiles . DIRECTORY_SEPARATOR . 'text.txt';

            $originalSubject = "Email from John";
            $originalTextBody   = "Email from John";
            $originalHtmlBody   = "<strong>Hi Steve,</strong>. This is John. Bye!";

            $forwardedEmailClientSubjectPrefixes = EmailClientForwardTemplatesTestHelper::$subjectPrefixes;
            $forwardedEmailClientBodyPrefixes    = EmailClientForwardTemplatesTestHelper::$bodyPrefixes;

            $numberOfEmailMessages = 0;
            foreach ($forwardedEmailClientSubjectPrefixes as $client => $subjectPrefixes)
            {
                $this->assertTrue(isset($forwardedEmailClientBodyPrefixes[$client]) ||
                                  !empty($forwardedEmailClientBodyPrefixes[$client])
                );
                $bodyPrefixes = $forwardedEmailClientBodyPrefixes[$client];
                // Test all subject/body prefix combinations
                foreach ($subjectPrefixes as $subjectPrefix)
                {
                    foreach ($bodyPrefixes as $bodyPrefix)
                    {
                        $this->assertTrue($subjectPrefix != '');
                        $this->assertTrue($bodyPrefix    != '');
                        $bodyPrefix = str_replace("FROM_NAME", "John Smith", $bodyPrefix);
                        $bodyPrefix = str_replace("FROM_EMAIL", Yii::app()->params['emailTestAccounts']['testEmailAddress'], $bodyPrefix);

                        // Expunge all emails from dropbox
                        Yii::app()->imap->expungeMessages();
                        // Check if there are no emails in dropbox
                        $job = new EmailArchivingJob();
                        $this->assertTrue($job->run());
                        $this->assertEquals($numberOfEmailMessages, count(EmailMessage::getAll()));
                        $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
                        $this->assertEquals(0, $imapStats->Nmsgs);

                        $subject = $subjectPrefix . " " . $originalSubject;
                        $textBody = $bodyPrefix . $originalTextBody;
                        $htmlBody = $bodyPrefix . $originalHtmlBody;
                        Yii::app()->emailHelper->sendRawEmail($subject,
                                                              $user->primaryEmail->emailAddress,
                                                              array(Yii::app()->imap->imapUsername),
                                                              $textBody,
                                                              $htmlBody,
                                                              null, null,
                                                              array($filePath_1, $filePath_2));

                        sleep(30);
                        $job = new EmailArchivingJob();
                        $this->assertTrue($job->run());

                        $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
                        $this->assertEquals(1, $imapStats->Nmsgs);
                        $numberOfEmailMessages++;
                        $this->assertEquals($numberOfEmailMessages, count(EmailMessage::getAll()));
                        $emailMessages = EmailMessage::getAll();
                        $emailMessage = $emailMessages[$numberOfEmailMessages - 1];


                        $this->assertEquals($subject, $emailMessage->subject);
                        $this->assertEquals(Yii::app()->params['emailTestAccounts']['testEmailAddress'], $emailMessage->sender->fromAddress);
                        $this->assertEquals($user->primaryEmail->emailAddress, $emailMessage->recipients[0]->toAddress);

                        $this->assertEquals(2, count($emailMessage->files));
                        foreach ($emailMessage->files as $attachment)
                        {
                            $this->assertTrue(in_array($attachment->name, array('table.csv', 'text.txt')));
                            $this->assertTrue($attachment->size > 0);
                        }
                    }
                }
            }
        }

        /**
        * Test case when sender email is not user primary email.
        * In this case system should send email to user.
        */
        public function testRunCaseFive()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $user = User::getByUsername('steve');
            Yii::app()->imap->connect();

            $messages = EmailMessage::getAll();
            foreach ($messages as $message)
            {
                $message->delete();
            }
            Yii::app()->imap->expungeMessages();

            // Check if there are no emails in dropbox
            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, count(EmailMessage::getAll()));
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);

            Yii::app()->emailHelper->sendRawEmail("Email from invalid user",
                                                  $user->primaryEmail->emailAddress,
                                                  array(Yii::app()->imap->imapUsername),
                                                  'Some content here',
                                                  '<strong>Some</strong> content here',
                                                  null,
                                                  null,
                                                  null);

            // Change user email address.
            $originalUserAddress = $user->primaryEmail->emailAddress;
            $user = User::getByUsername('steve');
            $user->primaryEmail->emailAddress = Yii::app()->params['emailTestAccounts']['testEmailAddress'];
            $this->assertTrue($user->save());

            sleep(30);
            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());

            $this->assertEquals(1, count(EmailMessage::getAll()));
            $emailMessages = EmailMessage::getAll();
            $this->assertEquals("Invalid email address.", $emailMessages[0]->subject);
            $this->assertTrue(strpos($emailMessages[0]->content->textContent, 'Email address does not exist in system.') !== false);
            $this->assertTrue(strpos($emailMessages[0]->content->htmlContent, 'Email address does not exist in system.') !== false);
            $this->assertEquals($originalUserAddress, $emailMessages[0]->recipients[0]->toAddress);
        }

        /**
        * Check if only new messages are pulled from dropdown
        */
        public function testRunCaseSix()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $user = User::getByUsername('steve');
            Yii::app()->imap->connect();

            $messages = EmailMessage::getAll();
            foreach ($messages as $message)
            {
                $message->delete();
            }
            Yii::app()->imap->expungeMessages();

            // Check if there are no emails in dropbox
            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, count(EmailMessage::getAll()));
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);

            //Now user send email to another user, and to dropbox
            $pathToFiles = Yii::getPathOfAlias('application.modules.emailMessages.tests.unit.files');

            Yii::app()->emailHelper->sendRawEmail("Email from Steve",
                                                   $user->primaryEmail->emailAddress,
                                                  array(Yii::app()->params['emailTestAccounts']['testEmailAddress']),
                                                  'Email from Steve',
                                                  '<strong>Email</strong> from Steve',
                                                  null,
                                                  array(Yii::app()->imap->imapUsername)
                                                  );

            sleep(30);

            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());

            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(1, $imapStats->Nmsgs);
            $this->assertEquals(1, count(EmailMessage::getAll()));
            $emailMessages = EmailMessage::getAll();
            $emailMessage = $emailMessages[0];

            $this->assertEquals('Email from Steve', $emailMessage->subject);
            $this->assertEquals('Email from Steve', trim($emailMessage->content->textContent));
            $this->assertEquals('<strong>Email</strong> from Steve', trim($emailMessage->content->htmlContent));
            $this->assertEquals($user->primaryEmail->emailAddress, $emailMessage->sender->fromAddress);

            $this->assertEquals(1, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertEquals($recipient->toAddress, Yii::app()->params['emailTestAccounts']['testEmailAddress']);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }

            $job = new EmailArchivingJob();
            $this->assertTrue($job->run());

            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(1, $imapStats->Nmsgs);
            $this->assertEquals(1, count(EmailMessage::getAll()));
        }
    }
?>