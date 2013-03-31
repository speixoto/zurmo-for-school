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

    class WorkflowEmailMessageProcessingHelperTest extends WorkflowBaseTest
    {
        protected static $superUserId;

        protected static $bobbyUserId;

        protected static $sarahUserId;

        protected static $emailTemplate;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = User::getByUsername('super');
            $super = User::getByUsername('super');
            $super->primaryEmail = new Email();
            $super->primaryEmail->emailAddress = 'super@zurmo.com';
            assert($super->save()); //Not Coding Standard
            $bobby = UserTestHelper::createBasicUserWithEmailAddress('bobby');
            $sarah = UserTestHelper::createBasicUserWithEmailAddress('sarah');
            self::$superUserId = $super->id;
            self::$bobbyUserId = $bobby->id;
            self::$sarahUserId = $sarah->id;

            $emailTemplate              = new EmailTemplate();
            $emailTemplate->type        = 1;
            $emailTemplate->name        = 'some template';
            $emailTemplate->subject     = 'some subject';
            $emailTemplate->htmlContent = 'html content';
            $emailTemplate->textContent = 'text content';
            $saved = $emailTemplate->save();
            assert($saved); // Not Coding Standard
            self::$emailTemplate = $emailTemplate;
        }

        public function testProcessWithDefaultSender()
        {
            $message               = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                      'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                      'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                      DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->emailTemplateId = self::$emailTemplate->id;
            $message->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));

            $model           = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $saved = $model->save();
            $this->assertTrue($saved);
            $helper = new WorkflowEmailMessageProcessingHelper($message, $model, Yii::app()->user->userModel);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $helper->process();
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals('text content',    $emailMessages[0]->content->textContent);
            $this->assertEquals('html content',    $emailMessages[0]->content->htmlContent);
            $this->assertEquals('Clark Kent',      $emailMessages[0]->sender->fromName);
            $this->assertEquals('super@zurmo.com', $emailMessages[0]->sender->fromAddress);
            $this->assertEquals(1,                 $emailMessages[0]->recipients->count());
            $this->assertEquals('super@zurmo.com', $emailMessages[0]->recipients[0]->toAddress);
            $emailMessages[0]->delete();
        }

        /**
         * @depends testProcessWithDefaultSender
         */
        public function testProcessWithCustomSender()
        {
            $message               = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                      'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                      'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                      DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->emailTemplateId = self::$emailTemplate->id;
            $message->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_CUSTOM;
            $message->sendFromAddress = 'someone@zurmo.com';
            $message->sendFromName    = 'Jason';
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));

            $model           = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $saved = $model->save();
            $this->assertTrue($saved);
            $helper = new WorkflowEmailMessageProcessingHelper($message, $model, Yii::app()->user->userModel);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $helper->process();
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals('text content',      $emailMessages[0]->content->textContent);
            $this->assertEquals('html content',      $emailMessages[0]->content->htmlContent);
            $this->assertEquals('Jason',             $emailMessages[0]->sender->fromName);
            $this->assertEquals('someone@zurmo.com', $emailMessages[0]->sender->fromAddress);
            $this->assertEquals(1,                   $emailMessages[0]->recipients->count());
            $this->assertEquals('super@zurmo.com',   $emailMessages[0]->recipients[0]->toAddress);
            $emailMessages[0]->delete();
        }
    }
?>