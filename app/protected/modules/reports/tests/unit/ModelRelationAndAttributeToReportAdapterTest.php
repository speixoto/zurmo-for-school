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

    class ModelRelationAndAttributeToReportAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetAllRelations()
        {
            //Get count of all normal relations
            $model              = new ReportModelTestItem();
            $adapter            = new ModelRelationAndAttributeToReportAdapter($model);
            $relations          = $adapter->getAllRelations();
            $this->assertEquals(10, count($relations));
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
            $adapter            = new ModelRelationAndAttributeToReportAdapter($model);
            $relations = $adapter->getAllReportableRelations();
            $this->assertEquals(4, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Model Via Item');
            $this->assertEquals($compareData, $relations['modelViaItem']);
        }

        /**
         * When retrieving available relations, make sure it does not give a relation based on what model it is coming
         * from.  If you are in a Contact and the parent relation is account, then Contact should not return the account
         * as an available relation.
         */
        public function testGetAvailableRelationsDoesNotCauseFeedbackLoop()
        {
        }

        public function testGetAvailableAttributesDataAndLabels()
        {
        }

        public function testGetAvailableAttributesForRowsAndColumnsFilters()
        {
        }

        public function testGetAvailableAttributesForRowsAndColumnsDisplayColumns()
        {
        }

        public function testGetAvailableAttributesForRowsAndColumnsOrderBys()
        {
        }

        public function testGetAvailableAttributesForSummationFilters()
        {
        }

        public function testGetAvailableAttributesForSummationDisplayAttributes()
        {
        }

        public function testGetAvailableAttributesForSummationOrderBys()
        {
        }

        public function testGetAvailableAttributesForSummationGroupBys()
        {
        }

        public function testGetAvailableAttributesForSummationDrillDownDisplayAttributes()
        {
        }

        public function testGetAvailableAttributesForMatrixFilters()
        {
        }

        public function testGetAvailableAttributesForMatrixGroupBys()
        {
        }

        public function testGetAvailableAttributesForMatrixnDisplayAttributes()
        {
        }
    }
?>
