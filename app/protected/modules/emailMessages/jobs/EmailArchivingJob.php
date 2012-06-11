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

    /**
     * A job for retriving emails from dropbox(catch-all) folder
     */
    class EmailArchivingJob extends BaseJob
    {
        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Yii::t('Default', 'Process Inbound Email Job');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'ProcessInboundEmail';
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Yii::t('Default', 'Every 1 minute.');
        }

        /**
        * @returns the threshold for how long a job is allowed to run. This is the 'threshold'. If a job
        * is running longer than the threshold, the monitor job might take action on it since it would be
        * considered 'stuck'.
        */
        public static function getRunTimeThresholdInSeconds()
        {
            return 30;
        }

        /**
         *
         * (non-PHPdoc)
         * @see BaseJob::run()
         */
        public function run()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            Yii::app()->imap->connect();

            $lastImapCheckTime     = EmailMessagesModule::getLastImapDropboxCheckTime();
            if (isset($lastImapCheckTime) && $lastImapCheckTime != '')
            {
                $criteria = "SINCE \"{$lastImapCheckTime}\" UNDELETED";
                $lastImapCheckTimeStamp = strtotime($lastImapCheckTime);
            }
            else
            {
                $criteria = "ALL UNDELETED";
                $lastImapCheckTimeStamp = 0;
            }

            $messages = Yii::app()->imap->getMessages($criteria, $lastImapCheckTimeStamp);

            $lastCheckTime = null;
            if (count($messages))
            {
                foreach ($messages as $message)
                {
                    $lastMessageCreatedTime = strtotime($message->createdDate);
                    if (strtotime($message->createdDate) > strtotime($lastCheckTime))
                    {
                        $lastCheckTime = $message->createdDate;
                    }

                    // Get owner for message
                    try {
                        $emailOwner = EmailArchivingHelper::resolveOwnerOfEmailMessage($message);
                    }
                    catch (CException $e)
                    {
                        // User not found, or few users share same primary email address,
                        // so infrm user about issue and continue with next email.
                        $subject = Yii::t('Default', 'Invalid email address.');
                        $textContent = Yii::t('Default', 'Email address does not exist in system.') . "\n\n" . $message->textBody;
                        $htmlContent = Yii::t('Default', 'Email address does not exist in system.') . "<br><br>" . $message->htmlBody;

                        $emailMessage = new EmailMessage();
                        $emailMessage->owner   = Yii::app()->emailHelper->getUserToSendNotificationsAs();
                        $emailMessage->subject = $subject;
                        $emailContent              = new EmailMessageContent();
                        $emailContent->textContent = $textContent;
                        $emailContent->htmlContent = $htmlContent;
                        $emailMessage->content     = $emailContent;
                        $sender                    = new EmailMessageSender();
                        $sender->fromAddress       = Yii::app()->emailHelper->resolveFromAddressByUser(Yii::app()->user->userModel);
                        $sender->fromName          = strval(Yii::app()->user->userModel);
                        $sender->person            = Yii::app()->user->userModel;
                        $emailMessage->sender      = $sender;
                        $recipient                 = new EmailMessageRecipient();
                        $recipient->toAddress      = $message->fromEmail;
                        $recipient->type           = EmailMessageRecipient::TYPE_TO;
                        $emailMessage->recipients->add($recipient);
                        $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                        $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
                        $validated                 = $emailMessage->validate();
                        if (!$validated)
                        {
                            $this->addErrorsAsUsageErrors($emailMessage->getErrors());
                        }
                        Yii::app()->emailHelper->sendImmediately($emailMessage);
                        continue;
                    }

                    $senderInfo = EmailArchivingHelper::resolveEmailSenderFromEmailMessage($message);
                    if (!$senderInfo)
                    {
                        $subject = Yii::t('Default', "Sender info can't be extracted from email message.");
                        $textContent = Yii::t('Default', "Sender info can't be extracted from email message.") . "\n\n" . $message->textBody;
                        $htmlContent = Yii::t('Default', "Sender info can't be extracted from email message.") . "<br><br>" . $message->htmlBody;

                        $emailMessage = new EmailMessage();
                        $emailMessage->owner   = Yii::app()->emailHelper->getUserToSendNotificationsAs();
                        $emailMessage->subject = $subject;
                        $emailContent              = new EmailMessageContent();
                        $emailContent->textContent = $textContent;
                        $emailContent->htmlContent = $htmlContent;
                        $emailMessage->content     = $emailContent;
                        $sender                    = new EmailMessageSender();
                        $sender->fromAddress       = Yii::app()->emailHelper->resolveFromAddressByUser(Yii::app()->user->userModel);
                        $sender->fromName          = strval(Yii::app()->user->userModel);
                        $sender->person            = Yii::app()->user->userModel;
                        $emailMessage->sender      = $sender;
                        $recipient                 = new EmailMessageRecipient();
                        $recipient->toAddress      = $message->fromEmail;
                        $recipient->type           = EmailMessageRecipient::TYPE_TO;
                        $emailMessage->recipients->add($recipient);
                        $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                        $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
                        $validated                 = $emailMessage->validate();
                        if (!$validated)
                        {
                            $this->addErrorsAsUsageErrors($emailMessage->getErrors());
                        }
                        Yii::app()->emailHelper->sendImmediately($emailMessage);
                        continue;
                    }

                    $receiversInfo = EmailArchivingHelper::resolveEmailReceiversFromEmailMessage($message);
                    if (!$receiversInfo)
                    {
                        $subject = Yii::t('Default', "Receipt info can't be extracted from email message.");
                        $textContent = Yii::t('Default', "Receipt info can't be extracted from email message.") . "\n\n" . $message->textBody;
                        $htmlContent = Yii::t('Default', "Receipt info can't be extracted from email message.") . "<br><br>" . $message->htmlBody;

                        $emailMessage = new EmailMessage();
                        $emailMessage->owner   = Yii::app()->emailHelper->getUserToSendNotificationsAs();
                        $emailMessage->subject = $subject;
                        $emailContent              = new EmailMessageContent();
                        $emailContent->textContent = $textContent;
                        $emailContent->htmlContent = $htmlContent;
                        $emailMessage->content     = $emailContent;
                        $sender                    = new EmailMessageSender();
                        $sender->fromAddress       = Yii::app()->emailHelper->resolveFromAddressByUser(Yii::app()->user->userModel);
                        $sender->fromName          = strval(Yii::app()->user->userModel);
                        $sender->person            = Yii::app()->user->userModel;
                        $emailMessage->sender      = $sender;
                        $recipient                 = new EmailMessageRecipient();
                        $recipient->toAddress      = $message->fromEmail;
                        $recipient->type           = EmailMessageRecipient::TYPE_TO;
                        $emailMessage->recipients->add($recipient);
                        $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                        $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
                        $validated                 = $emailMessage->validate();
                        if (!$validated)
                        {
                            $this->addErrorsAsUsageErrors($emailMessage->getErrors());
                        }
                        Yii::app()->emailHelper->sendImmediately($emailMessage);
                        continue;
                    }

                    $emailMessage = new EmailMessage();
                    $emailMessage->owner   = $emailOwner;
                    // To-Do: Should we try to extract original subject, in case if message is forwarded,
                    // for example removing Fwd: prefix?
                    $emailMessage->subject = $message->subject;

                    $emailContent              = new EmailMessageContent();
                    $emailContent->textContent = $message->textBody;
                    $emailContent->htmlContent = $message->htmlBody;
                    $emailMessage->content     = $emailContent;

                    $sender                    = new EmailMessageSender();
                    $sender->fromAddress       = $senderInfo['email'];
                    if (isset($senderInfo['name']))
                    {
                        $sender->fromName          = $senderInfo['name'];
                    }
                    $emailMessage->sender      = $sender;

                    foreach ($receiversInfo as $receiverInfo)
                    {
                        $recipient                 = new EmailMessageRecipient();
                        $recipient->toAddress      = $receiverInfo['email'];
                        $recipient->toName         = $receiverInfo['name'];
                        $recipient->type           = EmailMessageRecipient::TYPE_TO;
                        $emailMessage->recipients->add($recipient);
                    }

                    $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                    $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_INBOX);

                    if (!empty($message->attachments))
                    {
                        foreach($message->attachments as $attachment)
                        {
                            if (!$attachment['is_attachment'])
                            {
                                continue;
                            }
                            // Save attachments
                            $fileContent          = new FileContent();
                            $fileContent->content = $attachment['attachment'];
                            $file                 = new EmailFileModel();
                            $file->fileContent    = $fileContent;
                            $file->name           = $attachment['filename'];
                            $file->type           = ZurmoFileHelper::getMimeType($attachment['filename']);
                            $file->size           = strlen($attachment['attachment']);
                            $saved                = $file->save();
                            assert('$saved'); // Not Coding Standard
                            $emailMessage->files->add($file);
                        }
                    }
                    $validated                 = $emailMessage->validate();
                    if (!$validated)
                    {
                        // To-Do::What to do if emailMessage couldn't be validated???
                        // Email user
                    }
                    $saved = $emailMessage->save();

                    try {
                        if (!$saved)
                        {
                            throw new NotSupportedException();
                        }
                    }
                    catch (NotSupportedException $e)
                    {
                        // To-Do::What to do if emailMessage couldn't be saved???
                        // Should we send some email to email owner?
                    }
                }
                if ($lastCheckTime != ''){
                    EmailMessagesModule::setLastImapDropboxCheckTime($lastCheckTime);
                }
            }
            return true;
        }
    }
?>