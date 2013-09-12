<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Broad data provider tests that touch across different modules in the zurmo application.
     */
    class ModulesSearchFormTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSearchFormAnyAttributes()
        {
            $super = User::getByUsername('super');
            //Confirm the adaptedMetadata is correctly formed, when nothing is inputed for search.
            $fakePostData = array();
            $metadataAdapter = new SearchDataProviderMetadataAdapter(new AccountsSearchForm(new Account(false)),
                                    $super->id, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $this->assertEquals(array(), $searchAttributeData['clauses']);
            $this->assertEquals('', $searchAttributeData['structure']);

            //Now search on anyState
            $this->assertTrue(property_exists('AccountsSearchForm', 'anyState'));
            $this->assertFalse(property_exists('AccountsSearchForm', 'name'));
            $fakePostData = array(
                'anyState'  => 'Illinois',
                'anyStreet' => 'Thompson',
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(new AccountsSearchForm(new Account(false)),
                                    $super->id, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'billingAddress',
                    'relatedAttributeName' => 'state',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Illinois',
                ),
                2 => array(
                    'attributeName'        => 'shippingAddress',
                    'relatedAttributeName' => 'state',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Illinois',
                ),
                3 => array(
                    'attributeName'        => 'billingAddress',
                    'relatedAttributeName' => 'street1',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Thompson',
                ),
                4 => array(
                    'attributeName'        => 'shippingAddress',
                    'relatedAttributeName' => 'street1',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Thompson',
                ),
            );
            $compareStructure = '(1 or 2) and (3 or 4)';
            $this->assertEquals($compareClauses,   $searchAttributeData['clauses']);
            $this->assertEquals($compareStructure, $searchAttributeData['structure']);

            //testCheckBox 'any' search.
            $fakePostData = array(
                'anyOptOutEmail' => array('value' => '1'),
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(new AccountsSearchForm(new Account(false)),
                                    $super->id, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'primaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'equals',
                    'value'                => (bool)1,
                ),
                2 => array(
                    'attributeName'        => 'secondaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'equals',
                    'value'                => (bool)1,
                ),
            );
            $compareStructure = '(1 or 2)';
            $this->assertEquals($compareClauses,   $searchAttributeData['clauses']);
            $this->assertEquals($compareStructure, $searchAttributeData['structure']);

            //Now add other non 'any' attributes to the search to make sure it works ok.
            $fakePostData = array(
                'anyState' => 'Illinois',
                'anyOptOutEmail' => array('value' => '1'),
                'name' => 'ABC Company',
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(new AccountsSearchForm(new Account(false)),
                                    $super->id, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'billingAddress',
                    'relatedAttributeName' => 'state',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Illinois',
                ),
                2 => array(
                    'attributeName'        => 'shippingAddress',
                    'relatedAttributeName' => 'state',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Illinois',
                ),
                3 => array(
                    'attributeName'        => 'primaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'equals',
                    'value'                => (bool)1,
                ),
                4 => array(
                    'attributeName'        => 'secondaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'equals',
                    'value'                => (bool)1,
                ),
                5 => array(
                    'attributeName'        => 'name',
                    'operatorType'         => 'startsWith',
                    'value'                => 'ABC Company',
                ),
            );
            $compareStructure = '(1 or 2) and (3 or 4) and 5';
            $this->assertEquals($compareClauses,   $searchAttributeData['clauses']);
            $this->assertEquals($compareStructure, $searchAttributeData['structure']);

            //Test using an or clause between everything.
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata(false);
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'billingAddress',
                    'relatedAttributeName' => 'state',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Illinois',
                ),
                2 => array(
                    'attributeName'        => 'shippingAddress',
                    'relatedAttributeName' => 'state',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Illinois',
                ),
                3 => array(
                    'attributeName'        => 'primaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'equals',
                    'value'                => (bool)1,
                ),
                4 => array(
                    'attributeName'        => 'secondaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'equals',
                    'value'                => (bool)1,
                ),
                5 => array(
                    'attributeName'        => 'name',
                    'operatorType'         => 'startsWith',
                    'value'                => 'ABC Company',
                ),
            );
            $compareStructure = '((1 or 2) or (3 or 4) or 5)';
            $this->assertEquals($compareClauses,   $searchAttributeData['clauses']);
            $this->assertEquals($compareStructure, $searchAttributeData['structure']);
        }

        /**
         * @depends testSearchFormAnyAttributes
         */
        public function testSearchFormAnyOptOutEmail()
        {
            //get the super user here
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //test the anyOptOut value '' for search.
            $fakePostData = array(
                'anyOptOutEmail' => array('value' => ''),
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(new AccountsSearchForm(new Account(false)),
                                    $super->id, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();

            $compareStructure = '(1 or 2)';

            $this->assertEquals(array(), $searchAttributeData['clauses']);
            $this->assertEquals(null,    $searchAttributeData['structure']);

            //test the anyOptOut value '0' for search.
            $fakePostData = array(
                'anyOptOutEmail' => array('value' => '0'),
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(new AccountsSearchForm(new Account(false)),
                                    $super->id, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'primaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'equals',
                    'value'                => '0',
                ),
                2 => array(
                    'attributeName'        => 'primaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                ),
                3 => array(
                    'attributeName'        => 'secondaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'equals',
                    'value'                => '0',
                ),
                4 => array(
                    'attributeName'        => 'secondaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                ),
            );
            $compareStructure = '((1 or 2) or (3 or 4))';

            $this->assertEquals($compareClauses,   $searchAttributeData['clauses']);
            $this->assertEquals($compareStructure, $searchAttributeData['structure']);

            //test the anyOptOut value '1' for search.
            $fakePostData = array(
                'anyOptOutEmail' => array('value' => '1'),
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(new AccountsSearchForm(new Account(false)),
                                    $super->id, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'primaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'equals',
                    'value'                => (bool)1,
                ),
                2 => array(
                    'attributeName'        => 'secondaryEmail',
                    'relatedAttributeName' => 'optOut',
                    'operatorType'         => 'equals',
                    'value'                => (bool)1,
                ),
            );
            $compareStructure = '(1 or 2)';
            $this->assertEquals($compareClauses,   $searchAttributeData['clauses']);
            $this->assertEquals($compareStructure, $searchAttributeData['structure']);
        }
    }
?>
