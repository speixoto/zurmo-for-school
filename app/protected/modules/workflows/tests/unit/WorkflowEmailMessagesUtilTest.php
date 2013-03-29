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

    class WorkflowEmailMessagesUtilTest extends WorkflowBaseTest
    {
        protected static $savedWorkflow;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = User::getByUsername('super');
            $super->primaryEmail = new Email();
            $super->primaryEmail->emailAddress = 'super@zurmo.com';
            assert($super->save()); //Not Coding Standard
            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'some workflow';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'moduleClassName';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = 'some type';
            $savedWorkflow->serializedData  = serialize(array('something'));
            $saved                          = $savedWorkflow->save();
            assert($saved); // Not Coding Standard
            self::$savedWorkflow = $savedWorkflow;
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testProcessAfterSaveWhenSendIsInFuture()
        {
            $this->assertEquals(0, count(WorkflowMessageInQueue::getAll()));
            $workflow         = new Workflow();
            $workflow->setId(self::$savedWorkflow->id);
            $workflow->type   = Workflow::TYPE_ON_SAVE;
            $emailMessageForm = new EmailAlertForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $emailMessageForm->sendAfterDurationSeconds = 86400;
            $recipients = array(array('type'              => WorkflowEmailAlertRecipientForm::
                                                             TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                       'audienceType'    => EmailMessageRecipient::TYPE_TO,
                                       'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm::
                                                             DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $emailMessageForm->setAttributes(array(EmailAlertForWorkflowForm::EMAIL_ALERT_RECIPIENTS => $recipients));
            $workflow->addEmailAlert($emailMessageForm);
            $model = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $this->assertTrue($model->save());
            $compareDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 86400);
            WorkflowEmailMessagesUtil::processAfterSave($workflow, $model, Yii::app()->user->userModel);
            $workflowMessageInQueues = WorkflowMessageInQueue::getAll();
            $this->assertEquals(1, count($workflowMessageInQueues));
            $this->assertTrue($workflowMessageInQueues[0]->savedWorkflow->isSame(self::$savedWorkflow));
            $this->assertTrue($workflowMessageInQueues[0]->triggeredByUser->isSame(Yii::app()->user->userModel));
            $this->assertEquals($model->getClassId('Item'), $workflowMessageInQueues[0]->modelItem->getClassId('Item'));
            $this->assertEquals('WorkflowModelTestItem',    $workflowMessageInQueues[0]->modelClassName);
            $this->assertEquals($compareDateTime,           $workflowMessageInQueues[0]->processDateTime);
            $emailMessageData = SavedWorkflowToWorkflowAdapter::
                                makeArrayFromEmailAlertForWorkflowFormAttributesData(array($emailMessageForm));
            $this->assertEquals(serialize($emailMessageData), $workflowMessageInQueues[0]->serializedData);
            $this->assertTrue($workflowMessageInQueues[0]->delete());
        }

        /**
         * @depends testProcessAfterSaveWhenSendIsInFuture
         */
        public function testProcessAfterSaveWhenSendIsImmediate()
        {
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            $emailTemplate          = new EmailTemplate();
            $emailTemplate->name    = 'the name';
            $emailTemplate->type    = 2;
            $emailTemplate->subject = 'subject';
            $saved                  = $emailTemplate->save();
            $this->assertTrue($saved);
            $workflow         = new Workflow();
            $workflow->setId(self::$savedWorkflow->id);
            $workflow->type   = Workflow::TYPE_ON_SAVE;
            $emailMessageForm = new EmailAlertForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $emailMessageForm->sendAfterDurationSeconds = 0;
            $emailMessageForm->emailTemplateId = $emailTemplate->id;
            $emailMessageForm->sendFromType    = EmailAlertForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $recipients = array(array('type'             => WorkflowEmailAlertRecipientForm::
                                                            TYPE_DYNAMIC_TRIGGERED_BY_USER,
                                      'audienceType'    => EmailMessageRecipient::TYPE_TO));
            $emailMessageForm->setAttributes(array(EmailAlertForWorkflowForm::EMAIL_ALERT_RECIPIENTS => $recipients));
            $workflow->addEmailAlert($emailMessageForm);
            $model = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $this->assertTrue($model->save());
            WorkflowEmailMessagesUtil::processAfterSave($workflow, $model, Yii::app()->user->userModel);
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
        }
    }
?>