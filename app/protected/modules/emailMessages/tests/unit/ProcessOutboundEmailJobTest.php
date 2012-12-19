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

    class ProcessOutboundEmailJobTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
        }

        public function testRun()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');

            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $outboxFolder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX);
            $sentFolder   = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_SENT);

            $emailMessage = EmailMessageTestHelper::createDraftSystemEmail('My Email Message', $super);
            $emailMessage->folder       = $outboxFolder;
            $saved                      = $emailMessage->save();
            $this->assertTrue($saved);
            $emailMessageId            = $emailMessage->id;
            $emailMessage->forget();
            unset($emailMessage);

            $emailMessage2 = EmailMessageTestHelper::createDraftSystemEmail('My Email Message', $super);
            $emailMessage2->folder      = $outboxFolder;
            $saved                      = $emailMessage2->save();
            $this->assertTrue($saved);
            $emailMessage2Id            = $emailMessage2->id;
            $emailMessage2->forget();
            unset($emailMessage2);
            $this->assertEquals(2, count(EmailMessage::getAll()));

            $job = new ProcessOutboundEmailJob();
            $this->assertTrue($job->run());
            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(2, count($emailMessages));

            $emailMessage   = EmailMessage::getById($emailMessageId);
            $this->assertEquals($sentFolder, $emailMessage->folder);
            $emailMessage2  = EmailMessage::getById($emailMessage2Id);
            $this->assertEquals($sentFolder, $emailMessage2->folder);
        }
    }
?>