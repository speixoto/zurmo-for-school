<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Walkthrough for the super user of mixed models search actions
     */
    class ZurmoMixedModelsSearchSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
            AllPermissionsOptimizationUtil::rebuild();
            ContactsModule::loadStartingData();

            //Setup test data owned by the super user.
            AccountTestHelper::createAccountByNameForOwner('Dinamite', $super);
            AccountTestHelper::createAccountByNameForOwner('dino', $super);
        }

        public function testSuperUserGlobalList()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test if actionGlobalList return a list view for all modules
            $this->setGetArray(array('MixedModelsSearchForm' => array('anyMixedAttributesScope' => array('All'),
                                                                      'term'                    => 't'
                )));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/globallist');
            $this->assertContains('<div class="cgrid-view type-accounts" id="list-view-accounts">', $content);
            $this->assertContains('<div class="cgrid-view type-contacts" id="list-view-contacts">', $content);
            $this->assertContains('<div class="cgrid-view type-leads" id="list-view-leads">', $content);
            $this->assertContains('<div class="cgrid-view type-opportunities" id="list-view-opportunities">', $content);
            //Even if there are results it should return a cgridview with no text
            $this->assertNotContains('No results found', $content);

            //Test if actionGlobalList only show the module requested
            ContactTestHelper::createContactByNameForOwner('tim', $super);
            $_SERVER['HTTP_X_REQUESTED_WITH']='XMLHttpRequest';
            $this->setGetArray(array('MixedModelsSearchForm' => array('term' => 'd'),
                                     'ajax'                  => 'list-view-accounts')
                    );
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/globallist');
            $this->assertContains('id="AccountsForMixedModelsSearchListView"', $content);
            $this->assertNotContains('id="ContactsForMixedModelsSearchListView"', $content);
            $this->assertNotContains('id="LeadsForMixedModelsSearchListView"', $content);
            $this->assertNotContains('id="OpportunitiesForMixedModelsSearchListView">', $content);
            //TODO: Should test if the accounts created is shown
        }
    }
?>