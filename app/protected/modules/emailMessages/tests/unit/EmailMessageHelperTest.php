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

    class EmailMessageHelperTest extends ZurmoBaseTest
    {
        public static $emailHelperSendEmailThroughTransport;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();

            self::$emailHelperSendEmailThroughTransport = Yii::app()->emailHelper->sendEmailThroughTransport;

            if (EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                Yii::app()->emailHelper->outboundHost     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundHost'];
                Yii::app()->emailHelper->outboundPort     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPort'];
                Yii::app()->emailHelper->outboundUsername = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundUsername'];
                Yii::app()->emailHelper->outboundPassword = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPassword'];
                Yii::app()->emailHelper->sendEmailThroughTransport = true;
                Yii::app()->emailHelper->setOutboundSettings();
                Yii::app()->emailHelper->init();

                Yii::app()->imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
                Yii::app()->imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
                Yii::app()->imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
                Yii::app()->imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
                Yii::app()->imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
                Yii::app()->imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];
                Yii::app()->imap->setInboundSettings();
                Yii::app()->imap->init();
            }
        }

        public static function tearDownAfterClass()
        {
            Yii::app()->emailHelper->sendEmailThroughTransport = self::$emailHelperSendEmailThroughTransport;
            parent::tearDownAfterClass();
        }

        public function testSendSystemEmail()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Yii::t('Default', 'Test email settings are not configured in perInstanceTest.php file.'));
            }

            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            Yii::app()->imap->connect();

            $messages = EmailMessage::getAll();
            foreach ($messages as $message)
            {
                $message->delete();
            }
            // Expunge all emails from dropbox
            Yii::app()->imap->deleteMessages(true);
            $this->assertEquals(0, count(EmailMessage::getAll()));
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);

            $subject = "System Message";
            $textMessage = "System message content.";
            $htmlMessage = "<strong>System</strong> message content.";

            EmailMessageHelper::sendSystemEmail($subject, array(Yii::app()->imap->imapUsername), $textMessage, $htmlMessage);
            sleep(30);

            Yii::app()->imap->connect();
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(1, $imapStats->Nmsgs);
            $this->assertEquals(1, count(EmailMessage::getAll()));
            $emailMessages = EmailMessage::getAll();
            $emailMessage = $emailMessages[0];

            $this->assertEquals('System Message', $emailMessage->subject);
            $this->assertEquals('System message content.', trim($emailMessage->content->textContent));
            $this->assertEquals('<strong>System</strong> message content.', trim($emailMessage->content->htmlContent));

            $this->assertEquals(1, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertEquals($recipient->toAddress, Yii::app()->imap->imapUsername);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }
        }

        public function testSendEmailFromPost()
        {
            //Test with no users/person in recipients
            $billy                      = UserTestHelper::createBasicUser('billy');
            Yii::app()->user->userModel = $billy;
            EmailMessageTestHelper::createEmailAccount($billy);
            $_POST = array('EmailMessage' => array ('recipients' => array('to'  => 'a@zurmo.com,b@zurmo.com',
                                                                          'cc'  => 'c@zurmo.com,d@zurmo.com',
                                                                          'bcc' => 'e@zurmo.com,f@zurmo.com'),
                                                    'subject' => 'Test Email From Post',
                                                    'content' => array('htmlContent' => 'This is a test email')
                ));
            $emailMessage = EmailMessageHelper::sendEmailFromPost($billy);
            //Message should have 6 recipients 2 of each type
            $this->assertEquals('6', count($emailMessage->recipients));
            $recipients = $emailMessage->recipients;
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[1]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_CC, $recipients[2]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_CC, $recipients[3]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_BCC, $recipients[4]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_BCC, $recipients[5]->type);
            $this->assertEquals('a@zurmo.com', $recipients[0]->toAddress);
            $this->assertEquals('b@zurmo.com', $recipients[1]->toAddress);
            $this->assertEquals('c@zurmo.com', $recipients[2]->toAddress);
            $this->assertEquals('d@zurmo.com', $recipients[3]->toAddress);
            $this->assertEquals('e@zurmo.com', $recipients[4]->toAddress);
            $this->assertEquals('f@zurmo.com', $recipients[5]->toAddress);
            $this->assertNull($recipients[0]->toName);
            $this->assertNull($recipients[1]->toName);
            $this->assertNull($recipients[2]->toName);
            $this->assertNull($recipients[3]->toName);
            $this->assertNull($recipients[4]->toName);
            $this->assertNull($recipients[5]->toName);
            //Recipients are not personOrAccount
            $this->assertLessThan(0, $recipients[0]->personOrAccount->id);
            $this->assertLessThan(0, $recipients[1]->personOrAccount->id);
            $this->assertLessThan(0, $recipients[2]->personOrAccount->id);
            $this->assertLessThan(0, $recipients[3]->personOrAccount->id);
            $this->assertLessThan(0, $recipients[4]->personOrAccount->id);
            $this->assertLessThan(0, $recipients[5]->personOrAccount->id);
            //The message should go to the default outbox folder
            $this->assertEquals(EmailFolder::getDefaultOutboxName(), $emailMessage->folder->name);
            $this->assertEquals(EmailFolder::TYPE_OUTBOX, $emailMessage->folder->type);
            unset($emailMessage);
            unset($recipients);
            unset($_POST);

            //Test with no personOrAccount in recipients
            $sally = UserTestHelper::createBasicUser('sally');;
            $email = new Email();
            $email->emailAddress = 'sally@example.com';
            $sally->primaryEmail = $email;
            $sally->save();
            $_POST = array('EmailMessage' => array ('recipients' => array('to'  => 'sally@example.com',
                                                                          'cc'  => null,
                                                                          'bcc' => null),
                                                    'subject' => 'Test Email From Post',
                                                    'content' => array('htmlContent' => 'This is a test email')
                ));
            $emailMessage = EmailMessageHelper::sendEmailFromPost($billy);
            $this->assertEquals($emailMessage->recipients[0]->personOrAccount->id, $sally->id);
            unset($emailMessage);
            unset($recipients);
            unset($_POST);

            //Test with attachments
            $email = new Email();
            $filesIds = array();
            $fileDocx = ZurmoTestHelper::createFileModel('testNote.txt', 'FileModel');
            $filesIds[] = $fileDocx->id;
            $fileTxt = ZurmoTestHelper::createFileModel('testImage.png', 'FileModel');
            $filesIds[] = $fileTxt->id;
            $_POST = array('EmailMessage' => array ('recipients' => array('to'  => 'sally@example.com',
                                                                          'cc'  => null,
                                                                          'bcc' => null),
                                                    'subject' => 'Test Email From Post',
                                                    'content' => array('htmlContent' => 'This is a test email')
                                             ),
                           'filesIds'     => $filesIds,
                );
            $emailMessage = EmailMessageHelper::sendEmailFromPost($billy);
            $this->assertEquals($emailMessage->recipients[0]->personOrAccount->id, $sally->id);
            $this->assertEquals(2, count($emailMessage->files));
        }

        public function testAttachFilesToMessage()
        {
            $billy = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            $filesIds = array();
            $fileTxt = ZurmoTestHelper::createFileModel('testNote.txt', 'FileModel');
            $filesIds[] = $fileTxt->id;
            $filePng = ZurmoTestHelper::createFileModel('testImage.png', 'FileModel');
            $filesIds[] = $filePng->id;
            $fileZip = ZurmoTestHelper::createFileModel('testZip.zip', 'FileModel');
            $filesIds[] = $fileZip->id;
            $filePdf = ZurmoTestHelper::createFileModel('testPDF.pdf', 'FileModel');
            $filesIds[] = $filePdf->id;
            $emailMessage = new EmailMessage();
            EmailMessageHelper::attachFilesToMessage($filesIds, $emailMessage);
            $this->assertEquals('4', count($emailMessage->files));
        }

        /**
         * @depends testSendEmailFromPost
         */
        public function testAttachRecipientsToMessage()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            $emailMessage = new EmailMessage();
            //Attach non personOrAccount recipient
            EmailMessageHelper::attachRecipientsToMessage(array('a@zurmo.com', 'b@zurmo.com', 'c@zurmo.com'), $emailMessage, EmailMessageRecipient::TYPE_TO);
            $this->assertEquals('3', count($emailMessage->recipients));
            $this->assertLessThan(0, $emailMessage->recipients[0]->personOrAccount->id);
            $this->assertLessThan(0, $emailMessage->recipients[1]->personOrAccount->id);
            $this->assertLessThan(0, $emailMessage->recipients[2]->personOrAccount->id);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[0]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[1]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[2]->type);
            //Attach personOrAccount recipient
            $sally = User::getByUsername('sally');
            EmailMessageHelper::attachRecipientsToMessage(array('sally@example.com'), $emailMessage, EmailMessageRecipient::TYPE_BCC);
            $this->assertEquals('4', count($emailMessage->recipients));
            $this->assertEquals($emailMessage->recipients[3]->personOrAccount->id, $sally->id);
            $this->assertEquals(EmailMessageRecipient::TYPE_BCC, $emailMessage->recipients[3]->type);
            //Attach an empty email
            EmailMessageHelper::attachRecipientsToMessage(array(''), $emailMessage, EmailMessageRecipient::TYPE_CC);
            $this->assertEquals('4', count($emailMessage->recipients));
        }
    }
?>