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
     * Test class to specifically test nested or related model data searches.
     * @see ModelDataProviderUtilRecursiveDataTest

    Models and relations used in this class


                                III -> hasOne EEE
                                  |
                                  | CCC hasMany III
                                  | III hasOne  CCC
                                CCC -> hasOne EEE
                                  |
                                  | CCC hasMany BBB
         /-> hasOne EEE			  | BBB hasOne  CCC
         |						  |
         |						  |/---> BBB hasOne GGG -> hasOne EEE
         |						  ||
         |                        ||
         FFF <-hasOnehasMany ->  BBB <- manyMany -> DDD -> hasOne EEE
                                  |
          FFF hasOne  BBB         | BBB hasMany AAA
          BBB hasMany FFF         | AAA hasOne  BBB
                                  |
                                  |
                                 AAA --- hasOne HHH -> hasOne EEE
                                      HHH hasOneBelongsTo AAA
     */
    class SearchDataProviderMetadataAdapterForRecursiveSearchesTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }
//chekcin.
        //#0  remove reference to $processAsRelatedModelData
        //#1 make sure both tests run.
        //#2 refactor iteratives to recursive
        //#3 clean up in general.
        //#4 we should not be using all this static stuff in the adapter. much easier to put some things against porperties and not pass
        //$5 careful look for todos
//checheckin.


        //still need to do date time stuff which will be tricky since we have not recursed that part of the adapter.
        //should relatedAttributeName be nixed? or should we still allow for it to be prcessed in modelDataUtil? I think we still
        //allow because then peopel can use it for convenience. plus it is tested...

        //test starting at hasMany as first nest also manyMany as first nest. also do as firsts and seconds...
        //test id fields here and in the modelDataproviderutilrecurisevedata
        //also means since we added support for relatedAttributeName... we should test, or should we remove that
        //support since it is redundant what is the mont?

        //on modeldataproviderUtilRecursiveData - 2 calls to hasOne eee but from different branches...
        //also refactor to use non static since we can reduce parameters beign passed


        //nested normal model fields like a custom field
        //nested normal model fields like a multi-select custom field

        //search form specific, like date fields since you can do betweens etc.
        //search form specials like owner (owned by field)

        //populateClausesAndStructureForAttributeWithRelatedModelData, test nesting oneOf custom field...
        //if you are 4 levels deep and have relatedAttributeName because of custom data or something does this work not
        //only here but also in the conversion to sql? need to add tests there as well

        public function testGetAdaptedMetadataForAttributesAcrossRelations()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'aaaMember' => 'Vomitorio Corp',
                'bbb' => array(
                    'bbbMember'  => 'bbbMemberValue',
                    'ccc'    => array(
                        'cccMember' => 'cccMemberValue',
                        'eee' => array(
                            'eeeMember' => 'eeeMemberValue',
                       ),
                        'iii'    => array(
                            'eee' => array(
                                'eeeMember' => 'eeeMemberValue',
                            )
                        )
                    )
                )
            );

            $searchAttributes2 = array(
                'aaaMember' => 'Vomitorio Corp',
                array(	'relationName'  => 'bbb',
                        'relationData' => array(
                            'bbbMember'  => 'bbbMemberValue',
                            array(	'relationName'  => 'ccc',
                                    'relationData' => array(
                                        'cccMember' => 'cccMemberValue',
                                        array(	'relationName'  => 'eee',
                                                'relationData' => array(
                                                    'eeeMember' => 'eeeMemberValue'
                                                )
                                        ),
                                        array(	'relationName'  => 'iii',
                                                'relationData' => array(
                                                    array(	'relationName'  => 'eee',
                                                            'relationData' => array(
                                                                'eeeMember' => 'eeeMemberValue'
                                                            )
                                                    ),
                                                )
                                        ),
                                )
                            ),
                        )
                )
            );


            $searchAttributes3 = array();
            $searchAttributes3['aaaMember']              = 'Vomitorio Corp';
            $searchAttributes3['bbb']['relatedData']     = true;
            $searchAttributes3['bbb']['bbbMember']       = 'bbbMemberValue';


            $searchAttributes4 = array(
                'aaaMember' => 'Vomitorio Corp',
                'bbb' => array(
                    'relatedData' => true,
                    'bbbMember'  => 'bbbMemberValue',
                    'ccc'    => array(
                        'relatedData' => true,
                        'cccMember' => 'cccMemberValue',
                        'eee' => array(
                            'relatedData' => true,
                            'eeeMember' => 'eeeMemberValue',
                       ),
                        'iii'    => array(
                           'relatedData' => true,
                            'eee' => array(
                                'relatedData' => true,
                                'eeeMember' => 'eeeMemberValue',
                            )
                        )
                    )
                )
            );

            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAA(false),
                1,
                $searchAttributes4
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                //Standard attribute on model
                1 => array(
                    'attributeName' => 'aaaMember',
                    'operatorType'  => 'startsWith',
                    'value'         => 'Vomitorio Corp',
                ),
                //Standard attribute on related model
                2 => array(
                    'attributeName'        => 'bbb',
                        'relatedModelData'	=> array(
                            'attributeName' 	=> 'bbbMember',
                            'operatorType'	    => 'startsWith',
                            'value'             => 'bbbMemberValue',
                        ),
                ),
                //Standard attribute on related related model
                3 => array(
                    'attributeName'	=> 'bbb',
                    'relatedModelData' => array(
                        'attributeName' 	=> 'ccc',
                            'relatedModelData'	=> array(
                                'attributeName' 	=> 'cccMember',
                                'operatorType'	    => 'startsWith',
                                'value'             => 'cccMemberValue',
                            ),
                    ),
                ),
                //Standard attribute on related related related model
                4 => array(
                    'attributeName'	=> 'bbb',
                    'relatedModelData' => array(
                        'attributeName' 	=> 'ccc',
                            'relatedModelData'	=> array(
                                'attributeName' 	    => 'eee',
                                    'relatedModelData'	=> array(
                                        'attributeName' 	=> 'eeeMember',
                                        'operatorType'	        => 'startsWith',
                                        'value'                 => 'eeeMemberValue',
                                    ),
                            ),
                    ),
                ),
                5 => array(
                    'attributeName'	=> 'bbb',
                    'relatedModelData' => array(
                        'attributeName' 	=> 'ccc',
                            'relatedModelData'	=> array(
                                'attributeName' 	=> 'iii',
                                    'relatedModelData'	=> array(
                                        'attributeName' 	    => 'eee',
                                            'relatedModelData'	=> array(
                                                'attributeName' 	=> 'eeeMember',
                                                'operatorType'	        => 'startsWith',
                                                'value'                 => 'eeeMemberValue',
                                            ),
                                    ),
                            ),
                    ),
                ),
            );
//echo "<pre>";
//print_r($metadata);
//echo "</pre>";
            $compareStructure = '1 and 2 and 3 and 4 and 5';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }


        /**
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
            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
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
                    'operatorType'  => 'doesNotEqual',
                    'value'         => (bool)1,
                ),
                2 => array(
                    'attributeName'        => 'a',
                    'relatedAttributeName' => 'a',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1,
                ),
            );
            $compareStructure = '1 and 2';
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
                    'operatorType'  => 'doesNotEqual',
                    'value'         => (bool)1,
                ),
                2 => array(
                    'attributeName'        => 'a',
                    'relatedAttributeName' => 'a',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1,
                ),
            );
            $compareStructure = '1 and 2';
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

            $compareStructure = '1 or 2 or 3';
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
            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
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
            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
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
            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
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
            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
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
            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
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
            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
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
            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
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
            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
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
        **/
    }
?>