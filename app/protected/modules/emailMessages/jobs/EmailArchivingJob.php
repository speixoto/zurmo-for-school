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
            return 'EmailArchiving';
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
                        $emailOwner = EmailArchivingUtil::resolveOwnerOfEmailMessage($message);
                    }
                    catch (CException $e)
                    {
                        // User not found, or few users share same primary email address,
                        // so inform user about issue and continue with next email.
                        $this->resolveMessageSubjectAndContentAndSendSystemMessage('OwnerNotExist', $message);
                        continue;
                    }

                    $senderInfo = EmailArchivingUtil::resolveEmailSenderFromEmailMessage($message);
                    if (!$senderInfo)
                    {
                        $this->resolveMessageSubjectAndContentAndSendSystemMessage('SenderNotExtracted', $message);
                        continue;
                    }
                    else
                    {
                        $sender                    = new EmailMessageSender();
                        $sender->fromAddress       = $senderInfo['email'];
                        if (isset($senderInfo['name']))
                        {
                            $sender->fromName          = $senderInfo['name'];
                        }

                        $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($senderInfo['email']);
                        $sender->personOrAccount = $personOrAccount;
                    }

                    $recipientsInfo = EmailArchivingUtil::resolveEmailRecipientsFromEmailMessage($message);
                    if (!$recipientsInfo)
                    {
                        $this->resolveMessageSubjectAndContentAndSendSystemMessage('RecipientNotExtracted', $message);
                        continue;
                    }

                    $emailMessage = new EmailMessage();
                    $emailMessage->owner   = $emailOwner;
                    $emailMessage->subject = $message->subject;

                    $emailContent              = new EmailMessageContent();
                    $emailContent->textContent = $message->textBody;
                    $emailContent->htmlContent = $message->htmlBody;
                    $emailMessage->content     = $emailContent;
                    $emailMessage->sender      = $sender;

                    foreach ($recipientsInfo as $recipientInfo)
                    {
                        $recipient                 = new EmailMessageRecipient();
                        $recipient->toAddress      = $recipientInfo['email'];
                        $recipient->toName         = $recipientInfo['name'];
                        $recipient->type           = EmailMessageRecipient::TYPE_TO;

                        $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($recipientInfo['email']);
                        $recipient->personOrAccount = $personOrAccount;
                        $emailMessage->recipients->add($recipient);
                    }

                    $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                    $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_ARCHIVED);

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
                        // Email message couldn't be validated(some related models can't be validated). Email user.
                        $this->resolveMessageSubjectAndContentAndSendSystemMessage('EmailMessageNotValidated', $message);
                    }

                    $saved = $emailMessage->save();
                    try {
                        if (!$saved)
                        {
                            throw new NotSupportedException();
                        }
                        Yii::app()->imap->deleteMessage($message->msgNumber);
                    }
                    catch (NotSupportedException $e)
                    {
                        // Email message couldn't be saved. Inform user
                        $this->resolveMessageSubjectAndContentAndSendSystemMessage('EmailMessageNotSaved', $message);
                    }
                }
                Yii::app()->imap->expungeMessages();
                if ($lastCheckTime != ''){
                    EmailMessagesModule::setLastImapDropboxCheckTime($lastCheckTime);
                }
            }
            return true;
        }

        /**
         * Resolve system message to be sent, and send it
         * @param string $messageType
         * @param ImapMessage $originalMessage
         * @return boolean
         * @throws NotSupportedException
         */
        protected function resolveMessageSubjectAndContentAndSendSystemMessage($messageType, $originalMessage)
        {
            switch ($messageType)
            {
                case "OwnerNotExist":
                    $subject = Yii::t('Default', 'Invalid email address.');
                    $textContent = Yii::t('Default', 'Email address does not exist in system.') . "\n\n" . $originalMessage->textBody;
                    $htmlContent = Yii::t('Default', 'Email address does not exist in system.') . "<br><br>" . $originalMessage->htmlBody;
                    break;
                case "SenderNotExtracted":
                    $subject = Yii::t('Default', "Sender info can't be extracted from email message.");
                    $textContent = Yii::t('Default', "Sender info can't be extracted from email message.") . "\n\n" . $originalMessage->textBody;
                    $htmlContent = Yii::t('Default', "Sender info can't be extracted from email message.") . "<br><br>" . $originalMessage->htmlBody;
                    break;
                case "RecipientNotExtracted":
                    $subject = Yii::t('Default', "Recipient info can't be extracted from email message.");
                    $textContent = Yii::t('Default', "Recipient info can't be extracted from email message.") . "\n\n" . $originalMessage->textBody;
                    $htmlContent = Yii::t('Default', "Recipient info can't be extracted from email message.") . "<br><br>" . $originalMessage->htmlBody;
                    break;
                case "EmailMessageNotValidated":
                    $subject = Yii::t('Default', "Email message could not be validated.");
                    $textContent = Yii::t('Default', "Email message could not be validated.") . "\n\n" . $originalMessage->textBody;
                    $htmlContent = Yii::t('Default', "Email message could not be validated.") . "<br><br>" . $originalMessage->htmlBody;
                    break;
                case "EmailMessageNotSaved":
                    $subject = Yii::t('Default', "Email message could not be saved.");
                    $textContent = Yii::t('Default', "Email message could not be saved.") . "\n\n" . $originalMessage->textBody;
                    $htmlContent = Yii::t('Default', "Email message could not be saved.") . "<br><br>" . $originalMessage->htmlBody;
                    break;
                default:
                    throw NotSupportedException();
            }
            return EmailMessageHelper::sendSystemEmail($subject, array($originalMessage->fromEmail), $textContent, $htmlContent);
        }
    }
?>