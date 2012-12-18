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

    class DisplayAttributesReportQueryBuilderTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }
/**
        public function testNonRelatedNonDerivedAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'phone';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Add a second attribute on the same model
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute2                     = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'integer';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testAttributeOnOwnedModelWithNoBeanSkips()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is on an owned model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'primaryAddress___street1';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testAttributeOnOwnedModelWithBeanSkip()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is on an owned model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'dropDown';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedCastedUpAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Two display attributes that are casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedCastedUpAttributeThatIsAUserRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'owner___lastName';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Two display attributes that are casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'owner___lastName';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'modifiedByUser___lastName';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedAttributeNested()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute nested in a relation
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___phone';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem2{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());

            //Add a second attribute
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute2                     = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'integer';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent = "select {$q}reportmodeltestitem2{$q}.{$q}id{$q}, {$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testAttributeOnOwnedModelWithNoBeanSkipsThatIsNested()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is on an owned model through a relation
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem2');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasMany2___primaryAddress___street1';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedCastedUpAttributeThatIsAUserRelationWhenNested()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___owner___lastName';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem2{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());

            //Two display attributes that are casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___owner___lastName';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___modifiedByUser___lastName';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $this->assertEquals("select {$q}reportmodeltestitem2{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            //Add third display attribute on the base model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___owner___lastName';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___modifiedByUser___lastName';
            $displayAttribute3                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType = 'modifiedByUser___lastName';
            $content        = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent = "select {$q}reportmodeltestitem2{$q}.{$q}id{$q}, {$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }
**/

        public function testDisplayCalculationAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'Count';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select count({$q}reportmodeltestitem{$q}.{$q}id{$q}) col0 ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime__Minimum';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select min({$q}item{$q}.{$q}createddatetime{$q}) col0 ", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime__Maximum';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select max({$q}item{$q}.{$q}createddatetime{$q}) col0 ", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'integer__Average';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select avg({$q}reportmodeltestitem{$q}.{$q}integer{$q}) col0 ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'integer__Summation';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select sum({$q}reportmodeltestitem{$q}.{$q}integer{$q}) col0 ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime__Day';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select day({$q}item{$q}.{$q}createddatetime{$q}) col0 ", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDisplayCalculationMoreThanOneAttribute()
        {

        }

        public function testDisplayCalculationAttributesThatAreNested()
        {

        }
