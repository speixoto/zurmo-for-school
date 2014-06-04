<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class SearchDataProviderMetadataAdapterTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testEmptyConcatedValue()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchAttributes = array(
                'concatedName' => null, //should resolve to no clause
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAASearchFormTestModel(new AAA(false)),
                1,
                $searchAttributes
            );
            $metadata         = $metadataAdapter->getAdaptedMetadata();
            $compareStructure = '';
            $this->assertEquals(array(), $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchingOnACustomFieldWithMultipleValues()
        {
            $searchAttributes = array(
                'industry' => array(
                    'value'    => array('A', 'B', 'C'),
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new TestCustomFieldsModel(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'industry',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'oneOf',
                    'value'                => array('A', 'B', 'C'),
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchingOnACustomFieldWithMultipleValuesWhereWithEmptyValueSpecified()
        {
            $searchAttributes = array(
                'industry' => array(
                    'value'    => array(''),
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new TestCustomFieldsModel(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array();
            $compareStructure = null;
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testGetAdaptedMetadataForhasManyRelationAttributes()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'ks' => array(
                    'kMember'    => 'some Value',
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new I(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'ks',
                    'relatedAttributeName' => 'kMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'some Value',
                ),
            );

            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchFormDynamicAttributesForRelatedManyManyDateTimeAttributes()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'dateDateTimeADate__Date'          => array('type'         => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER,
                                                         'firstDate'  => '1993-04-04'),
                'dateDateTimeADateTime__DateTime'  => array('type'         => MixedDateTypesSearchFormAttributeMappingRules::TYPE_TODAY),
            );
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $this->assertTrue($searchForm->isAttributeOnForm('dateDateTimeADate__Date'));
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $todayDateTime      = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $today              = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                  $todayDateTime->getTimeStamp());
            $todayPlus7Days     = MixedDateTypesSearchFormAttributeMappingRules::calculateNewDateByDaysFromNow(7);
            $metadata           = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'manyMany',
                    'relatedAttributeName' => 'aDate',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '1993-04-04',
                ),
                2 => array(
                    'attributeName'        => 'manyMany',
                    'relatedAttributeName' => 'aDateTime',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => DateTimeUtil::
                                              convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($today),
                ),
                3 => array(
                    'attributeName'        => 'manyMany',
                    'relatedAttributeName' => 'aDateTime',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => DateTimeUtil::
                                              convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today),
                ),
            );

            $compareStructure = '(1) and (2 and 3)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testAdaptingBooleanValues()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //Test with blank values for boolean on model and on related model.
            //Will treat as '0'. Normally you would sanitize $searchAttributes so that this
            //would be removed before passing into the adapter.
            $searchAttributes = array(
                'bool'       => '',
                'a'          => array('a' => ''),
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new TestBooleanAttributeModel(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'bool',
                    'operatorType'  => 'equals',
                    'value'         => '0',
                ),
                2 => array(
                    'attributeName' => 'bool',
                    'operatorType'  => 'isNull',
                    'value'         => null,
                ),
                3 => array(
                    'attributeName'        => 'a',
                    'relatedAttributeName' => 'a',
                    'operatorType'         => 'equals',
                    'value'                => '0',
                ),
                4 => array(
                    'attributeName'        => 'a',
                    'relatedAttributeName' => 'a',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                ),
            );
            $compareStructure = '(1 or 2) and (3 or 4)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            $searchAttributes = array(
                'bool'       => false,
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new TestBooleanAttributeModel(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'bool',
                    'operatorType'  => 'equals',
                    'value'         => '0',
                ),
                2 => array(
                    'attributeName' => 'bool',
                    'operatorType'  => 'isNull',
                    'value'         => null,
                )
            );
            $compareStructure = '(1 or 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
            //Now test with populated as '0'
            $searchAttributes = array(
                'bool'       => '0',
                'a'          => array('a' => '0'),
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new TestBooleanAttributeModel(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'bool',
                    'operatorType'  => 'equals',
                    'value'         => '0',
                ),
                2 => array(
                    'attributeName' => 'bool',
                    'operatorType'  => 'isNull',
                    'value'         => null,
                ),
                3 => array(
                    'attributeName'        => 'a',
                    'relatedAttributeName' => 'a',
                    'operatorType'         => 'equals',
                    'value'                => '0',
                ),
                4 => array(
                    'attributeName'        => 'a',
                    'relatedAttributeName' => 'a',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                ),
            );
            $compareStructure = '(1 or 2) and (3 or 4)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Now test with populated as '1'
            $searchAttributes = array(
                'bool'       => '1',
                'a'          => array('a' => '1'),
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new TestBooleanAttributeModel(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'bool',
                    'operatorType'  => 'equals',
                    'value'         => (bool)1,
                ),
                2 => array(
                    'attributeName'        => 'a',
                    'relatedAttributeName' => 'a',
                    'operatorType'         => 'equals',
                    'value'                => (bool)1,
                ),
            );
            $compareStructure = '1 and 2';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testAdaptingBooleanValuesWithSearchForms()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //Test with blank values for boolean on model and on related model.
            //Will treat as '0'. Normally you would sanitize $searchAttributes so that this
            //would be removed before passing into the adapter.
            $searchAttributes = array(
                'aaaBoolean'       => '',
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAASearchFormTestModel(new AAA()),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'aaaBoolean',
                    'operatorType'  => 'equals',
                    'value'         => '0',
                ),
                2 => array(
                    'attributeName' => 'aaaBoolean',
                    'operatorType'  => 'isNull',
                    'value'         => null,
                ),
            );
            $compareStructure = '(1 or 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            $searchAttributes = array(
                'aaaBoolean'       => false,
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAASearchFormTestModel(new AAA()),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'aaaBoolean',
                    'operatorType'  => 'equals',
                    'value'         => '0',
                ),
                2 => array(
                    'attributeName' => 'aaaBoolean',
                    'operatorType'  => 'isNull',
                    'value'         => null,
                ),
            );
            $compareStructure = '(1 or 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
            //Now test with populated as '0'
            $searchAttributes = array(
                'aaaBoolean'       => '0',
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAASearchFormTestModel(new AAA()),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'aaaBoolean',
                    'operatorType'  => 'equals',
                    'value'         => '0',
                ),
                2 => array(
                    'attributeName' => 'aaaBoolean',
                    'operatorType'  => 'isNull',
                    'value'         => null,
                ),
            );
            $compareStructure = '(1 or 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Now test with populated as '1'
            $searchAttributes = array(
                'aaaBoolean'       => '1',
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAASearchFormTestModel(new AAA()),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'aaaBoolean',
                    'operatorType'  => 'equals',
                    'value'         => (bool)1,
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testGetAdaptedMetadata()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'name'          => 'Vomitorio Corp',
                'officePhone'   => '5',
                'billingAddress' => array(
                    'street1'    => null,
                    'street2'    => 'Suite 101',
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'name',
                    'operatorType'  => 'startsWith',
                    'value'         => 'Vomitorio Corp',
                ),
                2 => array(
                    'attributeName' => 'officePhone',
                    'operatorType'  => 'startsWith',
                    'value'         => 5,
                ),
                3 => array(
                    'attributeName'        => 'billingAddress',
                    'relatedAttributeName' => 'street2',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Suite 101',
                ),
            );
            $compareStructure = '1 and 2 and 3';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchingOnMultipleValuesCustomFields()
        {
            $searchAttributes = array(
                'multipleIndustries' => array(
                    'values'    => array('Something'),
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new TestCustomFieldsModel(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'multipleIndustries',
                    'relatedAttributeName' => 'values',
                    'operatorType'         => 'oneOf',
                    'value'                => array('Something'),
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchingOnMultipleValuesCustomFieldsWithVariousNullOrEmptyValues()
        {
            $searchAttributes = array(
                'multipleIndustries' => array(
                    'values'    => array(0 => null),
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new TestCustomFieldsModel(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $this->assertEquals(array(), $metadata['clauses']);
            $this->assertEquals(null, $metadata['structure']);

            $searchAttributes = array(
                'multipleIndustries' => array(
                    'values'    => null,
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new TestCustomFieldsModel(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $this->assertEquals(array(), $metadata['clauses']);
            $this->assertEquals(null, $metadata['structure']);

            $searchAttributes = array(
                'multipleIndustries' => array(
                    'values'    => array(0 => ''),
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new TestCustomFieldsModel(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $this->assertEquals(array(), $metadata['clauses']);
            $this->assertEquals(null, $metadata['structure']);
        }

        public function testGetAdaptedMetadataUsingOrClause()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'name'          => 'Vomitorio Corp',
                'officePhone'   => '5',
                'billingAddress' => array(
                    'street1'    => null,
                    'street2'    => 'Suite 101',
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata(false);
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'name',
                    'operatorType'  => 'startsWith',
                    'value'         => 'Vomitorio Corp',
                ),
                2 => array(
                    'attributeName' => 'officePhone',
                    'operatorType'  => 'startsWith',
                    'value'         => 5,
                ),
                3 => array(
                    'attributeName'        => 'billingAddress',
                    'relatedAttributeName' => 'street2',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Suite 101',
                ),
            );

            $compareStructure = '(1 or 2 or 3)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchFormAttributesAreAdaptedProperly()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'ABName' => null,
                'anyA'   => null,
            );
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array();

            $compareStructure = null;
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Now put values in for the search.
            $searchAttributes = array(
                'ABName' => 'something',
                'anyA'   => 'nothing',
            );
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'aName',
                    'operatorType'  => 'startsWith',
                    'value'         => 'something',
                ),
                2 => array(
                    'attributeName' => 'bName',
                    'operatorType'  => 'startsWith',
                    'value'         => 'something',
                ),
                3 => array(
                    'attributeName'        => 'primaryA',
                    'relatedAttributeName' => 'name',
                    'operatorType'         => 'startsWith',
                    'value'                => 'nothing',
                ),
                4 => array(
                    'attributeName'        => 'secondaryA',
                    'relatedAttributeName' => 'name',
                    'operatorType'         => 'startsWith',
                    'value'                => 'nothing',
                ),
            );

            $compareStructure = '(1 or 2) and (3 or 4)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testOwnedItemsOnlyAttribute()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Try where the value for differentOperatorA is specified (Checkbox is checked)
            $searchAttributes = array(
                'differentOperatorA' => '1',
            );
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'primaryA',
                    'relatedAttributeName' => 'name',
                    'operatorType'  => 'startsWith',
                    'value'         => $super->id,
                ),
            );

            $compareStructure = '(1)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Now try where the value for differentOperatorA is not specified (Checkbox not checked)
            $searchAttributes = array(
                'differentOperatorA' => '',
            );
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses   = array();
            $compareStructure = null;
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testCustomOperatorTypeOnAttribute()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'differentOperatorB' => 'something',
            );
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'aName',
                    'operatorType'  => 'endsWith',
                    'value'         => 'something',
                ),
            );

            $compareStructure = '(1)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchFormDynamicAttributes()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'date__Date'          => array('type'         => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER,
                                                 'firstDate'  => '1991-03-04'),
                'dateTime__DateTime'  => array('type'         => MixedDateTypesSearchFormAttributeMappingRules::TYPE_TODAY),
                'dateTime2__DateTime' => array('value'        => null)
            );
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $todayDateTime      = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $today              = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                  $todayDateTime->getTimeStamp());
            $todayPlus7Days     = MixedDateTypesSearchFormAttributeMappingRules::calculateNewDateByDaysFromNow(7);
            $metadata           = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'date',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '1991-03-04',
                ),
                2 => array(
                    'attributeName'        => 'dateTime',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => DateTimeUtil::
                                              convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($today),
                ),
                3 => array(
                    'attributeName'        => 'dateTime',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => DateTimeUtil::
                                              convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today),
                ),
            );

            $compareStructure = '(1) and (2 and 3)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchFormDynamicAttributesBetweenAndOnDateSearch()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'date__Date'      => array('type'          => MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON,
                                                 'firstDate'   => '1991-03-04'),
                'date2__Date'      => array('type'          => MixedDateTypesSearchFormAttributeMappingRules::TYPE_BETWEEN,
                                                 'firstDate'   => '1991-03-05',
                                                 'secondDate'  => '1992-04-04'),
            );
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $metadata           = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'date',
                    'operatorType'         => 'equals',
                    'value'                => '1991-03-04',
                ),
                2 => array(
                    'attributeName'        => 'date2',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '1991-03-05',
                ),
                3 => array(
                    'attributeName'        => 'date2',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => '1992-04-04',
                ),
            );

            $compareStructure = '(1) and (2 and 3)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchFormDynamicAttributesTodayAndBeforeTodayDateSearch()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'date__Date'      => array('type'          => MixedDateTypesSearchFormAttributeMappingRules::TYPE_TODAY),
                'date2__Date'      => array('type'          => MixedDateTypesSearchFormAttributeMappingRules::TYPE_BEFORE_TODAY),
            );
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $metadata           = $metadataAdapter->getAdaptedMetadata();
            $today              = DateTimeCalculatorUtil::calculateNew(DateTimeCalculatorUtil::TODAY,
                                        new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'date',
                    'operatorType'         => 'equals',
                    'value'                => $today,
                ),
                2 => array(
                    'attributeName'        => 'date2',
                    'operatorType'         => 'lessThan',
                    'value'                => $today,
                ),
            );

            $compareStructure = '(1) and (2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchFormDynamicAttributesBetweenAndOnDateTimeSearch()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'dateTime__DateTime'      => array('type'          => MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON,
                                                   'firstDate'   => '1991-03-04'),
                'dateTime2__DateTime'      => array('type'          => MixedDateTypesSearchFormAttributeMappingRules::TYPE_BETWEEN,
                                                    'firstDate'   => '1991-03-05',
                                                    'secondDate'  => '1992-04-04'),
            );
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $metadata           = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'dateTime',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '1991-03-04 00:00:00',
                ),
                2 => array(
                    'attributeName'        => 'dateTime',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => '1991-03-04 23:59:59',
                ),
                3 => array(
                    'attributeName'        => 'dateTime2',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '1991-03-05 00:00:00',
                ),
                4 => array(
                    'attributeName'        => 'dateTime2',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => '1992-04-04 23:59:59',
                ),
            );

            $compareStructure = '(1 and 2) and (3 and 4)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }
    }
?>