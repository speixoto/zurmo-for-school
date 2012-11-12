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

    class EmailMessageUtil
    {
        /**
         * Send Email from $_POST data
         * @param User $userToSendMessagesFrom
         * @return boolean
         */
        public static function resolveEmailMessageFromPostData(Array & $postData, EmailMessage $emailMessage, User $userToSendMessagesFrom)
        {
            $postVariableName   = get_class($emailMessage);
            Yii::app()->emailHelper->loadOutboundSettingsFromUserEmailAccount($userToSendMessagesFrom);
            $toRecipients = explode(",", $postData[$postVariableName]['recipients']['to']);
            static::attachRecipientsToMessage($toRecipients, $emailMessage, EmailMessageRecipient::TYPE_TO);
            $ccRecipients = explode(",", $postData[$postVariableName]['recipients']['cc']);
            static::attachRecipientsToMessage($ccRecipients, $emailMessage, EmailMessageRecipient::TYPE_CC);
            $bccRecipients = explode(",", $postData[$postVariableName]['recipients']['bcc']);
            static::attachRecipientsToMessage($bccRecipients, $emailMessage, EmailMessageRecipient::TYPE_BCC);
            unset($postData[$postVariableName]['recipients']);
            if (isset($postData['filesIds']))
            {
                static::attachFilesToMessage($postData['filesIds'], $emailMessage);
            }
            $emailAccount              = EmailAccount::getByUserAndName($userToSendMessagesFrom);
            $sender                    = new EmailMessageSender();
            $sender->fromName          = Yii::app()->emailHelper->fromName;
            $sender->fromAddress       = Yii::app()->emailHelper->fromAddress;
            $sender->personOrAccount   = $userToSendMessagesFrom;
            $emailMessage->sender      = $sender;
            $emailMessage->account     = $emailAccount;
            $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX);
            return $emailMessage;
        }

        public static function attachRecipientsToMessage(Array $recipients, EmailMessage $emailMessage, $type)
        {
            $existingPersonsOrAccounts = array();
            if($emailMessage->recipients->count() >0)
            {
                foreach($emailMessage->recipients as $recipient)
                {
                    if($recipient->personOrAccount != null && $recipient->personOrAccount->id > 0)
                    {
                        $existingPersonsOrAccounts[] = $recipient->personOrAccount->getClassId('Item');
                    }
                }
            }
            foreach ($recipients as $recipient)
            {
                if ($recipient != null)
                {
                    $personsOrAccounts = EmailArchivingUtil::
                                         getPersonsAndAccountsByEmailAddressForUser($recipient, Yii::app()->user->userModel);
                    if(empty($personsOrAccounts))
                    {
                        $personsOrAccounts[] = null;
                    }
                    foreach($personsOrAccounts as $personOrAccount)
                    {
                        if(!in_array($personOrAccount->getClassId('Item'), $existingPersonsOrAccounts))
                        {
                            $messageRecipient                   = new EmailMessageRecipient();
                            $messageRecipient->toName           = strval($personOrAccount);
                            $messageRecipient->toAddress        = $recipient;
                            $messageRecipient->type             = $type;
                            $messageRecipient->personOrAccount  = $personOrAccount;
                            $emailMessage->recipients->add($messageRecipient);
                            $existingPersonsOrAccounts[] = $personOrAccount->getClassId('Item');
                        }
                    }
                }
            }
        }

        public static function attachFilesToMessage(Array $filesIds, $emailMessage)
        {
            foreach ($filesIds as $fileId)
            {
                $attachment = FileModel::getById((int)$fileId);
                $emailMessage->files->add($attachment);
            }
        }

        public static function resolveSignatureToEmailMessage(EmailMessage $emailMessage, User $user)
        {
            if($user->emailSignatures->count() > 0 && $user->emailSignatures[0]->htmlContent != null)
            {
                $emailMessage->content->htmlContent = '<p><br/></p><p>' . $user->emailSignatures[0]->htmlContent . '</p>';
            }
        }

        public static function resolvePersonOrAccountToEmailMessage(EmailMessage $emailMessage, User $user,
                                                                    $toAddress = null, $relatedId = null,
                                                                    $relatedModelClassName = null)
        {
            assert('is_string($toAddress) || $toAddress == null');
            assert('is_string($relatedId) || $relatedId == null');
            assert('$relatedModelClassName == "Account" || $relatedModelClassName == "Contact" ||
                    $relatedModelClassName == "User" ||$relatedModelClassName == null');
            if ($toAddress != null && $relatedId != null && $relatedModelClassName != null)
            {
                $personOrAccount                    = $relatedModelClassName::getById((int)$relatedId);
                $messageRecipient                   = new EmailMessageRecipient();
                $messageRecipient->toName           = strval($personOrAccount);
                $messageRecipient->toAddress        = $toAddress;
                $messageRecipient->type             = EmailMessageRecipient::TYPE_TO;
                $messageRecipient->personOrAccount  = $personOrAccount;
                $emailMessage->recipients->add($messageRecipient);
            }
        }

        public static function renderEmailAddressAsMailToOrModalLinkStringContent($emailAddress, RedBeanModel $model)
        {
            assert('is_string($emailAddress)');
            $userCanAccess   = RightsUtil::canUserAccessModule('EmailMessagesModule', Yii::app()->user->userModel);
            $userCanCreate   = RightsUtil::doesUserHaveAllowByRightName(
                               'EmailMessagesModule',
                               EmailMessagesModule::RIGHT_CREATE_EMAIL_MESSAGES,
                               Yii::app()->user->userModel);
            if(!$userCanAccess || !$userCanCreate)
            {
                $showLink = false;
            }
            else
            {
                $showLink = true;
            }
            if($showLink && !($model instanceof Account))
            {
                $url               = Yii::app()->createUrl('/emailMessages/default/createEmailMessage',
                                                           array('toAddress'			 => $emailAddress,
                                                                 'relatedId'             => $model->id,
                                                                 'relatedModelClassName' => get_class($model)));
                $modalAjaxOptions  = ModalView::getAjaxOptionsForModalLink(
                                     Yii::t('Default', 'Compose Email'), 'modalContainer', 'auto', 800);
                $content           = ZurmoHtml::ajaxLink($emailAddress, $url, $modalAjaxOptions);
            }
            else
            {
                $content  = Yii::app()->format->email($emailAddress);
            }
            return $content;
        }
    }
?>