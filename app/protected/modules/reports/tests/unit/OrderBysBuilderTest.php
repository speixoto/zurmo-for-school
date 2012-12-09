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

    class OrderBysBuilderTest extends ZurmoBaseTest
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

        public function testNonRelatedNonDerivedAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single sortable attribute
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'phone';
            $content                               = $builder->makeQueryContent(array($orderBy));
            $this->assertEquals("{$q}reportmodeltestitem{$q}.{$q}phone{$q} asc", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Add a second attribute on the same model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'integer';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}reportmodeltestitem{$q}.{$q}phone{$q} asc, " .
                                                     "{$q}reportmodeltestitem{$q}.{$q}integer{$q} asc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedCastedUpAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single sortable attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $content                               = $builder->makeQueryContent(array($orderBy));
            $this->assertEquals("{$q}item{$q}.{$q}createddatetime{$q} asc", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Two sortable attribute that are casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'modifiedDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasOneRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_ONE
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne___createdDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
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
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany___createdDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
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
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'account___createdDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
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
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany1___createdDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
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
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'hasOne___createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne___modifiedDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
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
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'hasMany___createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany___modifiedDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
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
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'account___createdDateTime';
            $orderBy2                              = new OrderByForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'account___modifiedDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
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
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'hasMany1___createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany1___modifiedDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
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
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne___createdDateTime';
            $orderBy3                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy3->attributeIndexOrDerivedType = 'hasOne___modifiedDateTime';
            $orderBy3->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2, $orderBy3));
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
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany___createdDateTime';
            $orderBy3                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy3->attributeIndexOrDerivedType = 'hasMany___modifiedDateTime';
            $orderBy3->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2, $orderBy3));
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
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'account___modifiedDateTime';
            $orderBy2->order                       = 'desc';
            $orderBy3                              = new OrderByForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy3->attributeIndexOrDerivedType = 'account___modifiedDateTime';
            $orderBy3->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} desc" .
                                                     "{$q}item1{$q}.{$q}modifieddatetime{$q} desc" .
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAManyManyRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is MANY_MANY and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $builder                               = new OrderBysBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany1___createdDateTime';
            $orderBy3                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy3->attributeIndexOrDerivedType = 'hasMany1___modifiedDateTime';
            $orderBy3->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2, $orderBy3));
            $compareContent                        = "{$q}item{$q}.{$q}modifieddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

/**
 *             echo "<pre>";
print_r($joinTablesAdapter->getFromTablesAndAliases());
print_r($joinTablesAdapter->getLeftTablesAndAliases());
echo "</pre>";
 */
        //todo: casted up on 2 custom fields cause you ahve the canHaveBeans situation. good to test
        //todo: test derived like __User, via self and related, via doubles too
        //todo: link-specific 2 relations of the same model classes. testing this out various ways. good to test
        //todo: derived dynamics - 1 level, then 2 levels deep
        //todo: derived inferreds - 1 level, then 2 levels deep
        //todo: Acc -> con -> opp -> acc? i dont think will work but maybe it will.
        //todo: acc -> opps, but also acc-> con -> opps
        //todo: with stuff already joined from filter? - seperate test class?? we can just fill in join with existing stuff.
        //tdod
    }
?>