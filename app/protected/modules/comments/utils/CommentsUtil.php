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
     * Helper class for working with comments
     */
    class CommentsUtil
    {

        public static function resolveEmailNewComment($senderPerson, $participants, $subject, $content)
        {
            assert('$senderPerson instanceof User');
            assert('is_array($participants)');
            assert('is_string($subject)');
            assert('$content instanceof EmailMessageContent');
            if (count($participants) == 0)
            {
                return;
            }
            $userToSendMessagesFrom     = $senderPerson;
            $emailMessage               = new EmailMessage();
            $emailMessage->owner        = $senderPerson;
            $emailMessage->subject      = $subject;
            $emailMessage->content      = $content;
            $sender                     = new EmailMessageSender();
            $sender->fromAddress        = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
            $sender->fromName           = strval($userToSendMessagesFrom);
            $sender->personOrAccount    = $userToSendMessagesFrom;
            $emailMessage->sender       = $sender;
            foreach ($participants as $recipientPerson)
            {
                $recipient                  = new EmailMessageRecipient();
                $recipient->toAddress       = $recipientPerson->primaryEmail->emailAddress;
                $recipient->toName          = strval($recipientPerson);
                $recipient->type            = EmailMessageRecipient::TYPE_TO;
                $recipient->personOrAccount = $recipientPerson;
                $emailMessage->recipients->add($recipient);
            }
            $box                        = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            Yii::app()->emailHelper->send($emailMessage);
        }

        public static function getEmailContent($model, $comment, $updater)
        {
            $emailContent  = new EmailMessageContent();
            $url           = static::getUrlToEmail($model);
            $textContent = Yii::t('Default', "Hello, {lineBreak} {updaterName} added a new comment to the " .
                                             "{strongStartTag}{modelString}{strongEndTag}: {lineBreak}" .
                                             "\"{commentDescription}.\" {lineBreak}{lineBreak} {url} " .
                                             "{lineBreak} --------------------------------------- {lineBreak} " .
                                             "This message was sent automaticaly by the ZurmoCRM",
                               array('{lineBreak}'           => "\n",
                                     '{strongStartTag}'      => null,
                                     '{strongEndTag}'        => null,
                                     '{updaterName}'         => strval($updater),
                                     '{modelString}'         => strtolower(get_class($model)) . ' ' . strval($model),
                                     '{commentDescription}'  => strval($comment),
                                     '{url}'                 => ZurmoHtml::link($url, $url)
                                   ));
            $emailContent->textContent  = $textContent;
            $htmlContent = Yii::t('Default', "Hello, {lineBreak} {updaterName} added a new comment to the " .
                                             "{strongStartTag}{modelString}{strongEndTag}: {lineBreak}" .
                                             "\"{commentDescription}.\" {lineBreak}{lineBreak} {url} " .
                                             "{lineBreak} --------------------------------------- {lineBreak} " .
                                             "This message was sent automaticaly by the ZurmoCRM",
                               array('{lineBreak}'           => "<br/>",
                                     '{strongStartTag}'      => '<strong>',
                                     '{strongEndTag}'        => '</strong>',
                                     '{updaterName}'         => strval($updater),
                                     '{modelString}'         => strtolower(get_class($model)) . ' ' . strval($model),
                                     '{commentDescription}'  => strval($comment),
                                     '{url}'                 => ZurmoHtml::link($url, $url)
                                   ));
            $emailContent->htmlContent  = $htmlContent;
            return $emailContent;
        }

        protected static function getUrlToEmail($model)
        {
            if ($model instanceof Conversation)
            {
                return Yii::app()->createAbsoluteUrl('conversations/default/details/', array('id' => $model->id));
            }
        }
    }