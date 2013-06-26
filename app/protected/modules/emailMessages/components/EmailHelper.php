<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Component for working with outbound and inbound email transport
     */
    class EmailHelper extends CApplicationComponent
    {
        const OUTBOUND_TYPE_SMTP = 'smtp';

        /**
         * Currently supports smtp.
         * @var string
         */
        public $outboundType = self::OUTBOUND_TYPE_SMTP;

        /**
         * Outbound mail server host name. Example mail.someplace.com
         * @var string
         */
        public $outboundHost;

        /**
         * Outbound mail server port number. Default to 25, but it can be set to something different.
         * @var integer
         */
        public $outboundPort = 25;

        /**
         * Outbound mail server username. Not always required, depends on the setup.
         * @var string
         */
        public $outboundUsername;

        /**
         * Outbound mail server password. Not always required, depends on the setup.
         * @var string
         */
        public $outboundPassword;

        /**
         * Outbound mail server security. Options: null, 'ssl', 'tls'
         * @var string
         */
        public $outboundSecurity;

        /**
         * Name to use in the email sent
         * @var string
         */
        public $fromName;

        /**
         * Address to use in the email sent
         * @var string
         */
        public $fromAddress;

        /**
         * Contains array of settings to load during initialization from the configuration table.
         * @see loadOutboundSettings
         * @var array
         */
        protected $settingsToLoad = array(
            'outboundType',
            'outboundHost',
            'outboundPort',
            'outboundUsername',
            'outboundPassword',
            'outboundSecurity'
        );

        /**
         * Fallback from address to use for sending out notifications.
         * @var string
         */
        public $defaultFromAddress   = 'notifications@zurmoalerts.com';

        /**
         * Utilized when sending a test email nightly to check the status of the smtp server
         * @var string
         */
        public $defaultTestToAddress = 'testJobEmail@zurmoalerts.com';

        /**
         * Called once per page load, will load up outbound settings from the database if available.
         * (non-PHPdoc)
         * @see CApplicationComponent::init()
         */
        public function init()
        {
            $this->loadOutboundSettings();
        }

        protected function loadOutboundSettings()
        {
            foreach ($this->settingsToLoad as $keyName)
            {
                if ($keyName == 'outboundPassword')
                {
                    $encryptedKeyValue = ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', $keyName);
                    if ($encryptedKeyValue !== '' && $encryptedKeyValue !== null)
                    {
                        $keyValue = ZurmoPasswordSecurityUtil::decrypt($encryptedKeyValue);
                    }
                    else
                    {
                        $keyValue = null;
                    }
                }
                else
                {
                    $keyValue = ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', $keyName);
                }
                if (null !== $keyValue)
                {
                    $this->$keyName = $keyValue;
                }
            }
        }

        /**
         * Load user's outbound settings from user's email account or the system settings
         * @param User   $user
         * @param string $name  EmailAccount name or null for default name
         */
        public function loadOutboundSettingsFromUserEmailAccount(User $user, $name = null)
        {
            $userEmailAccount = EmailAccount::getByUserAndName($user, $name);
            if ($userEmailAccount->useCustomOutboundSettings)
            {
                $settingsToLoad = array_merge($this->settingsToLoad, array('fromName', 'fromAddress'));
                foreach ($settingsToLoad as $keyName)
                {
                    if ($keyName == 'outboundPassword')
                    {
                        $keyValue = ZurmoPasswordSecurityUtil::decrypt($userEmailAccount->$keyName);
                        $this->$keyName = $keyValue;
                    }
                    else
                    {
                        $this->$keyName = $userEmailAccount->$keyName;
                    }
                }
            }
            else
            {
                $this->loadOutboundSettings();
                $this->fromName = strval($user);
                $this->fromAddress = $this->resolveFromAddressByUser($user);
            }
        }

        /**
         * Set outbound settings into the database.
         */
        public function setOutboundSettings()
        {
            foreach ($this->settingsToLoad as $keyName)
            {
                if ($keyName == 'outboundPassword')
                {
                    $password = ZurmoPasswordSecurityUtil::encrypt($this->$keyName);
                    ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', $keyName, $password);
                }
                else
                {
                    ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', $keyName, $this->$keyName);
                }
            }
        }

        /**
         * Send an email message. This will queue up the email to be sent by the queue sending process. If you want to
         * send immediately, consider using @sendImmediately
         * @param EmailMessage $emailMessage
         * @throws NotSupportedException
         * @return boolean
         */
        public function send(EmailMessage $emailMessage)
        {
            if ($emailMessage->folder->type == EmailFolder::TYPE_OUTBOX ||
                $emailMessage->folder->type == EmailFolder::TYPE_SENT ||
                $emailMessage->folder->type == EmailFolder::TYPE_OUTBOX_ERROR ||
                $emailMessage->folder->type == EmailFolder::TYPE_OUTBOX_FAILURE)
            {
                throw new NotSupportedException();
            }
            $emailMessage->folder   = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox, EmailFolder::TYPE_OUTBOX);
            $saved                  = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            return true;
        }

        /**
         * Use this method to send immediately, instead of putting an email in a queue to be processed by a scheduled
         * job.
         * @param EmailMessage $emailMessage
         * @throws NotSupportedException - if the emailMessage does not properly save.
         * @return null
         */
        public function sendImmediately(EmailMessage $emailMessage)
        {
            if ($emailMessage->folder->type == EmailFolder::TYPE_SENT)
            {
                throw new NotSupportedException();
            }
            $mailer           = $this->getOutboundMailer();
            $this->populateMailer($mailer, $emailMessage);
            $this->sendEmail($mailer, $emailMessage);
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Call this method to process all email Messages in the queue. This is typically called by a scheduled job
         * or cron.  This will process all emails in a TYPE_OUTBOX folder or TYPE_OUTBOX_ERROR folder. If the message
         * has already been sent 3 times then it will be moved to a failure folder.
         */
        public function sendQueued()
        {
            $queuedEmailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            foreach ($queuedEmailMessages as $emailMessage)
            {
                $this->sendImmediately($emailMessage);
            }
            $queuedEmailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX_ERROR);
            foreach ($queuedEmailMessages as $emailMessage)
            {
                if ($emailMessage->sendAttempts < 3)
                {
                    $this->sendImmediately($emailMessage);
                }
                else
                {
                    $this->processMessageAsFailure($emailMessage);
                }
            }
            return true;
        }

        protected function processMessageAsFailure(EmailMessage $emailMessage)
        {
            $emailMessage->folder = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox,
                                    EmailFolder::TYPE_OUTBOX_FAILURE);
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
        }

        protected function populateMailer(Mailer $mailer, EmailMessage $emailMessage)
        {
            $mailer->mailer   = $this->outboundType;
            $mailer->host     = $this->outboundHost;
            $mailer->port     = $this->outboundPort;
            $mailer->username = $this->outboundUsername;
            $mailer->password = $this->outboundPassword;
            $mailer->security = $this->outboundSecurity;
            $mailer->Subject  = $emailMessage->subject;
            $mailer->headers  = unserialize($emailMessage->headers);
            if ($emailMessage->content->htmlContent == null && $emailMessage->content->textContent != null)
            {
                $mailer->body     = $emailMessage->content->textContent;
                $mailer->altBody  = $emailMessage->content->textContent;
            }
            elseif ($emailMessage->content->htmlContent != null && $emailMessage->content->textContent == null)
            {
                $mailer->body     = $emailMessage->content->htmlContent;
            }
            elseif ($emailMessage->content->htmlContent != null && $emailMessage->content->textContent != null)
            {
                $mailer->body     = $emailMessage->content->htmlContent;
                $mailer->altBody  = $emailMessage->content->textContent;
            }
            $mailer->From = array($emailMessage->sender->fromAddress => $emailMessage->sender->fromName);
            foreach ($emailMessage->recipients as $recipient)
            {
                $mailer->addAddressByType($recipient->toAddress, $recipient->toName, $recipient->type);
            }

            if (isset($emailMessage->files) && !empty($emailMessage->files))
            {
                foreach ($emailMessage->files as $file)
                {
                    $mailer->attachDynamicContent($file->fileContent->content, $file->name, $file->type);
                    //$emailMessage->attach($attachment);
                }
            }
        }

        protected function sendEmail(Mailer $mailer, EmailMessage $emailMessage)
        {
            try
            {
                $emailMessage->sendAttempts = $emailMessage->sendAttempts + 1;
                $acceptedRecipients         = $mailer->send();
                // Code below is quick fix, we need to think about better solution
                // Here is related PT story: https://www.pivotaltracker.com/projects/380027#!/stories/45841753
                //if ($acceptedRecipients != $emailMessage->recipients->count())
                if ($acceptedRecipients <= 0)
                {
                    $content = Zurmo::t('EmailMessagesModule', 'Response from Server') . "\n";
                    foreach ($mailer->getSendResponseLog() as $logMessage)
                    {
                        $content .= $logMessage . "\n";
                    }
                    $emailMessageSendError = new EmailMessageSendError();
                    $data                  = array();
                    $data['message']                       = $content;
                    $emailMessageSendError->serializedData = serialize($data);
                    $emailMessage->folder                  = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox,
                                                                                          EmailFolder::TYPE_OUTBOX_ERROR);
                    $emailMessage->error                   = $emailMessageSendError;
                }
                else
                {
                    $emailMessage->error        = null;
                    $emailMessage->folder       = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox, EmailFolder::TYPE_SENT);
                    $emailMessage->sentDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                }
            }
            catch (OutboundEmailSendException $e)
            {
                $emailMessageSendError = new EmailMessageSendError();
                $data = array();
                $data['code']                          = $e->getCode();
                $data['message']                       = $e->getMessage();
                $data['trace']                         = $e->getPrevious();
                $emailMessageSendError->serializedData = serialize($data);
                $emailMessage->folder   = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox, EmailFolder::TYPE_OUTBOX_ERROR);
                $emailMessage->error    = $emailMessageSendError;
            }
        }

        protected function getOutboundMailer()
        {
            $mailer = new ZurmoSwiftMailer();
            $mailer->init();
            return $mailer;
        }

        /**
         * @return integer count of how many emails are queued to go.  This means they are in either the TYPE_OUTBOX
         * folder or the TYPE_OUTBOX_ERROR folder.
         */
        public function getQueuedCount()
        {
            return count(EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX)) +
                   count(EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX_ERROR));
        }

        /**
         * Given a user, attempt to get the user's email address, but if it is not available, then return the default
         * address.  @see EmailHelper::defaultFromAddress
         * @param User $user
         */
        public function resolveFromAddressByUser(User $user)
        {
            assert('$user->id >0');
            if ($user->primaryEmail->emailAddress == null)
            {
                return $this->defaultFromAddress;
            }
            return $user->primaryEmail->emailAddress;
        }
    }
?>