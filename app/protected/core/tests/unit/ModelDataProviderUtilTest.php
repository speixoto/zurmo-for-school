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
     * Models used:
     * I extends H.  I has_one G - used to test standard, casted up and relation ordering.
     *
     * TestCustomFieldsModel - used to test customFields ordering.
     *
     */
    class ModelDataProviderUtilTest extends BaseTest
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
            $builder           = new ModelWhereAndJoinBuilder($adapter, $joinTablesAdapter);
            $tableAliasName    = $builder->resolveShouldAddFromTable();
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
            $builder           = new ModelWhereAndJoinBuilder($adapter, $joinTablesAdapter);
            $tableAliasName    = $builder->resolveShouldAddFromTable();
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
            $builder           = new ModelWhereAndJoinBuilder($adapter, $joinTablesAdapter);
            $tableAliasName    = $builder->resolveShouldAddFromTable();
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
            $builder           = new ModelWhereAndJoinBuilder($adapter, $joinTablesAdapter);
            $tableAliasName    = $builder->resolveShouldAddFromTable();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        /**
         * @depends testResolveShouldAddFromTableWithOwnedCustomFieldAttribute
         */
        public function testResolveSortAttributeColumnName()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();

            //Test a standard non-relation attribute on I
            $joinTablesAdapter                   = new RedBeanModelJoinTablesQueryAdapter('I');
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter('I', 'iMember');
            $sort                                = ModelDataProviderUtil::
                                                   resolveSortAttributeColumnName(
                                                   $modelAttributeToDataProviderAdapter, $joinTablesAdapter);
            $this->assertEquals("{$quote}i{$quote}.{$quote}imember{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Test a standard casted up attribute on H from I
            $joinTablesAdapter                   = new RedBeanModelJoinTablesQueryAdapter('I');
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter('I', 'name');
            $sort                                = ModelDataProviderUtil::
                                                   resolveSortAttributeColumnName(
                                                   $modelAttributeToDataProviderAdapter, $joinTablesAdapter);
            $this->assertEquals("{$quote}h{$quote}.{$quote}name{$quote}", $sort);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Test a relation attribute G->g from H (HAS_ONE)
            $joinTablesAdapter                   = new RedBeanModelJoinTablesQueryAdapter('H');
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter('H', 'castUpHasOne', 'g');
            $sort                                = ModelDataProviderUtil::
                                                   resolveSortAttributeColumnName(
                                                   $modelAttributeToDataProviderAdapter, $joinTablesAdapter);
            $this->assertEquals("{$quote}g{$quote}.{$quote}g{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('g', $leftTables[0]['tableName']);

            //Test a relation attribute G->g where casted up from I (HAS_ONE)
            $joinTablesAdapter                   = new RedBeanModelJoinTablesQueryAdapter('I');
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter('I', 'castUpHasOne', 'g');
            $sort                                = ModelDataProviderUtil::
                                                   resolveSortAttributeColumnName(
                                                   $modelAttributeToDataProviderAdapter, $joinTablesAdapter);
            $this->assertEquals("{$quote}g{$quote}.{$quote}g{$quote}", $sort);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $fromTables = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('h', $fromTables[0]['tableName']);
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('g', $leftTables[0]['tableName']);

            //Test a customField like TestCustomFieldsModel->industry
            $joinTablesAdapter                   = new RedBeanModelJoinTablesQueryAdapter('TestCustomFieldsModel');
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter('TestCustomFieldsModel', 'industry', 'value');
            $sort                                = ModelDataProviderUtil::
                                                   resolveSortAttributeColumnName(
                                                   $modelAttributeToDataProviderAdapter, $joinTablesAdapter);
            $this->assertEquals("{$quote}customfield{$quote}.{$quote}value{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('customfield', $leftTables[0]['tableName']);

            //Test I HAS_MANY K -> kMember (Testing HAS_MANY)
            $joinTablesAdapter                   = new RedBeanModelJoinTablesQueryAdapter('I');
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter('I', 'ks', 'kMember');
            $sort                                = ModelDataProviderUtil::
                resolveSortAttributeColumnName(
                $modelAttributeToDataProviderAdapter, $joinTablesAdapter);
            $this->assertEquals("{$quote}k{$quote}.{$quote}kmember{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('k', $leftTables[0]['tableName']);

            //Test I MANY_MANY Z -> z (Testing MANY_MANY)
            $joinTablesAdapter                   = new RedBeanModelJoinTablesQueryAdapter('I');
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter('I', 'manyManyRelation', 'z');
            $sort                                = ModelDataProviderUtil::
                resolveSortAttributeColumnName(
                $modelAttributeToDataProviderAdapter, $joinTablesAdapter);
            $this->assertEquals("{$quote}z{$quote}.{$quote}z{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('i_z', $leftTables[0]['tableName']);
            $this->assertEquals('z',   $leftTables[1]['tableName']);
        }

        /**
         * @depends testResolveSortAttributeColumnName
         */
        public function testResolveSortWhenThereAreTableAliases()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();
            //Test a customField like TestCustomFieldsModel->industry
            $joinTablesAdapter                   = new RedBeanModelJoinTablesQueryAdapter('TestCustomFieldsModel');
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter('TestCustomFieldsModel', 'industry', 'value');
            $sort                                = ModelDataProviderUtil::
                resolveSortAttributeColumnName(
                $modelAttributeToDataProviderAdapter, $joinTablesAdapter);
            $this->assertEquals("{$quote}customfield{$quote}.{$quote}value{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('customfield', $leftTables[0]['tableName']);

            //Now add a second sort on a different CustomField
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter('TestCustomFieldsModel', 'market', 'value');
            $sort                                = ModelDataProviderUtil::
                resolveSortAttributeColumnName(
                $modelAttributeToDataProviderAdapter, $joinTablesAdapter);
            $this->assertEquals("{$quote}customfield1{$quote}.{$quote}value{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('customfield', $leftTables[1]['tableName']);
            $this->assertEquals('customfield1', $leftTables[1]['tableAliasName']);
        }
    }
?>
