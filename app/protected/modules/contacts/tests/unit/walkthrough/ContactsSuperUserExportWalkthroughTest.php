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
     * Export module walkthrough tests.
     */
    class ContactsSuperUserExportWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected static $asynchronousThreshold;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            self::$asynchronousThreshold = ExportModule::$asynchronousThreshold;
            ExportModule::$asynchronousThreshold = 3;
        }

        public static function tearDownAfterClass()
        {
            ExportModule::$asynchronousThreshold = self::$asynchronousThreshold;
            parent::tearDownAfterClass();
        }

        /**
         * Walkthrough test for synchronous download
         */
        public function testDownloadDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);

            $contacts = array();
            for ($i = 0; $i < 2; $i++)
            {
                $contacts[] = ContactTestHelper::createContactWithAccountByNameForOwner('superContact' . $i, $super, $account);
            }

            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->setGetArray(array(
                'Contact_page' => '1',
                'export' => '',
                'ajax' => '',
                'selectAll' => '',
                'selectedIds' => '')
            );
            $response = $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/export');
            $this->assertTrue(strstr($response, 'contacts/default/index') !== false);

            $this->setGetArray(array(
                'ContactsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'fullName'                => 'superContact',
                    'officePhone'             => ''
                ),
                'multiselect_ContactsSearchForm_anyMixedAttributesScope' => 'All',
                'Contact_page'   => '1',
                'export'         => '',
                'ajax'           => '',
                'selectAll' => '1',
                'selectedIds' => '')
            );
            $response = $this->runControllerWithExitExceptionAndGetContent('contacts/default/export');
            $this->assertEquals('Testing download.', $response);

            $this->setGetArray(array(
                'ContactsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'fullName'                => 'superContact',
                    'officePhone'             => ''
                ),
                'multiselect_ContactsSearchForm_anyMixedAttributesScope' => 'All',
                'Contact_page'   => '1',
                'export'         => '',
                'ajax'           => '',
                'selectAll' => '',
                'selectedIds' => "{$contacts[0]->id}, {$contacts[1]->id}")
            );
            $response = $this->runControllerWithExitExceptionAndGetContent('contacts/default/export');
            $this->assertEquals('Testing download.', $response);

            // No mathces
            $this->setGetArray(array(
                'ContactsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'fullName'                => 'missingName',
                    'officePhone'             => ''
                ),
                'multiselect_ContactsSearchForm_anyMixedAttributesScope' => 'All',
                'Contact_page' => '1',
                'export'       => '',
                'ajax'         => '',
                'selectAll' => '1',
                'selectedIds' => '')
            );
            $response = $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/export');
            $this->assertTrue(strstr($response, 'contacts/default/index') !== false);
        }

        /**
        * Walkthrough test for synchronous download
        */
        public function testAsynchronousDownloadDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            $notificationsBeforeCount        = count(Notification::getAll());
            $notificationMessagesBeforeCount = count(NotificationMessage::getAll());

            $contacts = Contact::getAll();
            if (count($contacts))
            {
                foreach ($contacts as $contact)
                {
                    $contact->delete();
                }
            }
            $contacts = array();
            for ($i = 0; $i <= (ExportModule::$asynchronousThreshold + 1); $i++)
            {
                $contacts[] = ContactTestHelper::createContactWithAccountByNameForOwner('superContact' . $i, $super, $account);
            }

            $this->setGetArray(array(
                'Contact_page' => '1',
                'export' => '',
                'ajax' => '',
                'selectAll' => '1',
                'selectedIds' => '')
            );
            $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/export');

            // Start background job
            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItems = ExportItem::getAll();
            $this->assertEquals(1, count($exportItems));
            $fileModel = $exportItems[0]->exportFileModel;
            $this->assertEquals(1, $exportItems[0]->isCompleted);
            $this->assertEquals('csv', $exportItems[0]->exportFileType);
            $this->assertEquals('contacts', $exportItems[0]->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            $this->assertEquals($notificationsBeforeCount + 1, count(Notification::getAll()));
            $this->assertEquals($notificationMessagesBeforeCount + 1, count(NotificationMessage::getAll()));

            // Check export job, when many ids are selected.
            // This will probably never happen, but we need test for this case too.
            $notificationsBeforeCount        = count(Notification::getAll());
            $notificationMessagesBeforeCount = count(NotificationMessage::getAll());

            // Now test case when multiple ids are selected
            $exportItems = ExportItem::getAll();
            if (count($exportItems))
            {
                foreach ($exportItems as $exportItem)
                {
                    $exportItem->delete();
                }
            }

            $selectedIds = "";
            foreach ($contacts as $contact)
            {
                $selectedIds .= $contact->id . ","; // Not Coding Standard
            }
            $this->setGetArray(array(
                'ContactsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'fullName'                => '',
                    'officePhone'             => ''
                ),
                'multiselect_ContactsSearchForm_anyMixedAttributesScope' => 'All',
                'Contact_page'   => '1',
                'export'         => '',
                'ajax'           => '',
                'selectAll' => '',
                'selectedIds' => "$selectedIds")
            );

            $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/export');
            // Start background job
            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItems = ExportItem::getAll();
            $this->assertEquals(1, count($exportItems));
            $fileModel = $exportItems[0]->exportFileModel;
            $this->assertEquals(1, $exportItems[0]->isCompleted);
            $this->assertEquals('csv', $exportItems[0]->exportFileType);
            $this->assertEquals('contacts', $exportItems[0]->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            $this->assertEquals($notificationsBeforeCount + 1, count(Notification::getAll()));
            $this->assertEquals($notificationMessagesBeforeCount + 1, count(NotificationMessage::getAll()));
        }
    }
?>