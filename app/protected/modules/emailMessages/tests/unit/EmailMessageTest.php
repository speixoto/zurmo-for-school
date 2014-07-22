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

    class EmailMessageTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $billy = UserTestHelper::createBasicUser('billy');
            $jane = UserTestHelper::createBasicUser('jane');
            UserTestHelper::createBasicUser('sally');
            UserTestHelper::createBasicUser('jason');
            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            EmailBoxUtil::createBoxAndDefaultFoldersByUserAndName($jane, 'JaneBox');
        }

        /**
         * A notification email is different than a regular outbound email because it is owned by a super user
         * that is different than the user logged in.  So the sender does not have a 'person'
         */
        public function testCreateEmailMessageThatIsANotification()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');
            $this->assertEquals(0, EmailMessage::getCount());

            $emailMessage = new EmailMessage();
            $emailMessage->owner   = BaseControlUserConfigUtil::getUserToRunAs();
            $emailMessage->subject = 'My First Email';

            //Attempt to save without setting required information
            $saved        = $emailMessage->save();
            $this->assertFalse($saved);
            $compareData = array('folder' => array('name'          => array('Name cannot be blank.'),
                                                   'emailBox'      => array('name' => array('Name cannot be blank.'))),
                                 'sender' => array('fromAddress'   => array('From Address cannot be blank.')));
            $this->assertEquals($compareData, $emailMessage->getErrors());

            //Set sender, and recipient, and content
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My First Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;

            //Sending from the system, does not have a 'person'.
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'system@somewhere.com';
            $sender->fromName          = 'Zurmo System';
            $emailMessage->sender      = $sender;

            //Recipient is billy.
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'billy@fakeemail.com';
            $recipient->toName          = 'Billy James';
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personsOrAccounts->add($billy);
            $emailMessage->recipients->add($recipient);

            //At this point the message is in no folder
            $this->assertTrue($emailMessage->folder->id < 0);

            $box                  = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);

            //At this point the message should be in the draft folder by default.
            $this->assertEquals(EmailFolder::getDefaultDraftName(), $emailMessage->folder->name);
            $this->assertEquals(EmailFolder::TYPE_DRAFT, $emailMessage->folder->type);
        }

        /**
         * @depends testCreateEmailMessageThatIsANotification
         * @expectedException NotSupportedException
         */
        public function testAttemptingToSendEmailInOutbox()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailMessages              = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));

            //Now put the message in the outbox. Should not send.
            $box                      = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessages[0]->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX);

            Yii::app()->emailHelper->send($emailMessages[0]);
        }

        /**
         * @depends testAttemptingToSendEmailInOutbox
         */
        public function testAttemptingToSendEmailNotOutbox()
        {
            $super                            = User::getByUsername('super');
            Yii::app()->user->userModel       = $super;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $emailMessages                    = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            //Because it was set to outbox from last test, stil at outbox.
            $this->assertTrue($emailMessages[0]->folder->type   == EmailFolder::TYPE_OUTBOX);
            $box                      = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessages[0]->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            $sentOrQueued = Yii::app()->emailHelper->send($emailMessages[0]);
            $this->assertTrue($sentOrQueued);
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            //The message, because it is queued, should still be in the outbox
            $this->assertEquals(EmailFolder::TYPE_OUTBOX, $emailMessages[0]->folder->type);
        }

        /**
         * @depends testAttemptingToSendEmailNotOutbox
         */
        public function testCreateNormalEmailMessage()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');
            $jane                       = User::getByUsername('jane');

            $this->assertEquals(1, EmailMessage::getCount());

            $emailMessage = new EmailMessage();
            $emailMessage->owner   = $jane;
            $emailMessage->subject = 'My Second Email';

            //Attempt to save without setting required information
            $saved        = $emailMessage->save();
            $this->assertFalse($saved);
            $compareData = array('folder' => array('name'          => array('Name cannot be blank.'),
                                                   'emailBox'      => array('name' => array('Name cannot be blank.'))),
                                 'sender' => array('fromAddress'   => array('From Address cannot be blank.')));
            $this->assertEquals($compareData, $emailMessage->getErrors());

            //Set sender, and recipient, and content
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My Second Message';
            $emailMessage->content     = $emailContent;

            //Sending from jane
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'jane@fakeemail.com';
            $sender->fromName          = 'Jane Smith';
            $sender->personsOrAccounts->add($jane);
            $emailMessage->sender      = $sender;

            //Recipient is billy.
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'billy@fakeemail.com';
            $recipient->toName          = 'Billy James';
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personsOrAccounts->add($billy);
            $emailMessage->recipients->add($recipient);

            //At this point the message is not in a folder.
            $this->assertTrue($emailMessage->folder->id < 0);

            $box                  = EmailBox::resolveAndGetByName('JaneBox');
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);
        }

        /**
         * @depends testCreateNormalEmailMessage
         */
        public function testCreateAndSendEmailMessageWithAttachment()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');
            $jane                       = User::getByUsername('jane');

            $emailMessage = new EmailMessage();
            $emailMessage->owner   = $jane;
            $emailMessage->subject = 'My Email with an Attachment';
            $fileModel        = ZurmoTestHelper::createFileModel('testNote.txt');
            $emailMessage->files->add($fileModel);

            //Attempt to save without setting required information
            $saved        = $emailMessage->save();
            $this->assertFalse($saved);
            $compareData = array('folder' => array('name'          => array('Name cannot be blank.'),
                                                   'emailBox'      => array('name' => array('Name cannot be blank.'))),
                                 'sender' => array('fromAddress'   => array('From Address cannot be blank.')));
            $this->assertEquals($compareData, $emailMessage->getErrors());

            //Set sender, and recipient, and content
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My Second Message';
            $emailMessage->content     = $emailContent;

            //Sending from jane
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'jane@fakeemail.com';
            $sender->fromName          = 'Jane Smith';
            $sender->personsOrAccounts->add($jane);
            $emailMessage->sender      = $sender;

            //Recipient is billy.
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'billy@fakeemail.com';
            $recipient->toName          = 'Billy James';
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personsOrAccounts->add($billy);
            $emailMessage->recipients->add($recipient);

            //At this point the message is not in a folder.
            $this->assertTrue($emailMessage->folder->id < 0);

            $box                  = EmailBox::resolveAndGetByName('JaneBox');
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);

            $id = $emailMessage->id;
            unset($emailMessage);
            $emailMessage = EmailMessage::getById($id);
            $this->assertEquals('My Email with an Attachment', $emailMessage->subject);
            $this->assertEquals(1, $emailMessage->files->count());
            $this->assertEquals($fileModel, $emailMessage->files->offsetGet(0));
        }

        /**
         * @depends testCreateAndSendEmailMessageWithAttachment
         */
        public function testMultipleRecipientsAndTypes()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');
            $jane                       = User::getByUsername('jane');
            $sally                      = User::getByUsername('sally');
            $jason                      = User::getByUsername('jason');

            $this->assertEquals(3, EmailMessage::getCount());

            $emailMessage = new EmailMessage();
            $emailMessage->owner   = $jane;
            $emailMessage->subject = 'My Third Email';

            //Attempt to save without setting required information
            $saved        = $emailMessage->save();
            $this->assertFalse($saved);
            $compareData = array('folder' => array('name'          => array('Name cannot be blank.'),
                                                   'emailBox'      => array('name' => array('Name cannot be blank.'))),
                                 'sender' => array('fromAddress'   => array('From Address cannot be blank.')));
            $this->assertEquals($compareData, $emailMessage->getErrors());
            //Set sender, and recipient, and content
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My Third Message';
            $emailMessage->content     = $emailContent;

            //Sending from jane
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'jane@fakeemail.com';
            $sender->fromName          = 'Jane Smith';
            $sender->personsOrAccounts->add($jane);
            $emailMessage->sender      = $sender;

            //Recipient is billy.
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'billy@fakeemail.com';
            $recipient->toName          = 'Billy James';
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personsOrAccounts->add($billy);
            $emailMessage->recipients->add($recipient);

            //CC recipient is Sally
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'sally@fakeemail.com';
            $recipient->toName          = 'Sally Pail';
            $recipient->type            = EmailMessageRecipient::TYPE_CC;
            $recipient->personsOrAccounts->add($sally);
            $emailMessage->recipients->add($recipient);

            //BCC recipient is Jason
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'jason@fakeemail.com';
            $recipient->toName          = 'Jason Blue';
            $recipient->type            = EmailMessageRecipient::TYPE_BCC;
            $recipient->personsOrAccounts->add($jason);
            $emailMessage->recipients->add($recipient);

            //At this point the message is not in a folder.
            $this->assertTrue($emailMessage->folder->id < 0);
            $box                  = EmailBox::resolveAndGetByName('JaneBox');
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);

            //Now send the message.
            Yii::app()->emailHelper->send($emailMessage);
        }

        /**
         * @depends testMultipleRecipientsAndTypes
         */
        public function testQueuedEmailsWhenEmailMessageChangeToSentFolder()
        {
            $super                            = User::getByUsername('super');
            Yii::app()->user->userModel       = $super;
            $this->assertEquals(2, Yii::app()->emailHelper->getQueuedCount());
            $emailMessages                    = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals(2, count($emailMessages));
            $emailMessages[0]->folder->type = EmailFolder::TYPE_OUTBOX;
            $emailMessages[1]->folder->type = EmailFolder::TYPE_OUTBOX;
            $emailMessageId = $emailMessages[0]->id;

            $sent = Yii::app()->emailHelper->sendQueued();
            $this->assertTrue($sent);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, count(EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX)));

            $emailMessages                    = EmailMessage::getAllByFolderType(EmailFolder::TYPE_SENT);
            $this->assertEquals(2, count($emailMessages));
            $this->assertEquals($emailMessageId, $emailMessages[0]->id);
        }

        /**
         * @depends testQueuedEmailsWhenEmailMessageChangeToSentFolder
         */
        public function testRegularUserCanCreateEmailMessageAndSend()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            $emailMessage               = EmailMessageTestHelper::createDraftSystemEmail('billy test email', $billy);
            AllPermissionsOptimizationUtil::rebuild();
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            Yii::app()->emailHelper->send($emailMessage);
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
        }

        public function testCreateMultipleEmailMessageWithAttachments()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $jane                       = User::getByUsername('jane');
            $billy                      = User::getByUsername('billy');
            $emailMessageIds            = array();

            for ($count = 0 ; $count < 5; $count++)
            {
                $emailMessage = new EmailMessage();
                $emailMessage->owner   = $jane;
                $emailMessage->subject = 'My Email with 2 Attachments';
                $emailMessage->files->add(ZurmoTestHelper::createFileModel('testNote.txt'));
                $emailMessage->files->add(ZurmoTestHelper::createFileModel('testPDF.pdf'));

                //Set sender, and recipient, and content
                $emailContent              = new EmailMessageContent();
                $emailContent->textContent = 'My Second Message';
                $emailMessage->content     = $emailContent;

                //Sending from jane
                $sender                    = new EmailMessageSender();
                $sender->fromAddress       = 'jane@fakeemail.com';
                $sender->fromName          = 'Jane Smith';
                $sender->personsOrAccounts->add($jane);
                $emailMessage->sender      = $sender;

                //Recipient is billy.
                $recipient                  = new EmailMessageRecipient();
                $recipient->toAddress       = 'billy@fakeemail.com';
                $recipient->toName          = 'Billy James';
                $recipient->type            = EmailMessageRecipient::TYPE_TO;
                $recipient->personsOrAccounts->add($billy);
                $emailMessage->recipients->add($recipient);

                //At this point the message is not in a folder.
                $this->assertTrue($emailMessage->folder->id < 0);

                $box                  = EmailBox::resolveAndGetByName('JaneBox');
                $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);

                //Save, at this point the email should be in the draft folder
                $saved = $emailMessage->save();
                $this->assertTrue($saved);
                $this->assertTrue($emailMessage->folder->id > 0);

                $emailMessageIds[] = $emailMessage->id;
                unset($emailMessage);
                ForgetAllCacheUtil::forgetAllCaches();
            }
            foreach ($emailMessageIds as $id)
            {
                $emailMessage = EmailMessage::getById($id);
                $this->assertEquals('My Email with 2 Attachments', $emailMessage->subject);
                $this->assertEquals(2, $emailMessage->files->count());
                unset($emailMessage);
                ForgetAllCacheUtil::forgetAllCaches();
            }
        }

        public function testCrudForHasOneAndHasManyEmailMessageRelations()
        {
            $super          = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailMessage   = EmailMessageTestHelper::createDraftSystemEmail('test crud relations', $super);
            $this->assertTrue($emailMessage->save());
            $emailMessageId = $emailMessage->id;
            $emailMessage->forgetAll();

            //Check read hasOne relation
            $emailMessage       = EmailMessage::getById($emailMessageId);
            $emailMessageSender = $emailMessage->sender;
            $this->assertEquals('system@somewhere.com', $emailMessageSender->fromAddress);

            //Check update hasOne relation
            $emailMessageSender->fromAddress = 'system@somewhere.org';
            $this->assertTrue($emailMessage->save());
            $emailMessage->forgetAll();
            $emailMessage       = EmailMessage::getById($emailMessageId);
            $emailMessageSender = $emailMessage->sender;
            $this->assertEquals('system@somewhere.org', $emailMessageSender->fromAddress);

            //Check delete hasOne relation
            $emailMessageSender2  = new EmailMessageSender();
            $emailMessageSender2->fromAddress = 'system@somewhere.org';
            $emailMessageSender2->fromName    = 'system name';
            $emailMessage->sender = $emailMessageSender2;
            $this->assertTrue($emailMessage->save());
            $emailMessage->forgetAll();
            $found                = true;
            try
            {
                EmailMessageSender::getById($emailMessageSender->id);
            }
            catch (NotFoundException $exception)
            {
                $found = false;
            }
            $this->assertFalse($found);

            //Check read hasMany relation
            $emailMessage       = EmailMessage::getById($emailMessageId);
            $recipients         = $emailMessage->recipients;
            $recipient          = $recipients[0];
            $this->assertCount (1, $recipients);
            $this->assertEquals('billy@fakeemail.com', $recipient->toAddress);

            //Check update hasMany relation
            $recipient->toAddress = 'billy@fakeemail.org';
            $this->assertTrue($emailMessage->save());
            $emailMessage->forgetAll();
            $emailMessage         = EmailMessage::getById($emailMessageId);
            $recipient            = $emailMessage->recipients[0];
            $this->assertEquals('billy@fakeemail.org', $recipient->toAddress);

            //Check add hasMany relation
            $recipient              = new EmailMessageRecipient();
            $recipient->toAddress   = 'anne@fakeemail.com';
            $recipient->toName      = 'Anne Frank';
            $recipient->type        = EmailMessageRecipient::TYPE_BCC;
            $emailMessage->recipients->add($recipient);
            $this->assertTrue($emailMessage->save());
            $emailMessage->forgetAll();
            $emailMessage           = EmailMessage::getById($emailMessageId);
            $recipients             = $emailMessage->recipients;
            $recipient              = $recipients[1];
            $this->assertCount (2, $recipients);

            //Check update hasMany relation with more than one model set
            $recipient->toAddress = 'anne@fakeemail.org';
            $this->assertTrue($emailMessage->save());
            $emailMessage->forgetAll();
            $emailMessage         = EmailMessage::getById($emailMessageId);
            $recipient            = $emailMessage->recipients[1];
            $this->assertEquals('anne@fakeemail.org', $recipient->toAddress);

            //Check delete hasMany relation
            $emailMessage->recipients->remove($recipient);
            $this->assertTrue($emailMessage->save());
            $emailMessage->forgetAll();
            $found                = true;
            try
            {
                EmailMessageRecipient::getById($recipient->id);
            }
            catch (NotFoundException $exception)
            {
                $found = false;
            }
            $this->assertFalse($found);

            //Check delete last hasMany relation model
            $emailMessage         = EmailMessage::getById($emailMessageId);
            $recipient             = $emailMessage->recipients[0];
            $emailMessage->recipients->remove($recipient);
            $this->assertTrue($emailMessage->save());
            $emailMessage->forgetAll();
            $found                = true;
            try
            {
                EmailMessageRecipient::getById($recipient->id);
            }
            catch (NotFoundException $exception)
            {
                $found = false;
            }
            $this->assertFalse($found);
            $this->assertCount(0, $emailMessage->recipients);
        }
    }
?>