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

    class FilterForReportFormTest extends ZurmoBaseTest
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

        public function testSetAndGetFilter()
        {
            $filter                              = new FilterForReportForm('ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->operator                    = 'equals';
            $filter->value                       = 'Zurmo';
            $this->assertEquals('string', $filter->attributeAndRelationData);
            $this->assertEquals('string', $filter->attributeIndexOrDerivedType);
            $this->assertEquals('string', $filter->getResolvedAttribute());
            $this->assertEquals('String', $filter->getDisplayLabel());

            $filter                              = new FilterForReportForm('ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasOne___name';
            $filter->operator                    = 'equals';
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasOne', 'name'), $filter->getAttributeAndRelationData());
            $this->assertEquals('hasOne___name',         $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem',   $filter->getPenultimateModelClassName());
            $this->assertEquals('hasOne',                $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem2',  $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItem2 >> Name', $filter->getDisplayLabel());

            //2 levels deeps
            $filter                              = new FilterForReportForm('ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasOne___hasMany3___name';
            $filter->operator                    = 'equals';
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasOne', 'hasMany3', 'name'), $filter->getAttributeAndRelationData());
            $this->assertEquals('hasOne___hasMany3___name',          $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',              $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany3',                          $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem3',              $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItem2 >> ReportModelTestItem3s >> Name', $filter->getDisplayLabel());
        }

        /**
         * @depends testSetAndGetFilter
         */
        public function testInferredRelationSetAndGet()
        {
            $filter                              = new FilterForReportForm('ReportModelTestItem5',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'ReportModelTestItem__reportItems__Inferred___phone';
            $filter->operator                    = 'equals';
            $filter->value                       = '1234567890';
            $this->assertEquals(array('ReportModelTestItem__reportItems__Inferred', 'phone'),
                                                                    $filter->getAttributeAndRelationData());
            $this->assertEquals('ReportModelTestItem__reportItems__Inferred___phone',
                                                                    $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem5',             $filter->getPenultimateModelClassName());
            $this->assertEquals('ReportModelTestItem__reportItems__Inferred',
                                                                    $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',              $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItems >> Phone',    $filter->getDisplayLabel());
        }

        /**
         * @depends testInferredRelationSetAndGet
         */
        public function testDerivedRelationSetAndGet()
        {
            $filter                              = new FilterForReportForm('ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'model5ViaItem___name';
            $filter->operator                    = 'equals';
            $filter->value                       = '1234567890';
            $this->assertEquals(array('model5ViaItem', 'name'),     $filter->getAttributeAndRelationData());
            $this->assertEquals('model5ViaItem___name',             $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem',              $filter->getPenultimateModelClassName());
            $this->assertEquals('model5ViaItem',                    $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem5',             $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItem5s >> Name',    $filter->getDisplayLabel());
        }

        /**
         * @depends testDerivedRelationSetAndGet
         */
        public function testRelationReportedAsAttributeSetAndGet()
        {
            //test dropDown
            $filter                              = new FilterForReportForm('ReportModelTestItem2',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasMany2___dropDown';
            $filter->operator                    = 'equals';
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasMany2', 'dropDown'), $filter->getAttributeAndRelationData());
            $this->assertEquals('hasMany2___dropDown',         $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',        $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany2',                    $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',         $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItems >> Drop Down', $filter->getDisplayLabel());

            //test currencyValue
            $filter                              = new FilterForReportForm('ReportModelTestItem2',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasMany2___currencyValue';
            $filter->operator                    = 'equals';
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasMany2', 'currencyValue'), $filter->getAttributeAndRelationData());
            $this->assertEquals('hasMany2___currencyValue',         $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',        $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany2',                    $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',         $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItems >> Currency Value', $filter->getDisplayLabel());

            //test reportedAsAttribute
            $filter                              = new FilterForReportForm('ReportModelTestItem2',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasMany2___reportedAsAttribute';
            $filter->operator                    = 'equals';
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasMany2', 'reportedAsAttribute'), $filter->getAttributeAndRelationData());
            $this->assertEquals('hasMany2___reportedAsAttribute',         $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',                   $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany2',                               $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',                    $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItems >> Reported As Attribute', $filter->getDisplayLabel());

            //test the likeContactState
            $filter                              = new FilterForReportForm('ReportModelTestItem2',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasMany2___likeContactState';
            $filter->operator                    = 'equals';
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasMany2', 'likeContactState'),        $filter->getAttributeAndRelationData());
            $this->assertEquals('hasMany2___likeContactState',                $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',                       $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany2',                                   $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',                        $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItems >> A name for a state', $filter->getDisplayLabel());
        }

        /**
         * @depends testRelationReportedAsAttributeSetAndGet
         */
        public function testDynamicallyDerivedAttributeSetAndGet()
        {
            //test the likeContactState
            $filter                              = new FilterForReportForm('ReportModelTestItem2',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasMany2___owner__User';
            $filter->operator                    = 'equals';
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasMany2', 'owner__User'),      $filter->getAttributeAndRelationData());
            $this->assertEquals('hasMany2___owner__User',              $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',                $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany2',                            $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',                 $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItems >> Owner',       $filter->getDisplayLabel());
        }

        /**
         * @depends testDynamicallyDerivedAttributeSetAndGet
         */
        public function testValidateTextAttribute()
        {
            $filter                              = new FilterForReportForm('ReportModelTestItem',
                                                                            Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'string';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'),
                                                         'value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $filter->operator                    = 'equals';
            $filter->value                       = 'Jason';
            $validated = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateTextAttribute
         */
        public function testValidateIntegerAttribute()
        {
            $filter                              = new FilterForReportForm('ReportModelTestItem',
                                                                            Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'integer';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'),
                                                         'value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //value is expected to be an array. will fail
            $filter->operator                    = 'equals';
            $filter->value                       = 'Zurmo';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value is improper type.'));
            $this->assertEquals($compareErrors, $errors);

            //also check value as 456 but not array. should still fail.
            $filter->value                       = 456;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value is improper type.'));
            $this->assertEquals($compareErrors, $errors);

            //now check as array,but with strings
            $filter->value                       = array('first' => 'test', 'second' => 'test2');
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value is improper type.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->value                       = array('first' => 456, 'second' => 789);
            //now check as array , but with integers - should pass
            $validated                           = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateIntegerAttribute
         */
        public function testValidateDateAttribute()
        {
            //todo:
        }

        /**
         * @depends testValidateDateAttribute
         */
        public function testValidateDateTimeAttribute()
        {
            //todo:
        }

        //do owner too..
        //stub out rest include dropdown, contactsTate etc (does it sub with ID or no?) hmm

        public function testValidateDropDownAttribute()
        {
                //['value'] = 'Automotive'   this is ok
        }

        public function testValidateLikeContactStateAttribute()
        {
            //seems dumb to have a sub 'id' since we only have value. but then it stays consistent? but we aren't using normal models
            //['value'][id'] - hmm. or do we just set the state id directly as the 'value'?
        }

        public function testValidateCurrencyValueAttribute()
        {
            //['value']['first']
            //['value']['second']
            //['value']['currency']['id']    //how do we know what to do with this? fortunetely it could come in as anything so who cares string/int
            //well now we know not to do 0,1 as value sub arrays, but using first/second is smart
        }

        public function testValidateDynamicallyDerivedOwnerAttribute()
        {
            //['value']['id'] = 'would be id of owner'. should we do it like this?
        }
        //test runTime as part of tests above
    }
?>