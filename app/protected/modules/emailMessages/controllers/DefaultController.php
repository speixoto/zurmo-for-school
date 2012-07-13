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
     * Controller for managing configuration actions for email messages
     */
    class EmailMessagesDefaultController extends ZurmoBaseController
    {
        public function filters()
        {
            $moduleClassName = get_class($this->getModule());
            return array(
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH . ' + configurationEdit',
                    'moduleClassName' => $moduleClassName,
                    'rightName'       => EmailMessagesModule::RIGHT_ACCESS_CONFIGURATION,
               ),
            );
        }

        public function actionDetails($id, $redirectUrl = null)
        {
            $emailMessage          = EmailMessage::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailMessage);
            $detailsView           = new EmailMessageDetailsView($this->getId(), $this->getModule()->getId(), $emailMessage);
            $view              = new EmailMessagesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsView));
            echo $view->render();
        }

        public function actionConfigurationEdit()
        {
            $view = new ConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                                  makeStandardViewForCurrentUser($this, new EmailConfigurationListView()));
            echo $view->render();
        }

        public function actionConfigurationEditOutbound()
        {
            $configurationForm = EmailSmtpConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    EmailSmtpConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'Email configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $editView = new EmailSmtpConfigurationEditAndDetailsView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        public function actionConfigurationEditArchiving()
        {
            $configurationForm = EmailArchivingConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    EmailArchivingConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'Email configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $editView = new EmailArchivingConfigurationEditAndDetailsView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        /**
         * Assumes before calling this, the outbound settings have been validated in the form.
         * Todo: When new user interface is complete, this will be re-worked to be on page instead of modal.
         */
        public function actionSendTestMessage()
        {
            $configurationForm = EmailSmtpConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->aTestToAddress != null)
                {
                    $emailHelper = new EmailHelper;
                    $emailHelper->outboundHost     = $configurationForm->host;
                    $emailHelper->outboundPort     = $configurationForm->port;
                    $emailHelper->outboundUsername = $configurationForm->username;
                    $emailHelper->outboundPassword = $configurationForm->password;
                    $emailHelper->outboundSecurity = $configurationForm->security;
                    $userToSendMessagesFrom        = User::getById((int)$configurationForm->userIdOfUserToSendNotificationsAs);

                    $emailMessage              = new EmailMessage();
                    $emailMessage->owner       = Yii::app()->user->userModel;
                    $emailMessage->subject     = Yii::t('Default', 'A test email from Zurmo');
                    $emailContent              = new EmailMessageContent();
                    $emailContent->textContent = Yii::t('Default', 'A test text message from Zurmo');
                    $emailContent->htmlContent = Yii::t('Default', 'A test text message from Zurmo');
                    $emailMessage->content     = $emailContent;
                    $sender                    = new EmailMessageSender();
                    $sender->fromAddress       = $emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
                    $sender->fromName          = strval($userToSendMessagesFrom);
                    $emailMessage->sender      = $sender;
                    $recipient                 = new EmailMessageRecipient();
                    $recipient->toAddress      = $configurationForm->aTestToAddress;
                    $recipient->toName         = 'Test Recipient';
                    $recipient->type           = EmailMessageRecipient::TYPE_TO;
                    $emailMessage->recipients->add($recipient);
                    $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                    $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
                    $validated                 = $emailMessage->validate();
                    if (!$validated)
                    {
                        throw new NotSupportedException();
                    }
                    $messageContent  = null;
                    $emailHelper->sendImmediately($emailMessage);
                    if (!$emailMessage->hasSendError())
                    {
                        $messageContent .= Yii::t('Default', 'Message successfully sent') . "\n";
                    }
                    else
                    {
                        $messageContent .= Yii::t('Default', 'Message failed to send') . "\n";
                        $messageContent .= $emailMessage->error     . "\n";
                    }
                }
                else
                {
                    $messageContent = Yii::t('Default', 'A test email address must be entered before you can send a test email.') . "\n";
                }
                Yii::app()->getClientScript()->setToAjaxMode();
                $messageView = new TestEmailMessageView($messageContent);
                $view = new ModalView($this, $messageView);
                echo $view->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
        * Assumes before calling this, the inbound settings have been validated in the form.
        */
        public function actionTestImapConnection()
        {
            $configurationForm = EmailArchivingConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);

                $imap = new ZurmoImap();

                $imap->imapHost     = $configurationForm->imapHost;
                $imap->imapUsername = $configurationForm->imapUsername;
                $imap->imapPassword = $configurationForm->imapPassword;
                $imap->imapPort     = $configurationForm->imapPort;
                $imap->imapSSL      = $configurationForm->imapSSL;
                $imap->imapFolder   = $configurationForm->imapFolder;

                try
                {
                    $connect = $imap->connect();
                }
                catch (Exception $e)
                {
                    $connect = false;
                    $messageContent = Yii::t('Default', 'Could not connect to IMAP server.') . "\n";
                }

                if (isset($connect) && $connect == true)
                {
                    $messageContent = Yii::t('Default', 'Successfully connected to IMAP server.') . "\n";
                }
                else
                {
                    $messageContent = Yii::t('Default', 'Could not connect to IMAP server.') . "\n";
                }

                Yii::app()->getClientScript()->setToAjaxMode();
                $messageView = new TestImapConnectionMessageView($messageContent);
                $view = new ModalView($this,
                                      $messageView,
                                      'modalContainer',
                                      Yii::t('Default', 'Test Message Results')
                );
                echo $view->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>