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

    class ProcessInboundEmailJobTest extends BaseTest
    {
        public static $userMailer;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('steve');

            Yii::app()->imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
            Yii::app()->imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
            Yii::app()->imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
            Yii::app()->imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
            Yii::app()->imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
            Yii::app()->imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];
            Yii::app()->imap->setInboundSettings();
            Yii::app()->imap->init();

            Yii::app()->emailHelper->outboundHost     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundHost'];
            Yii::app()->emailHelper->outboundPort     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPort'];
            Yii::app()->emailHelper->outboundUsername = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundUsername'];
            Yii::app()->emailHelper->outboundPassword = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPassword'];
            Yii::app()->emailHelper->sendEmailThroughTransport = true;
            Yii::app()->emailHelper->setOutboundSettings();
            Yii::app()->emailHelper->init();


            $userSmtpMailer = new EmailHelperForTesting();

            $userSmtpMailer->outboundHost     = Yii::app()->params['emailTestAccounts']['userSmtpSettings']['outboundHost'];
            $userSmtpMailer->outboundPort     = Yii::app()->params['emailTestAccounts']['userSmtpSettings']['outboundPort'];
            $userSmtpMailer->outboundUsername = Yii::app()->params['emailTestAccounts']['userSmtpSettings']['outboundUsername'];
            $userSmtpMailer->outboundPassword = Yii::app()->params['emailTestAccounts']['userSmtpSettings']['outboundPassword'];
            $userSmtpMailer->setOutboundSettings();
            $userSmtpMailer->init();
            $userSmtpMailer->sendEmailThroughTransport = true;
            self::$userMailer = $userSmtpMailer;
        }

        public function setup(){
            $user = User::getByUsername('steve');
            $user->primaryEmail->emailAddress = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'];
            $this->assertTrue($user->save());
        }

        public function testRun()
        {
            //Yii::app()->imap->connect();
            $super                      = User::getByUsername('super');
            //Yii::app()->user->userModel = $super;
            //$emailMessage = EmailMessageTestHelper::createDraftSystemEmail('a test email 3', $super);
            //self::$userMailer->sendImmediately($emailMessage);
            $emailMessage = EmailMessageTestHelper::createDraftSystemEmail('a test email 4', $super);
            Yii::app()->emailHelper->sendImmediately($emailMessage);
            exit;
            $user = User::getByUsername('steve');
            $user->primaryEmail->emailAddress = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'];
            $user->save;
            //Yii::app()->imap->expungeMessages();

            $job = new ProcessInboundEmailJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, count(EmailMessage::getAll()));


exit;
        }
    }
?>