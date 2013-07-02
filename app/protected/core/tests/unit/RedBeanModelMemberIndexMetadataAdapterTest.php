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

    class RedBeanModelMemberIndexMetadataAdapterTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testResolveIndexNameNonUnique()
        {
            $indexName          = 'ThisIsAnIndex';
            $resolvedIndexName  = RedBeanModelMemberIndexMetadataAdapter::resolveIndexName($indexName);
            $this->assertNotEmpty($resolvedIndexName);
            $this->assertNotEquals($indexName, $resolvedIndexName);
            $this->assertEquals('this_is_an_index_Index', $resolvedIndexName);
        }

        /**
         * @depends testResolveIndexNameNonUnique
         */
        public function testResolveIndexNameUnique()
        {
            $indexName          = 'ThisIsAUniqueIndex';
            $resolvedIndexName  = RedBeanModelMemberIndexMetadataAdapter::resolveIndexName($indexName, true);
            $this->assertNotEmpty($resolvedIndexName);
            $this->assertNotEquals($indexName, $resolvedIndexName);
            $this->assertEquals('unique_this_is_a_unique_index_Index', $resolvedIndexName);
        }

        /**
         * @depends testResolveIndexNameUnique
         */
        public function testResolveWithEmptyMetadata()
        {
            $indexName      = "ThisIsAnIndex";
            $indexMetadata  = array();
            $resolved       = RedBeanModelMemberIndexMetadataAdapter::resolve($indexName, $indexMetadata);
            $this->assertFalse($resolved);
        }

        /**
         * @depends testResolveWithEmptyMetadata
         */
        public function testResolveWithEmptyMembers()
        {
            $indexName      = "ThisIsAnIndex";
            $indexMetadata  = array('columns' => array());
            $resolved       = RedBeanModelMemberIndexMetadataAdapter::resolve($indexName, $indexMetadata);
            $this->assertFalse($resolved);
        }

        /**
         * @depends testResolveWithEmptyMembers
         */
        public function testResolveWithNonArrayMembers()
        {
            $indexName      = "ThisIsAnIndex";
            $indexMetadata  = array('columns' => 1234);
            $resolved       = RedBeanModelMemberIndexMetadataAdapter::resolve($indexName, $indexMetadata);
            $this->assertFalse($resolved);
        }

        /**
         * @depends testResolveWithNonArrayMembers
         */
        public function testResolveWithUniqueNotSet()
        {
            $indexName      = "ThisIsAnIndex";
            $indexMetadata  = array('members' => array('memberOne'));
            $resolved       = RedBeanModelMemberIndexMetadataAdapter::resolve($indexName, $indexMetadata);
            $this->assertTrue($resolved);
            $this->assertEquals('this_is_an_index_Index', $indexName);
            $this->assertNotEmpty($indexMetadata);
            $this->assertArrayHasKey('columns', $indexMetadata);
            $this->assertCount(1, $indexMetadata['columns']);
            $this->assertEquals('memberone', $indexMetadata['columns'][0]);
            $this->assertArrayHasKey('unique', $indexMetadata);
            $this->assertFalse($indexMetadata['unique']);
        }

        /**
         * @depends testResolveWithUniqueNotSet
         */
        public function testResolve()
        {
            $indexName      = "ThisIsAUniqueIndex";
            $indexMetadata  = array('members' => array('memberOne', 'memberTwo'), 'unique' => true);
            $resolved       = RedBeanModelMemberIndexMetadataAdapter::resolve($indexName, $indexMetadata);
            $this->assertTrue($resolved);
            $this->assertEquals('unique_this_is_a_unique_index_Index', $indexName);
            $this->assertNotEmpty($indexMetadata);
            $this->assertArrayHasKey('columns', $indexMetadata);
            $this->assertCount(2, $indexMetadata['columns']);
            $this->assertEquals('memberone', $indexMetadata['columns'][0]);
            $this->assertEquals('membertwo', $indexMetadata['columns'][1]);
            $this->assertArrayHasKey('unique', $indexMetadata);
            $this->assertTrue($indexMetadata['unique']);
        }
    }
?>