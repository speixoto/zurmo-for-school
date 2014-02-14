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
    class SpecialMergeTagsAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testIsSpecialMergeTag()
        {
            $this->assertFalse(SpecialMergeTagsAdapter::isSpecialMergeTag('attribute', null));
            $this->assertFalse(SpecialMergeTagsAdapter::isSpecialMergeTag('attribute', 'something'));
            $this->assertFalse(SpecialMergeTagsAdapter::isSpecialMergeTag('modelURl', null));
            $this->assertFalse(SpecialMergeTagsAdapter::isSpecialMergeTag('modelURl', 'something'));
            $this->assertTrue(SpecialMergeTagsAdapter::isSpecialMergeTag('modelUrl', null));
            $this->assertFalse(SpecialMergeTagsAdapter::isSpecialMergeTag('modelUrl', 'something'));
        }

        /**
         * @depends testIsSpecialMergeTag
         */
        public function testResolveModelUrl()
        {
            $contact = ContactTestHelper::createContactByNameForOwner('contact 01', Yii::app()->user->userModel);
            $resolvedModelUrl   = SpecialMergeTagsAdapter::resolve('modelUrl', $contact);
            $this->assertNotNull($resolvedModelUrl);
            $expectedSuffix                 = '/contacts/default/details?id=' . $contact->id;
            $this->assertTrue(strpos($resolvedModelUrl, $expectedSuffix) !== false);
        }

        /**
         * @depends testResolveModelUrl
         */
        public function testResolveBaseUrl()
        {
            $resolvedBaseUrl    = SpecialMergeTagsAdapter::resolve('baseUrl', null);
            $this->assertNotNull($resolvedBaseUrl);
            $this->assertTrue(strpos($resolvedBaseUrl, 'localhost') === 0);
        }

        /**
         * @depends testResolveBaseUrl
         */
        public function testResolveApplicationName()
        {
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'applicationName', 'Demo App');
            $resolvedApplicationName    = SpecialMergeTagsAdapter::resolve('applicationName', null);
            $expectedApplicationName    = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'applicationName');
            $this->assertNotNull($resolvedApplicationName);
            $this->assertEquals($expectedApplicationName, $resolvedApplicationName);
        }

        /**
         * @depends testResolveApplicationName
         */
        public function testResolveCurrentYear()
        {
            $resolvedCurrentYear    = SpecialMergeTagsAdapter::resolve('currentYear', null);
            $expectedCurrentYear    = date('Y');
            $this->assertNotNull($resolvedCurrentYear);
            $this->assertEquals($expectedCurrentYear, $resolvedCurrentYear);
        }

        /**
         * @depends testResolveCurrentYear
         */
        public function testResolveLastYear()
        {
            $resolvedLastYear        = SpecialMergeTagsAdapter::resolve('lastYear', null);
            $expectedLastYear       = date('Y') - 1;
            $this->assertNotNull($resolvedLastYear);
            $this->assertEquals($expectedLastYear, $resolvedLastYear);
        }

        public function testResolveAvatars()
        {
            $super = User::getByUsername('super');
            $contact = new Contact();
            $contact->owner = $super;
            $resolvedAvatarImage = SpecialMergeTagsAdapter::resolve('ownersAvatarSmall', $contact);
            $expectedAvatarImage = '<img class="gravatar" width="32" height="32" src="http://www.gravatar.com/avatar/?s=32&amp;r=g&amp;d=mm" alt="Clark Kent" />';
            $this->assertEquals($expectedAvatarImage, $resolvedAvatarImage);

            $resolvedAvatarImage = SpecialMergeTagsAdapter::resolve('ownersAvatarMedium', $contact);
            $expectedAvatarImage = '<img class="gravatar" width="64" height="64" src="http://www.gravatar.com/avatar/?s=32&amp;r=g&amp;d=mm" alt="Clark Kent" />';
            $this->assertEquals($expectedAvatarImage, $resolvedAvatarImage);

            $resolvedAvatarImage = SpecialMergeTagsAdapter::resolve('ownersAvatarLarge', $contact);
            $expectedAvatarImage = '<img class="gravatar" width="128" height="128" src="http://www.gravatar.com/avatar/?s=32&amp;r=g&amp;d=mm" alt="Clark Kent" />';
            $this->assertEquals($expectedAvatarImage, $resolvedAvatarImage);
        }

        /**
         * @depends testResolveAvatars
         */
        public function testResolveOwnersEmailSignature()
        {
            $super = User::getByUsername('super');
            $emailSignature = new EmailSignature();
            $emailSignature->htmlContent = 'my email signature';
            $super->emailSignatures->add($emailSignature);
            $super->save();
            $super->forget();
            $super = User::getByUsername('super');
            $contact = new Contact();
            $contact->owner = $super;
            $resolvedEmailSignature = SpecialMergeTagsAdapter::resolve('ownersEmailSignature', $contact);
            $expectedEmailSignature = 'my email signature';
            $this->assertEquals($expectedEmailSignature, $resolvedEmailSignature);
        }
    }
?>