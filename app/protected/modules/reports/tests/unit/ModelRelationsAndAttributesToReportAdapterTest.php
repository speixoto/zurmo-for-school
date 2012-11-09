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

    class ModelRelationsAndAttributesToReportAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetAllRelations()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules(); //ReportsTestModule rules
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $relations          = $adapter->getAllRelations();
            $this->assertEquals(16, count($relations));
        }

        /**
         * @depends testGetAllRelations
         */
        public function testGetAllReportableRelations()
        {
            //ReportModelTestItem has hasOne, hasMany, and hasOneAlso.  In addition it has a relationsViaParent
            //to ReportModelTestItem5.  Excludes any customField relations and relationsReportedOnAsAttributes
            //Also excludes any non-reportable relations
            //Get relations through adapter and confirm everything matches up as expected
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $relations = $adapter->getRelations();
            $this->assertEquals(11, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has One Again');
            $this->assertEquals($compareData, $relations['hasOneAgain']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Model 5 Via Item');
            $this->assertEquals($compareData, $relations['model5ViaItem']);
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Primary Address');
            $this->assertEquals($compareData, $relations['primaryAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * When retrieving available relations, make sure it does not give a relation based on what model it is coming
         * from.  If you are in a Contact and the parent relation is account, then Contact should not return the account
         * as an available relation.
         * @depends testGetAllReportableRelations
         */
        public function testGetAvailableRelationsDoesNotCauseFeedbackLoop()
        {
            $model              = new ReportModelTestItem2();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $relations = $adapter->getRelations();
            $this->assertEquals(5, count($relations));
            $compareData        = array('label' => 'Has Many2');
            $this->assertEquals($compareData, $relations['hasMany2']);
            $compareData        = array('label' => 'Has Many3');
            $this->assertEquals($compareData, $relations['hasMany3']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);

            $comingFromModel    = new ReportModelTestItem();
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $relations = $adapter->getRelations($comingFromModel);
            $this->assertEquals(6, count($relations));
            $compareData        = array('label' => 'Has Many3');
            $this->assertEquals($compareData, $relations['hasMany3']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * @depends testGetAvailableRelationsDoesNotCauseFeedbackLoop
         */
        public function testGetReportableAttributes()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesIncludingDerived();
            $this->assertEquals(24, count($attributes));
            $compareData        = array('label' => 'Id');
            $this->assertEquals($compareData, $attributes['id']);
            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modified Date Time']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            //Currency is treated as a relation reported as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation reported as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Includes derived attributes as well
            $compareData        = array('label' => 'Calculated');
            $this->assertEquals($compareData, $attributes['calculated']);
            $compareData        = array('label' => 'Full Name');
            $this->assertEquals($compareData, $attributes['FullName']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetReportableAttributes
         * Testing where a model relates to another model via something like Item. An example is notes which connects
         * to accounts via activityItems MANY_MANY through Items.  On Notes we need to be able to show  these relations
         * as selectable in reporting.
         *
         * In this example ReportModelTestItem5 connects to ReportModelTestItem and ReportModelTestItem2
         * via MANY_MANY through Item.
         * Known as viaRelations: model5ViaItem on ReportModelItem and model5ViaItem on ReportModelItem2
         */
        public function testGetReportableFacadeRelations()
        {
            $model              = new ReportModelTestItem5();
            $rules              = new ReportTest5Rules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $relations = $adapter->getFacadeRelations();
            $this->assertEquals(2, count($relations));
            $compareData        = array('label' => 'Facade To Model');
            $this->assertEquals($compareData, $relations['facadeToModel']);
            $compareData        = array('label' => 'Facade To Model 2');
            $this->assertEquals($compareData, $relations['facadeToModel2']);
            $this->fail(); //dont call this facadeRelations. think of something else
        }

        /**
         * @depends testGetAvailableAttributesDataAndLabels
         */
        public function testGetAvailableAttributesForRowsAndColumnsFilters()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForRowsAndColumnsFilters();
            $this->assertEquals(21, count($attributes));

            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modified Date Time']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            //Currency is treated as a relation reported as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation reported as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForRowsAndColumnsFilters
         */
        public function testGetAvailableAttributesForRowsAndColumnsDisplayColumns()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForRowsAndColumnsDisplayColumns();
            $this->assertEquals(23, count($attributes));

            //Includes derived attributes as well
            $compareData        = array('label' => 'Calculated');
            $this->assertEquals($compareData, $attributes['calculated']);
            $compareData        = array('label' => 'Full Name');
            $this->assertEquals($compareData, $attributes['FullName']);
        }

        /**
         * @depends testGetAvailableAttributesForRowsAndColumnsDisplayColumns
         */
        public function testGetAvailableAttributesForRowsAndColumnsOrderBys()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForRowsAndColumnsOrderBys();
            $this->assertEquals(21, count($attributes));

            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modified Date Time']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            //Currency is treated as a relation reported as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation reported as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForRowsAndColumnsOrderBys
         */
        public function testGetAvailableAttributesForSummationFilters()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForSummationFilters();
            $this->assertEquals(21, count($attributes));

            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modified Date Time']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            //Currency is treated as a relation reported as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation reported as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForSummationFilters
         */
        public function testGetAvailableAttributesForSummationDisplayAttributes()
        {
            //Depends on the selected Group By values.  This will determine what is available for display
            //Without any group by displayed, nothing is available
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForSummationDisplayAttributes();
            $this->assertEquals(0, count($attributes));

            //Select dropDown as the groupBy attribute
            $groupBy            = new ReportGroupBy('ReportModelTestItem');
            $groupBy->setAttribute('dropDown');
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes         = $adapter->getAttributesForSummationDisplayAttributes();
            $this->assertEquals(22, count($attributes));
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);

            $compareData        = array('label' => 'Count');
            $this->assertEquals($compareData, $attributes['Count']);

            $compareData        = array('label' => 'Created Date Time -(MIN)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Minimum']);
            $compareData        = array('label' => 'Created Date Time -(MAX)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Maximum']);
            $compareData        = array('label' => 'Modified Date Time (MIN)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Minimum']);
            $compareData        = array('label' => 'Modified Date Time -(MAX)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Maximum']);

            $compareData        = array('label' => 'Date (MIN)');
            $this->assertEquals($compareData, $attributes['date__Minimum']);
            $compareData        = array('label' => 'Date -(MAX)');
            $this->assertEquals($compareData, $attributes['date__Maximum']);
            $compareData        = array('label' => 'Date (MIN)');
            $this->assertEquals($compareData, $attributes['dateTime__Minimum']);
            $compareData        = array('label' => 'Date -(MAX)');
            $this->assertEquals($compareData, $attributes['dateTime__Maximum']);

            $compareData        = array('label' => 'Float -(MIX)');
            $this->assertEquals($compareData, $attributes['float__Minimum']);
            $compareData        = array('label' => 'Float -(MAX)');
            $this->assertEquals($compareData, $attributes['float__Maximum']);
            $compareData        = array('label' => 'Float -(SUM)');
            $this->assertEquals($compareData, $attributes['float__Summation']);
            $compareData        = array('label' => 'Float -(AVG)');
            $this->assertEquals($compareData, $attributes['float__Average']);

            $compareData        = array('label' => 'Integer -(MIX)');
            $this->assertEquals($compareData, $attributes['integer__Minimum']);
            $compareData        = array('label' => 'Integer -(MAX)');
            $this->assertEquals($compareData, $attributes['integer__Maximum']);
            $compareData        = array('label' => 'Integer -(SUM)');
            $this->assertEquals($compareData, $attributes['integer__Summation']);
            $compareData        = array('label' => 'Integer -(AVG)');
            $this->assertEquals($compareData, $attributes['integer__Average']);

            $compareData        = array('label' => 'Currency Value -(MIX)');
            $this->assertEquals($compareData, $attributes['currencyValue__Minimum']);
            $compareData        = array('label' => 'Currency Value -(MAX)');
            $this->assertEquals($compareData, $attributes['currencyValue__Maximum']);
            $compareData        = array('label' => 'Currency Value -(SUM)');
            $this->assertEquals($compareData, $attributes['currencyValue__Summation']);
            $compareData        = array('label' => 'Currency Value -(AVG)');
            $this->assertEquals($compareData, $attributes['currencyValue__Average']);


            //Add a second groupBy attribute radioDropDown on the same model
            $groupBy            = new ReportGroupBy('ReportModelTestItem');
            $groupBy->setAttribute('dropDown');
            $groupBy2           = new ReportGroupBy('ReportModelTestItem');
            $groupBy2->setAttribute('radioDropDown');
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $report->addGroupBy($groupBy);
            $report->addGroupBy($groupBy2);
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes         = $adapter->getAttributesForSummationDisplayAttributes();
            $this->assertEquals(23, count($attributes));
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
        }

        /**
         * @testGetAvailableAttributesForSummationDisplayAttributes
         */
        public function testGroupingOnDifferentModelAndMakingSureCorrectDisplayAttributesAreAvailable()
        {
            //Grouping on ReportModelTestItem, but we are looking at attributes in ReportModelTestItem2
            //so the name attribute should not show up as being available.
            $groupBy            = new ReportGroupBy('ReportModelTestItem');
            $groupBy->setAttribute('phone');
            $model              = new ReportModelTestItem2();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->addGroupBy($groupBy);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForSummationDisplayAttributes(new ReportModelTestItem(), 'hasOne');
            $this->assertEquals(5, count($attributes));
            $compareData        = array('label' => 'Count');
            $this->assertEquals($compareData, $attributes['Count']);
            $compareData        = array('label' => 'Created Date Time -(MIN)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Minimum']);
            $compareData        = array('label' => 'Created Date Time -(MAX)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Maximum']);
            $compareData        = array('label' => 'Modified Date Time (MIN)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Minimum']);
            $compareData        = array('label' => 'Modified Date Time -(MAX)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Maximum']);

            //Now test where there is a second group by and it is the name attribute on ReportModelTestItem2
            $groupBy            = new ReportGroupBy('ReportModelTestItem');
            $groupBy->setAttribute(array('hasOne' => 'name'));
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForSummationDisplayAttributes(new ReportModelTestItem(), 'hasOne');
            $this->assertEquals(6, count($attributes));
            $compareData        = array('label' => 'Name');
            $this->assertEquals($compareData, $attributes['name']);
            $compareData        = array('label' => 'Count');
            $this->assertEquals($compareData, $attributes['Count']);
            $compareData        = array('label' => 'Created Date Time -(MIN)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Minimum']);
            $compareData        = array('label' => 'Created Date Time -(MAX)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Maximum']);
            $compareData        = array('label' => 'Modified Date Time (MIN)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Minimum']);
            $compareData        = array('label' => 'Modified Date Time -(MAX)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Maximum']);

            //Now test where there is a second group by and it is the name attribute on ReportModelTestItem2 but we
            //are coming from a different relationship
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForSummationDisplayAttributes(new ReportModelTestItem(), 'hasOneAgain');
            $this->assertEquals(5, count($attributes));
            $this->assertFalse(isset($attributes['name']));

            //Test where the group by is 2 levels above
            $model              = new ReportModelTestItem3();
            $groupBy            = new ReportGroupBy('ReportModelTestItem');
            $groupBy->setAttribute(array('hasOne' => array('hasMany3' => 'somethingOn3')));
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForSummationDisplayAttributes(new ReportModelTestItem2(), 'hasMany3');
            $this->assertEquals(6, count($attributes));
            $compareData        = array('label' => 'somethingOn3');
            $this->assertEquals($compareData, $attributes['somethingOn3']);
            $compareData        = array('label' => 'Count');
            $this->assertEquals($compareData, $attributes['Count']);
            $compareData        = array('label' => 'Created Date Time -(MIN)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Minimum']);
            $compareData        = array('label' => 'Created Date Time -(MAX)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Maximum']);
            $compareData        = array('label' => 'Modified Date Time (MIN)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Minimum']);
            $compareData        = array('label' => 'Modified Date Time -(MAX)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Maximum']);
        }

        /**
         * @depends testGroupingOnDifferentModelAndMakingSureCorrectDisplayAttributesAreAvailable
         */
        public function testGetAvailableAttributesForSummationOrderBys()
        {
            //You can only order what is grouped on.
            //You can order on nothing because there are no group bys selected
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForSummationOrderBys();
            $this->assertEquals(0, count($attributes));

            //A group by is selected on the base model ReportModelTestItem
            $groupBy            = new ReportGroupBy('ReportModelTestItem');
            $groupBy->setAttribute('dropDown');
            $model              = new ReportModelTestItem();
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForSummationOrderBys();
            $this->assertEquals(1, count($attributes));
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);

            //Now test when a group by is also selected on the related ReportModelTestItem2
            $groupBy            = new ReportGroupBy('ReportModelTestItem');
            $groupBy->setAttribute(array('hasOne' => 'phone'));
            $model              = new ReportModelTestItem2();
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForSummationOrderBys(new ReportModelTestItem(), 'hasOne');
            $this->assertEquals(1, count($attributes));
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);

            //Now test a third group by on the base model ReportModelTestItem
            $groupBy            = new ReportGroupBy('ReportModelTestItem');
            $groupBy->setAttribute('radioDropDown');
            $model              = new ReportModelTestItem();
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForSummationOrderBys();
            $this->assertEquals(2, count($attributes));
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
        }

        /**
         * @depends testGetAvailableAttributesForSummationOrderBys
         */
        public function testGetAvailableAttributesForSummationGroupBys()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForSummationGroupBys();
            $this->assertEquals(35, count($attributes));

            //Date/DateTime columns first...
            $compareData        = array('label' => 'Date - Year');
            $this->assertEquals($compareData, $attributes['date__Year']);
            $compareData        = array('label' => 'Date - Quarter');
            $this->assertEquals($compareData, $attributes['date__Quarter']);
            $compareData        = array('label' => 'Date - Month');
            $this->assertEquals($compareData, $attributes['date__Month']);
            $compareData        = array('label' => 'Date - Week');
            $this->assertEquals($compareData, $attributes['date__Week']);
            $compareData        = array('label' => 'Date - Day');
            $this->assertEquals($compareData, $attributes['date__Day']);

            $compareData        = array('label' => 'Date Time - Year');
            $this->assertEquals($compareData, $attributes['dateTime__Year']);
            $compareData        = array('label' => 'Date Time - Quarter');
            $this->assertEquals($compareData, $attributes['dateTime__Quarter']);
            $compareData        = array('label' => 'Date Time - Month');
            $this->assertEquals($compareData, $attributes['dateTime__Month']);
            $compareData        = array('label' => 'Date Time - Week');
            $this->assertEquals($compareData, $attributes['dateTime__Week']);
            $compareData        = array('label' => 'Date Time - Day');
            $this->assertEquals($compareData, $attributes['dateTime__Day']);

            $compareData        = array('label' => 'Created Date Time - Year');
            $this->assertEquals($compareData, $attributes['createdDateTime__Year']);
            $compareData        = array('label' => 'Created Date Time - Quarter');
            $this->assertEquals($compareData, $attributes['createdDateTime__Quarter']);
            $compareData        = array('label' => 'Created Date Time - Month');
            $this->assertEquals($compareData, $attributes['createdDateTime__Month']);
            $compareData        = array('label' => 'Created Date Time - Week');
            $this->assertEquals($compareData, $attributes['createdDateTime__Week']);
            $compareData        = array('label' => 'Created Date Time - Day');
            $this->assertEquals($compareData, $attributes['createdDateTime__Day']);

            $compareData        = array('label' => 'Modified Date Time - Year');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Year']);
            $compareData        = array('label' => 'Modified Date Time - Quarter');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Quarter']);
            $compareData        = array('label' => 'Modified Date Time - Month');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Month']);
            $compareData        = array('label' => 'Modified Date Time - Week');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Week']);
            $compareData        = array('label' => 'Modified Date Time - Day');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Day']);

            //and then the rest of the attributes... (exclude text area)
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Id field
            $compareData        = array('label' => 'Idr');
            $this->assertEquals($compareData, $attributes['Id']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForSummationGroupBys
         */
        public function testGetAvailableAttributesForSummationDrillDownDisplayAttributes()
        {
            //Should be the same as the RowsAndColumns display columns
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForMatrixDisplayColumns();
            $this->assertEquals(23, count($attributes));

            //Includes derived attributes as well
            $compareData        = array('label' => 'Calculated');
            $this->assertEquals($compareData, $attributes['calculated']);
            $compareData        = array('label' => 'Full Name');
            $this->assertEquals($compareData, $attributes['FullName']);
        }

        /**
         * @depends testGetAvailableAttributesForSummationDrillDownDisplayAttributes
         */
        public function testGetAvailableAttributesForMatrixFilters()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForMatrixFilters();
            $this->assertEquals(21, count($attributes));

            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modified Date Time']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            //Currency is treated as a relation reported as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation reported as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForMatrixFilters
         */
        public function testGetAvailableAttributesForMatrixGroupBys()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForMatrixGroupBys();
            $this->assertEquals(35, count($attributes));

            //Date/DateTime columns first...
            $compareData        = array('label' => 'Date - Year');
            $this->assertEquals($compareData, $attributes['date__Year']);
            $compareData        = array('label' => 'Date - Quarter');
            $this->assertEquals($compareData, $attributes['date__Quarter']);
            $compareData        = array('label' => 'Date - Month');
            $this->assertEquals($compareData, $attributes['date__Month']);
            $compareData        = array('label' => 'Date - Week');
            $this->assertEquals($compareData, $attributes['date__Week']);
            $compareData        = array('label' => 'Date - Day');
            $this->assertEquals($compareData, $attributes['date__Day']);

            $compareData        = array('label' => 'Date Time - Year');
            $this->assertEquals($compareData, $attributes['dateTime__Year']);
            $compareData        = array('label' => 'Date Time - Quarter');
            $this->assertEquals($compareData, $attributes['dateTime__Quarter']);
            $compareData        = array('label' => 'Date Time - Month');
            $this->assertEquals($compareData, $attributes['dateTime__Month']);
            $compareData        = array('label' => 'Date Time - Week');
            $this->assertEquals($compareData, $attributes['dateTime__Week']);
            $compareData        = array('label' => 'Date Time - Day');
            $this->assertEquals($compareData, $attributes['dateTime__Day']);

            $compareData        = array('label' => 'Created Date Time - Year');
            $this->assertEquals($compareData, $attributes['createdDateTime__Year']);
            $compareData        = array('label' => 'Created Date Time - Quarter');
            $this->assertEquals($compareData, $attributes['createdDateTime__Quarter']);
            $compareData        = array('label' => 'Created Date Time - Month');
            $this->assertEquals($compareData, $attributes['createdDateTime__Month']);
            $compareData        = array('label' => 'Created Date Time - Week');
            $this->assertEquals($compareData, $attributes['createdDateTime__Week']);
            $compareData        = array('label' => 'Created Date Time - Day');
            $this->assertEquals($compareData, $attributes['createdDateTime__Day']);

            $compareData        = array('label' => 'Modified Date Time - Year');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Year']);
            $compareData        = array('label' => 'Modified Date Time - Quarter');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Quarter']);
            $compareData        = array('label' => 'Modified Date Time - Month');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Month']);
            $compareData        = array('label' => 'Modified Date Time - Week');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Week']);
            $compareData        = array('label' => 'Modified Date Time - Day');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Day']);

            //and then the rest of the attributes...
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);

            //think about this:
            //also for numbers... would need banding. on float, integer, currencyValue
            $this->fail(); //until we add code for banding. I think it would be where it gets displayed like Date can
            //have a between i think similar thing.
        }

        /**
         * @depends testGetAvailableAttributesForMatrixGroupBys
         */
        public function testGetAvailableAttributesForMatrixDisplayAttributes()
        {
            //Without any group by displayed, nothing is available
            $model              = new ReportModelTestItem();
            $rules              = new ReportTestRules();
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes = $adapter->getAttributesForMatrixDisplayAttributes();
            $this->assertEquals(0, count($attributes));

            //Select dropDown as the groupBy attribute
            $groupBy            = new ReportGroupBy('ReportModelTestItem');
            $groupBy->setAttribute('dropDown');
            $report             = new Report();
            $report->setModuleClassName('ReportsTestModule');
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report);
            $attributes         = $adapter->getAttributesForMatrixDisplayAttributes();
            $this->assertEquals(21, count($attributes));
            $compareData        = array('label' => 'Count');
            $this->assertEquals($compareData, $attributes['Count']);

            $compareData        = array('label' => 'Created Date Time -(MIN)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Minimum']);
            $compareData        = array('label' => 'Created Date Time -(MAX)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Maximum']);
            $compareData        = array('label' => 'Modified Date Time (MIN)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Minimum']);
            $compareData        = array('label' => 'Modified Date Time -(MAX)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Maximum']);

            $compareData        = array('label' => 'Date (MIN)');
            $this->assertEquals($compareData, $attributes['date__Minimum']);
            $compareData        = array('label' => 'Date -(MAX)');
            $this->assertEquals($compareData, $attributes['date__Maximum']);
            $compareData        = array('label' => 'Date (MIN)');
            $this->assertEquals($compareData, $attributes['dateTime__Minimum']);
            $compareData        = array('label' => 'Date -(MAX)');
            $this->assertEquals($compareData, $attributes['dateTime__Maximum']);

            $compareData        = array('label' => 'Float -(MIX)');
            $this->assertEquals($compareData, $attributes['float__Minimum']);
            $compareData        = array('label' => 'Float -(MAX)');
            $this->assertEquals($compareData, $attributes['float__Maximum']);
            $compareData        = array('label' => 'Float -(SUM)');
            $this->assertEquals($compareData, $attributes['float__Summation']);
            $compareData        = array('label' => 'Float -(AVG)');
            $this->assertEquals($compareData, $attributes['float__Average']);

            $compareData        = array('label' => 'Integer -(MIX)');
            $this->assertEquals($compareData, $attributes['integer__Minimum']);
            $compareData        = array('label' => 'Integer -(MAX)');
            $this->assertEquals($compareData, $attributes['integer__Maximum']);
            $compareData        = array('label' => 'Integer -(SUM)');
            $this->assertEquals($compareData, $attributes['integer__Summation']);
            $compareData        = array('label' => 'Integer -(AVG)');
            $this->assertEquals($compareData, $attributes['integer__Average']);

            $compareData        = array('label' => 'Currency Value -(MIX)');
            $this->assertEquals($compareData, $attributes['currencyValue__Minimum']);
            $compareData        = array('label' => 'Currency Value -(MAX)');
            $this->assertEquals($compareData, $attributes['currencyValue__Maximum']);
            $compareData        = array('label' => 'Currency Value -(SUM)');
            $this->assertEquals($compareData, $attributes['currencyValue__Summation']);
            $compareData        = array('label' => 'Currency Value -(AVG)');
            $this->assertEquals($compareData, $attributes['currencyValue__Average']);
        }
    }
?>
