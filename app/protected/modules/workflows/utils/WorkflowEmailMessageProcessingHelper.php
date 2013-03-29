<?php
/*********************************************************************************
 * Zurmo is a customer relationship management program developed by
 * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class WorkflowEmailMessageProcessingHelper
    {
        protected $emailMessageForm;

        protected $triggeredModel;

        protected $triggeredByUser;

        public function __construct(EmailMessageForWorkflowForm $emailMessageForm, RedBeanModel $triggeredModel, User $triggeredByUser)
        {
            $this->emailMessageForm  = $emailMessageForm;
            $this->triggeredModel    = $triggeredModel;
            $this->triggeredByUser     = $triggeredByUser;
        }

        public function process()
        {
            $emailTemplate              = EmailTemplate::getById((int)$this->emailMessageForm->emailTemplateId);
            $emailMessage               = new EmailMessage();
            $emailMessage->owner        = $this->triggeredByUser;
            $emailMessage->subject      = $emailTemplate->subject;
            $emailContent               = new EmailMessageContent();
            $emailContent->textContent  = $this->resolveEmailTemplateTextContentForModelData($emailTemplate);
            $emailContent->htmlContent  = $this->resolveEmailTemplateHtmlContentForModelData($emailTemplate);
            $emailMessage->content      = $emailContent;
            $emailMessage->sender       = $this->resolveSender();
            $this->resolveRecipients($emailMessage);
            if($emailMessage->recipients->count() == 0)
            {
                throw new MissingRecipientsForEmailMessageException();
            }
            $box                        = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            Yii::app()->emailHelper->send($emailMessage);
        }

        protected function resolveEmailTemplateTextContentForModelData(EmailTemplate $emailTemplate)
        {
            //$this->triggeredModel
            return $emailTemplate->textContent;
        }

        protected function resolveEmailTemplateHtmlContentForModelData(EmailTemplate $emailTemplate)
        {
            //$this->triggeredModel
            return $emailTemplate->htmlContent;
        }

        protected function resolveSender()
        {
            $sender                     = new EmailMessageSender();
            if($this->emailMessageForm->sendFromType == EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT)
            {
                $userToSendMessagesFrom     = Yii::app()->emailHelper->getUserToSendNotificationsAs();
                $sender->fromAddress        = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
                $sender->fromName           = strval($userToSendMessagesFrom);
            }
            elseif($this->emailMessageForm->sendFromType == EmailMessageForWorkflowForm::SEND_FROM_TYPE_CUSTOM)
            {
                $sender->fromAddress        = $this->emailMessageForm->sendFromAddress;
                $sender->fromName           = $this->emailMessageForm->sendFromName;
            }
            else
            {
                throw new NotSupportedException();
            }
            return $sender;
        }

        protected function resolveRecipients(EmailMessage $emailMessage)
        {
            foreach($this->emailMessageForm->getEmailMessageRecipients() as $emailMessageRecipient)
            {
                foreach($emailMessageRecipient->makeRecipients($this->triggeredModel, $this->triggeredByUser) as $recipient)
                {
                    $emailMessage->recipients->add($recipient);
                }
            }
        }
    }
?>