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
     * Test out Zurmo specific things on the ModelDataProviderUtilTest like using Accounts or Contacts and how they
     * cast up to Item.
      */
    class ZurmoModelDataProviderUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testResolveShouldAddFromTableWithAttributeCastedUpSeveralLevels()
        {
            $adapter           = new RedBeanModelAttributeToDataProviderAdapter('Account', 'createdDateTime');
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder           = new ModelWhereAndJoinBuilder($adapter, $joinTablesAdapter, true);
            $tableAliasName    = $builder->resolveJoins();
            $fromTables        = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('ownedsecurableitem', $fromTables[0]['tableName']);
            $this->assertEquals('securableitem',      $fromTables[1]['tableName']);
            $this->assertEquals('item',               $fromTables[2]['tableName']);
        }

        /**
         * @depends testResolveShouldAddFromTableWithAttributeCastedUpSeveralLevels
         */
        public function testResolveShouldAddFromTableWithUserModelAndPersonAttribute()
        {
            $adapter           = new RedBeanModelAttributeToDataProviderAdapter('User', 'firstName');
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('User');
            $builder           = new ModelWhereAndJoinBuilder($adapter, $joinTablesAdapter, true);
            $tableAliasName    = $builder->resolveJoins();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        /**
         * @depends testResolveShouldAddFromTableWithUserModelAndPersonAttribute
         */
        public function testResolveShouldAddFromTableWithAttributeOnModelSameTable()
        {
            $adapter           = new RedBeanModelAttributeToDataProviderAdapter('Account', 'name');
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder           = new ModelWhereAndJoinBuilder($adapter, $joinTablesAdapter, true);
            $tableAliasName    = $builder->resolveJoins();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        /**
         * @depends testResolveShouldAddFromTableWithAttributeOnModelSameTable
         */
        public function testResolveShouldAddFromTableWithOwnedCustomFieldAttribute()
        {
            $adapter           = new RedBeanModelAttributeToDataProviderAdapter('Account', 'industry');
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder           = new ModelWhereAndJoinBuilder($adapter, $joinTablesAdapter, true);
            $tableAliasName    = $builder->resolveJoins();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }
    }
?>