/**
        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasOneRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_ONE
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___createdDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany___createdDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasManyBelongsToRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'account___createdDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAManyManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is MANY_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany1___createdDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasOneRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with two on a relation that is HAS_ONE
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasMany___createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasManyBelongsToRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'account___createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'account___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAManyManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is MANY_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasMany1___createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany1___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasOneRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is HAS_ONE and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___createdDateTime';
            $displayAttribute3                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType = 'hasOne___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent                        = "{$q}item{$q}.{$q}modifieddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasManyRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is HAS_MANY and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany___createdDateTime';
            $displayAttribute3                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType = 'hasMany___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent                        = "{$q}item{$q}.{$q}modifieddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasManyBelongsToRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'account___createdDateTime';
            $displayAttribute3                              = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType = 'account___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} desc, " .
                                                     "{$q}item1{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAManyManyRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is MANY_MANY and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany1___createdDateTime';
            $displayAttribute3                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType = 'hasMany1___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent                        = "{$q}item{$q}.{$q}modifieddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenOneIsOnRelatedModelAndOneIsOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with 1 on relation and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'dropDown';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___dropDown';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                     "{$q}customfield1{$q}.{$q}value{$q} asc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenBothAreOnTheSameRelatedModelButDifferentRelations()
        {

            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on a related model, but the links are different
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___dropDown';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany___dropDown';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                     "{$q}customfield1{$q}.{$q}value{$q} asc";
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('reportmodeltestitem1', $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem1', $leftTablesAndAliases[3]['onTableAliasName']);
        }

        public function testTwoCustomFieldsWhenBothAreOnRelatedModelsThatAreDifferent()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on 2 different related models
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___dropDown';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne2___dropDownX';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                     "{$q}customfield1{$q}.{$q}value{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('reportmodeltestitem', $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem', $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('reportmodeltestitem8', $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem8', $leftTablesAndAliases[3]['onTableAliasName']);
        }

        public function testTwoCustomFieldsWhenBothAreOnTheSameRelatedModel()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on a related model, but 2 different dropdowns
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___dropDown';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___dropDown2';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                     "{$q}customfield1{$q}.{$q}value{$q} asc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDynamicallyDerivedAttributeOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //2 __User attributes on the same model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdByUser__User';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'modifiedByUser__User';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}person{$q}.{$q}lastname{$q} asc, " .
                                                     "{$q}person1{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('_user',   $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('_user',   $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('_user1',  $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('_user1',  $leftTablesAndAliases[3]['onTableAliasName']);

            //2 __User attributes on the same model, one is owned, so not originating both from Item
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdByUser__User';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'owner__User';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}person{$q}.{$q}lastname{$q} asc, " .
                                                     "{$q}person1{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('_user',               $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('_user',               $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('_user1',              $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',  $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('_user1',              $leftTablesAndAliases[3]['onTableAliasName']);
        }

        public function testDynamicallyDerivedAttributeOneOnSelfAndOneOnRelatedModelWhereSameAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //2 createdByUser__User attributes. One of self, one on related.
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdByUser__User';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___createdByUser__User';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}person{$q}.{$q}lastname{$q} asc, " .
                                                     "{$q}person1{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(8, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('_user',   $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('_user',   $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('_user1',  $leftTablesAndAliases[6]['tableAliasName']);
            $this->assertEquals('_user1',  $leftTablesAndAliases[7]['onTableAliasName']);
        }

        public function testDynamicallyDerivedAttributeOneOnSelfAndOneOnRelatedModelWhereDifferentAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Self createdByUser__User, related owner__User
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdByUser__User';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___owner__User';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}person{$q}.{$q}lastname{$q} asc, " .
                                                     "{$q}person1{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(6, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('_user',                 $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('item',                  $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('person',                $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('_user',                 $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('reportmodeltestitem',   $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem9',  $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem1',   $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem',   $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('_user1',                $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem1',   $leftTablesAndAliases[4]['onTableAliasName']);
            $this->assertEquals('person1',               $leftTablesAndAliases[5]['tableAliasName']);
            $this->assertEquals('_user1',                $leftTablesAndAliases[5]['onTableAliasName']);
        }

        public function testDynamicallyDerivedAttributeBothOnRelatedModelWhereDifferentAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Related createdByUser__User and related owner__User. On same related model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___createdByUser__User';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___owner__User';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                        = "{$q}person{$q}.{$q}lastname{$q} asc, " .
                                                     "{$q}person1{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(8, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('reportmodeltestitem',  $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem9', $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem',  $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('_user',                $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[4]['onTableAliasName']);
            $this->assertEquals('person',               $leftTablesAndAliases[5]['tableAliasName']);
            $this->assertEquals('_user',                $leftTablesAndAliases[5]['onTableAliasName']);
            $this->assertEquals('_user1',               $leftTablesAndAliases[6]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[6]['onTableAliasName']);
            $this->assertEquals('person1',              $leftTablesAndAliases[7]['tableAliasName']);
            $this->assertEquals('_user1',               $leftTablesAndAliases[7]['onTableAliasName']);
        }

        public function testNestedRelationsThatComeBackOnTheBaseModel()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Base model is Account.  Get related contact's opportunity's account's name
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'contacts___opportunities___account___name';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                        = "{$q}account1{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }


        public function testThreeTestedRelationsWhereTheyBothGoToTheSameModelButAtDifferentNestingPoints()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            //Accounts -> Opportunities, but also Accounts -> Contacts -> Opportunities,
            //and a third to go to Accounts again.
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'opportunities___name';
            $displayAttribute2                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'contacts___opportunities___name';
            $displayAttribute3                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType  = 'contacts___opportunities___account___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent                         = "{$q}opportunity{$q}.{$q}name{$q} asc, " .
                                                      "{$q}opportunity1{$q}.{$q}name{$q} asc, " .
                                                      "{$q}account1{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModelOne()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'meetings___category';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "{$q}customfield{$q}.{$q}value{$q} asc";
            $this->assertEquals($compareContent, $content);

            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',  $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('item',           $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',  $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('activity_id',    $leftTablesAndAliases[2]['tableJoinIdName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('customfield',    $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[3]['onTableAliasName']);
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModelTwo()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'meetings___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "{$q}meeting{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);

            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',  $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('item',           $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',  $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('activity_id',    $leftTablesAndAliases[2]['tableJoinIdName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[2]['onTableAliasName']);
        }

        public function testTwoAttributesDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModel()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'meetings___category';
            $displayAttribute2                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'meetings___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                      "{$q}meeting{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);

            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',  $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('item',           $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',  $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('activity_id',    $leftTablesAndAliases[2]['tableJoinIdName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('customfield',    $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[3]['onTableAliasName']);
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatDoesNotCastDown()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'model5ViaItem___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "{$q}reportmodeltestitem5{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeWhenThroughARelation()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Tests derivedRelation when going through a relation already before doing the derived relation
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'opportunities___meetings___category';
            $displayAttribute2                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'opportunities___meetings___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                      "{$q}meeting{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(8, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',  $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('item',           $leftTablesAndAliases[4]['onTableAliasName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[5]['tableAliasName']);
            $this->assertEquals('activity_item',  $leftTablesAndAliases[5]['onTableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[6]['tableAliasName']);
            $this->assertEquals('activity_id',    $leftTablesAndAliases[6]['tableJoinIdName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[6]['onTableAliasName']);
            $this->assertEquals('customfield',    $leftTablesAndAliases[7]['tableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[7]['onTableAliasName']);
        }

        public function testInferredRelationModelAttributeWithTwoAttributes()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Tests inferredRelation with 2 attributes on the opposing model. Only one declares the module specifically
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___industry';
            $displayAttribute2                               = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                      "{$q}account{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(6, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testInferredRelationModelAttributeWithTwoAttributesNestedTwoLevelsDeep()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___opportunities___stage';
            $displayAttribute2                               = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___opportunities___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                      "{$q}opportunity{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(7, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testInferredRelationModelAttributeWithTwoAttributesComingAtItFromANestedPoint()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Also declaring Via modules
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem7');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem7',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'model5___ReportModelTestItem__reportItems__Inferred___phone';
            $displayAttribute2                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem7',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'model5___ReportModelTestItem__reportItems__Inferred___dropDown';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "{$q}reportmodeltestitem{$q}.{$q}phone{$q} asc, " .
                                                      "{$q}customfield{$q}.{$q}value{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(7, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithItemAttribute()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___createdDateTime';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "{$q}item{$q}.{$q}createddatetime{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithMixedInAttribute()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___owner__User';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "{$q}person{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(6, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDowButAlsoWithFullCastDown()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___createdDateTime';
            $displayAttribute2                               = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                      "{$q}account{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }


        public function testDerivedRelationViaCastedUpModelAttributeWithCastingHintToNotCastDownSoFar()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'meetings___latestDateTime';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "{$q}activity{$q}.{$q}latestdatetime{$q} asc";
            $this->assertEquals($compareContent, $content);

            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }
**/
        /**
         * echo "<pre>";
        print_r($joinTablesAdapter->getFromTablesAndAliases());
        print_r($joinTablesAdapter->getLeftTablesAndAliases());
        echo "</pre>";
         */

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownTwiceWithNoSkips()
        {
            //todo: test casting down more than one level. not sure how to test this.. since meetings is only one skip past activity not really testing that castDown fully
            $this->fail();
        }

        public function testPolymorphic()
        {
            //todo: test polymorphics too? maybe we wouldnt have any for now? but we should still mark fail test here...
            $this->fail();
        }
    }
?>