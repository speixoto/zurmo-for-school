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

    class SavedReportToReportAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $billy = UserTestHelper::createBasicUser('billy');
        }

        public function setup()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testResolveReportToSavedReport()
        {
            $billy       = User::getByUsername('billy');
            $report      = new Report();
            $report->setDescription    ('aDescription');
            $report->setModuleClassName('ReportsTestModule');
            $report->setName           ('myFirstReport');
            $report->setType           (Report::TYPE_ROWS_AND_COLUMNS);
            $report->setOwner          ($billy);

            $filter = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->value                       = 'aValue';
            $filter->operator                    = 'Equals';
            $filter->availableAtRunTime          = true;
            $report->addFilter($filter);

            $filter = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $filter->attributeIndexOrDerivedType = 'currencyValue';
            $filter->value                       = 'aValue';
            $filter->secondValue                 = 'bValue';
            $filter->operator                    = 'Between';
            $filter->currencyIdForValue          = '4';
            $filter->availableAtRunTime          = true;
            $report->addFilter($filter);

            $filter = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $filter->attributeIndexOrDerivedType = 'owner__User';
            $filter->value                       = 'aValue';
            $filter->stringifiedModelForValue    = 'someName';
            $filter->availableAtRunTime          = false;
            $report->addFilter($filter);

            $filter = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $filter->attributeIndexOrDerivedType = 'createdDateTime';
            $filter->value                       = 'aValue';
            $filter->secondValue                 = 'bValue';
            $filter->operator                    = null;
            $filter->currencyIdForValue          = null;
            $filter->availableAtRunTime          = true;
            $filter->valueType                   = 'Between';
            $report->addFilter($filter);

            $groupBy = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'lastName';
            $groupBy->axis                        = 'y';
            $report->addGroupBy($groupBy);

            $orderBy = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $orderBy->attributeIndexOrDerivedType = 'url';
            $orderBy->order                       = 'desc';
            $report->addOrderBy($orderBy);

            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                  $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'phone';
            $displayAttribute->label                       = 'someNewLabel';
            $report->addDisplayAttribute($displayAttribute);

            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('ReportsTestModule',
                                                                  'ReportModelTestItem', $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'firstName';
            $drillDownDisplayAttribute->label                       = 'someNewLabel';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);

            $savedReport = new SavedReport();
            $this->assertNull($savedReport->serializedData);

            SavedReportToReportAdapter::resolveReportToSavedReport($report, $savedReport);

            $this->assertEquals('ReportsTestModule',           $savedReport->moduleClassName);
            $this->assertEquals('myFirstReport',               $savedReport->name);
            $this->assertEquals('aDescription',                $savedReport->description);
            $this->assertEquals(Report::TYPE_ROWS_AND_COLUMNS, $savedReport->type);
            $this->assertTrue($savedReport->owner->isSame($billy));
            $compareData = array('Filters' => array(
                array(
                    'availableAtRunTime'           => true,
                    'currencyIdForValue'           => null,
                    'value'                        => 'aValue',
                    'secondValue'                  => null,
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'string',
                    'operator'					   => 'Equals',
                ),
                array(
                    'availableAtRunTime'           => true,
                    'currencyIdForValue'           => '4',
                    'value'                        => 'aValue',
                    'secondValue'                  => 'bValue',
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'currencyValue',
                    'operator'					   => 'Between',
                ),
                array(
                    'availableAtRunTime'           => false,
                    'currencyIdForValue'           => null,
                    'value'                        => 'aValue',
                    'secondValue'                  => null,
                    'stringifiedModelForValue'     => 'someName',
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'owner__User',
                    'operator'					   => null,
                ),
                array(
                    'availableAtRunTime'           => true,
                    'value'                        => 'aValue',
                    'secondValue'                  => 'bValue',
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => 'Between',
                    'attributeIndexOrDerivedType'  => 'createdDateTime',
                    'operator'					   => null,
                    'currencyIdForValue'           => null,
                ),
            ),
            'OrderBys' => array(
                array(
                    'order'                        => 'desc',
                    'attributeIndexOrDerivedType'  => 'url',
                )
            ),
            'GroupBys' => array(
                array(
                    'axis' => 'y',
                    'attributeIndexOrDerivedType' => 'lastName',
                ),
            ),
            'DisplayAttributes' => array(
                array(
                    'label'						  => 'someNewLabel',
                    'attributeIndexOrDerivedType' => 'phone',
                )
            ),
            'DrillDownDisplayAttributes' => array(
                array(
                    'label'                       => 'someNewLabel',
                    'attributeIndexOrDerivedType' => 'firstName',
                )
            ));
            $unserializedData = unserialize($savedReport->serializedData);
            $this->assertEquals($compareData['Filters'],                     $unserializedData['Filters']);
            $this->assertEquals($compareData['OrderBys'],                    $unserializedData['OrderBys']);
            $this->assertEquals($compareData['GroupBys'],                    $unserializedData['GroupBys']);
            $this->assertEquals($compareData['DisplayAttributes'],           $unserializedData['DisplayAttributes']);
            $this->assertEquals($compareData['DrillDownDisplayAttributes'],  $unserializedData['DrillDownDisplayAttributes']);
            $saved = $savedReport->save();
            $this->assertTrue($saved);
        }

        /**
         * @depends testResolveReportToSavedReport
         */
        public function testMakeReportBySavedReport()
        {
            $billy                      = User::getByUsername('billy');
            $savedReports               = SavedReport::getAll();
            $this->assertEquals           (1, count($savedReports));
            $savedReport                = $savedReports[0];
            $report                     = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            $filters                    = $report->getFilters();
            $groupBys                   = $report->getGroupBys();
            $orderBys                   = $report->getOrderBys();
            $displayAttributes          = $report->getDisplayAttributes();
            $drillDownDisplayAttributes = $report->getDrillDownDisplayAttributes();
            $this->assertEquals    	      ('ReportsTestModule',           $report->getModuleClassName());
            $this->assertEquals           ('myFirstReport',               $report->getName());
            $this->assertEquals           ('aDescription',                $report->getDescription());
            $this->assertEquals           (Report::TYPE_ROWS_AND_COLUMNS, $report->getType());
            $this->assertTrue             ($report->getOwner()->isSame($billy));
            $this->assertCount            (4, $filters);
            $this->assertCount            (1, $groupBys);
            $this->assertCount            (1, $orderBys);
            $this->assertCount            (1, $displayAttributes);
            $this->assertCount            (1, $drillDownDisplayAttributes);

            $this->assertEquals           (true,         $filters[0]->availableAtRunTime);
            $this->assertEquals           ('aValue',     $filters[0]->value);
            $this->assertEquals           ('string',     $filters[0]->attributeIndexOrDerivedType);
            $this->assertNull             ($filters[0]->currencyIdForValue);
            $this->assertNull             ($filters[0]->secondValue);
            $this->assertNull             ($filters[0]->stringifiedModelForValue);
            $this->assertNull             ($filters[0]->valueType);
            $this->assertEquals           ('Equals',     $filters[0]->operator);

            $this->assertEquals           (true,             $filters[1]->availableAtRunTime);
            $this->assertEquals           ('aValue',         $filters[1]->value);
            $this->assertEquals           ('currencyValue',  $filters[1]->attributeIndexOrDerivedType);
            $this->assertEquals           (4,                $filters[1]->currencyIdForValue);
            $this->assertEquals           ('bValue',         $filters[1]->secondValue);
            $this->assertNull             ($filters[1]->stringifiedModelForValue);
            $this->assertNull             ($filters[1]->valueType);
            $this->assertEquals           ('Between',         $filters[1]->operator);

            $this->assertEquals           (false,            $filters[2]->availableAtRunTime);
            $this->assertEquals           ('aValue',         $filters[2]->value);
            $this->assertEquals           ('owner__User',    $filters[2]->attributeIndexOrDerivedType);
            $this->assertNull             ($filters[2]->currencyIdForValue);
            $this->assertNull             ($filters[2]->secondValue);
            $this->assertEquals           ('someName',       $filters[2]->stringifiedModelForValue);
            $this->assertNull             ($filters[2]->valueType);
            $this->assertNull             ($filters[2]->operator);

            $this->assertEquals           (true,               $filters[3]->availableAtRunTime);
            $this->assertEquals           ('aValue',           $filters[3]->value);
            $this->assertEquals           ('createdDateTime',  $filters[3]->attributeIndexOrDerivedType);
            $this->assertNull             ($filters[3]->currencyIdForValue);
            $this->assertEquals           ('bValue',           $filters[3]->secondValue);
            $this->assertNull             ($filters[3]->stringifiedModelForValue);
            $this->assertNull             ($filters[3]->operator);
            $this->assertEquals           ('Between',          $filters[3]->valueType);

            $this->assertEquals           ('url',              $orderBys[0]->attributeIndexOrDerivedType);
            $this->assertEquals           ('desc',             $orderBys[0]->order);

            $this->assertEquals           ('lastName',         $groupBys[0]->attributeIndexOrDerivedType);
            $this->assertEquals           ('y',                $groupBys[0]->axis);

            $this->assertEquals           ('phone',            $displayAttributes[0]->attributeIndexOrDerivedType);
            $this->assertEquals           ('someNewLabel',     $displayAttributes[0]->label);

            $this->assertEquals           ('firstName',        $drillDownDisplayAttributes[0]->attributeIndexOrDerivedType);
            $this->assertEquals           ('someNewLabel',     $drillDownDisplayAttributes[0]->label);
        }
    }
?>