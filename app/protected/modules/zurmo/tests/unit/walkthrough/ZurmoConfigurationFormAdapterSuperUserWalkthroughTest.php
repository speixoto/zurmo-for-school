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
     * Walkthrough for the super user of global configuration
     */
    class ZurmoConfigurationFormAdapterSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSuperUserEditConfigurationForm()
        {
            //checking with blank values for required fields
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setPostArray(array('save'                    => 'Save',
                                      'ZurmoConfigurationForm'  => array(
                                            'applicationName'                               => '',
                                            'dashboardListPage'                             => '',
                                            'gamificationModalNotificationsEnabled'         => '1',
                                            'listPageSize'                                  => '',
                                            'modalListPageSize'                             => '',
                                            'subListPageSize'                               => '',
                                            'defaultTestToEmailAddress'                     => '',
                                            'autoresponderOrCampaignBatchSize'              => '',
                                            'autoresponderOrCampaignFooterPlainText'        => '',
                                            'autoresponderOrCampaignFooterRichText'         => '',
                                            'timeZone'                                      => 'America/Chicago'),
                                      )
                               );
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/configurationEdit');
            $this->assertFalse(strpos($content, 'Dashboard portlet list page size') === false);
            $this->assertFalse(strpos($content, 'List page size cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Popup list page size cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Sublist page size cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Autoresponder/Campaign batch size cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Autoresponder/Campaign Footer(Plain Text) cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Autoresponder/Campaign Footer(Rich Text) cannot be blank.') === false);

            //checking with proper values for required fields
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setPostArray(array('save'                    => 'Save',
                                      'ZurmoConfigurationForm'  => array(
                                                'applicationName'                           => 'Demo Company Inc.',
                                                'dashboardListPage'                         => '5',
                                                'gamificationModalNotificationsEnabled'     => '0',
                                                'listPageSize'                              => '10',
                                                'modalListPageSize'                         => '5',
                                                'subListPageSize'                           => '5',
                                                'defaultFromEmailAddress'                   => 'notification@zurmoalerts.com',
                                                'defaultTestToEmailAddress'                 => 'testJobEmail@zurmoalerts.com',
                                                'autoresponderOrCampaignBatchSize'          => '10',
                                                'autoresponderOrCampaignFooterPlainText'    => 'abc',
                                                'autoresponderOrCampaignFooterRichText'     => 'def',
                                                'timeZone'                                  => 'America/Chicago'),
                                      )
                               );
            $this->runControllerWithRedirectExceptionAndGetContent('zurmo/default/configurationEdit');
            $this->assertEquals('Global configuration saved successfully.', Yii::app()->user->getFlash('notification'));
        }
    }
?>