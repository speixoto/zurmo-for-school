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
     * Email command is used for testing and troubleshooting email connections. This can also be used to send
     * emails.
     */
    class EmailCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            // Begin Not Coding Standard
            return <<<EOD
    USAGE
      zurmoc email <action-name>   --username=user
                                   --toAddress=address
                                   --subject=subject
                                   --textContent=content
                                   --htmlContent=content
                                   --host=host
                                   --port=port
                                   --outboundUsername=username
                                   --outboundPassword=password
                                   --outboundSecurity=security

    DESCRIPTION
      Send an email messages.  Use double quotes to to make a sentence for a subject or content
      An example is --subject="Welcome to Zurmo"
      Note: If the outbound settings are not provided, command will attempt to use any saved setting in Zurmo.

    PARAMETERS
     * action-name: The action to use. Currently supports 'send'
     * username: username to log in as and run the job. Typically 'super'. Must be a super adminstrator.
     * toAddress: the email address to send the email too

     Optional Parameters:
     * subject: optional Subject
     * textContent: optional textContent
     * htmlContent: optional htmlContent
     * host: optional host setting. Otherwise system setting will be used.
     * port: optional port setting. Otherwise system setting will be used.
     * outboundUsername: optional outbound username setting. Otherwise system setting will be used.
     * outboundPassword: optional outbound password setting. Otherwise system setting will be used.
     * outboundSecurity: optional outbound mail server security. Options: null, 'ssl', 'tls'.

EOD;
    // End Not Coding Standard
    }

    /**
     * Execute the action.
     * @param array command line parameters specific for this command
     */
    public function actionSend($username,
                               $toAddress,
                               $subject          = 'A test email from Zurmo',
                               $textContent      = 'A test text message from Zurmo.',
                               $htmlContent      = 'A test html message from Zurmo.',
                               $host             = null,
                               $port             = null,
                               $outboundUsername = null,
                               $outboundPassword = null,
                               $outboundSecurity = null)
    {
        if (!isset($username))
        {
            $this->usageError('A username must be specified.');
        }
        if (!isset($toAddress))
        {
            $this->usageError('You must specify a to address.');
        }
        try
        {
            Yii::app()->user->userModel = User::getByUsername($username);
        }
        catch (NotFoundException $e)
        {
            $this->usageError('The specified username does not exist.');
        }
        if ($host != null)
        {
            Yii::app()->emailHelper->outboundHost = $host;
        }
        if ($port != null)
        {
            Yii::app()->emailHelper->outboundPort = $port;
        }
        if ($outboundUsername != null)
        {
            Yii::app()->emailHelper->outboundUsername = $outboundUsername;
        }
        if ($outboundUsername != null)
        {
            Yii::app()->emailHelper->outboundPassword = $outboundPassword;
        }
        if ($outboundSecurity != null && $outboundSecurity != '' && $outboundSecurity != 'false')
        {
            Yii::app()->emailHelper->outboundSecurity = $outboundSecurity;
        }
        else
        {
            Yii::app()->emailHelper->outboundSecurity = null;
        }

        echo "\n";
        echo 'Using type:' . Yii::app()->emailHelper->outboundType . "\n";
        echo 'Using host:' . Yii::app()->emailHelper->outboundHost . "\n";
        echo 'Using port:' . Yii::app()->emailHelper->outboundPort . "\n";
        echo 'Using username:' . Yii::app()->emailHelper->outboundUsername . "\n";
        echo 'Using password:' . Yii::app()->emailHelper->outboundPassword . "\n";
        if (isset(Yii::app()->emailHelper->outboundSecurity))
        {
            echo 'Using outbound security:' . Yii::app()->emailHelper->outboundSecurity . "\n\n";
        }
        else
        {
            echo 'Using outbound security: none' . "\n\n";
        }
        echo 'Sending Email Message' . "\n";

        $emailMessage = new EmailMessage();
        $emailMessage->owner   = BaseControlUserConfigUtil::getUserToRunAs();
        $emailMessage->subject = $subject;
        $emailContent              = new EmailMessageContent();
        $emailContent->textContent = $textContent;
        $emailContent->htmlContent = $htmlContent;
        $emailMessage->content     = $emailContent;
        $sender                    = new EmailMessageSender();
        $sender->fromAddress       = Yii::app()->emailHelper->resolveFromAddressByUser(Yii::app()->user->userModel);
        $sender->fromName          = strval(Yii::app()->user->userModel);
        $sender->personOrAccount   = Yii::app()->user->userModel;
        $emailMessage->sender      = $sender;
        $recipient                 = new EmailMessageRecipient();
        $recipient->toAddress      = $toAddress;
        $recipient->toName         = 'Test Recipient';
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

        if (!$emailMessage->hasSendError())
        {
            echo Zurmo::t('Commands', 'Message successfully sent') . "\n";
        }
        else
        {
            echo Zurmo::t('Commands', 'Message failed to send') . "\n";
            echo $emailMessage->error     . "\n";
        }
        $saved = $emailMessage->save();
        if (!$saved)
        {
            throw new NotSupportedException();
        }
    }

    protected function addErrorsAsUsageErrors(array $errors)
    {
        foreach ($errors as $errorData)
        {
            foreach ($errorData as $errorOrRelatedError)
            {
                if (is_array($errorOrRelatedError))
                {
                    foreach ($errorOrRelatedError as $relatedError)
                    {
                        if (is_array($relatedError))
                        {
                            foreach ($relatedError as $relatedRelatedError)
                            {
                                if ($relatedRelatedError != '')
                                {
                                    $this->usageError($relatedRelatedError);
                                }
                            }
                        }
                        elseif ($relatedError != '')
                        {
                            $this->usageError($relatedError);
                        }
                    }
                }
                elseif ($errorOrRelatedError != '')
                {
                    $this->usageError($errorOrRelatedError);
                }
            }
        }
    }
}
?>