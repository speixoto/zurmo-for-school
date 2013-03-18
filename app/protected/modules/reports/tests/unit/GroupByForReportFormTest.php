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

    class GroupByForReportFormTest extends ZurmoBaseTest
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

        public function testGetDisplayLabelForAModifier()
        {
            $groupBy = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                      Report::TYPE_SUMMATION);
            $groupBy->attributeIndexOrDerivedType = 'createdDateTime__Quarter';
            $this->assertEquals('Created Date Time -(Quarter)',    $groupBy->getDisplayLabel());
        }

        public function testSetAndGetGroupBy()
        {
            $groupBy                              = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_SUMMATION);
            $groupBy->attributeIndexOrDerivedType = 'string';
            $this->assertEquals('x', $groupBy->axis);
            $groupBy->axis                        = 'y';
            $this->assertEquals('string', $groupBy->attributeAndRelationData);
            $this->assertEquals('string', $groupBy->attributeIndexOrDerivedType);
            $this->assertEquals('string', $groupBy->getResolvedAttribute());
            $this->assertEquals('String', $groupBy->getDisplayLabel());
        }

        /**
         * @depends testSetAndGetGroupBy
         */
        public function testValidate()
        {
            $groupBy                              = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_SUMMATION);
            $groupBy->attributeIndexOrDerivedType = 'string';
            $groupBy->axis                        = null;
            $validated = $groupBy->validate();
            $this->assertFalse($validated);
            $errors = $groupBy->getErrors();
            $compareErrors                       = array('axis'  => array('Axis cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $groupBy->axis                        = 'z';
            $validated = $groupBy->validate();
            $this->assertFalse($validated);
            $errors = $groupBy->getErrors();
            $compareErrors                       = array('axis'  => array('Axis must be x or y.'));
            $this->assertEquals($compareErrors, $errors);
            $groupBy->axis                        = 'y';
            $validated = $groupBy->validate();
            $this->assertTrue($validated);
        }
    }
?>