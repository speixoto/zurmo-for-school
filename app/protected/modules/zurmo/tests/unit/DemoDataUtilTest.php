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
     * This test is in the application instead of the framework so it can be tested when the database is frozen or
     * unfrozen.
     */
    class DemoDataUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            //Load default data first. This is required for the demo data to load correctly.
            $messageLogger   = new MessageLogger();
            DefaultDataUtil::load($messageLogger);
        }

        public function testLoad()
        {
            $this->assertEquals(2, Group::getCount());
            $this->assertEquals(0, Role::getCount());
            $this->assertEquals(0, Account::getCount());
            $this->assertEquals(0, Contact::getCount());
            $this->assertEquals(0, Opportunity::getCount());
            $this->assertEquals(0, Meeting::getCount());
            $this->assertEquals(0, Note::getCount());
            $this->assertEquals(0, Task::getCount());
            $this->assertEquals(1, User::getCount());
            $this->assertEquals(0, ProductCatalog::getCount());
            $this->assertEquals(0, ProductCategory::getCount());
            $this->assertEquals(0, ProductTemplate::getCount());
            $this->assertEquals(0, Product::getCount());
            $messageLogger   = new MessageLogger();
            DemoDataUtil::unsetLoadedModules();
            DemoDataUtil::load($messageLogger, 3);
            $this->assertEquals(8, Group::getCount());
            $this->assertEquals(3, Role::getCount());
            $this->assertEquals(3, Account::getCount());
            $this->assertEquals(16, Contact::getCount());
            $this->assertEquals(6,  Opportunity::getCount());
            $this->assertEquals(18, Meeting::getCount());
            $this->assertEquals(12, Note::getCount());
            $this->assertEquals(9,  Task::getCount());
            $this->assertEquals(10,  User::getCount());
            $this->assertEquals(1, ProductCatalog::getCount());
            $this->assertEquals(6, ProductCategory::getCount());
            $this->assertEquals(32, ProductTemplate::getCount());
            $this->assertEquals(59, Product::getCount());
        }
    }
?>