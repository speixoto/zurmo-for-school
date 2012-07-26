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
     * Walkthrough for the super user of dynamic search actions
     */
    class ZurmoDynamicSearchSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $this->setGetArray(array(   'viewClassName'      => 'AccountsSearchView',
                                        'modelClassName'     => 'Account',
                                        'formModelClassName' => 'AccountsSearchForm',
                                        'rowNumber'			 => 5));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/dynamicSearchAddExtraRow');
            $this->assertNotNull($content);

            //Test not passing validation post var
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/validateDynamicSearch', true);
            $this->assertEmpty($content);

            //Test form that validates
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'                          => 'search-form',
                                      'AccountsSearchForm'            => array()));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/validateDynamicSearch', true);
            $this->assertEmpty($content);

            //Test a form that does not validate because it is missing a field selection
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'               => 'search-form',
                                        'AccountsSearchForm' => array(
                                            'dynamicStructure' => '1',
                                            'dynamicClauses'   => array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => '')))));
            $content = $this->runControllerWithExitExceptionAndGetContent('zurmo/default/validateDynamicSearch');
            $this->assertEquals('{"AccountsSearchForm_dynamicClauses":["You must select a field for row 1"]}', $content);


            //Test a form that does not validate because it is missing a field selection
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'               => 'search-form',
                                        'AccountsSearchForm' => array(
                                            'dynamicStructure' => '1',
                                            'dynamicClauses'   => array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => 'name',
                                                      'name' => '')))));
            $content = $this->runControllerWithExitExceptionAndGetContent('zurmo/default/validateDynamicSearch');
            $this->assertEquals('{"AccountsSearchForm_dynamicClauses":["You must select a value for row 1"]}', $content);

            //Test a form that validates a dynamic cluase
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'               => 'search-form',
                                        'AccountsSearchForm' => array(
                                            'dynamicStructure' => '1',
                                            'dynamicClauses'   => array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => 'name',
                                                      'name' => 'someValue')))));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/validateDynamicSearch', true);
            $this->assertEmpty($content);

            //Test a form that does not validate recursive dynamic clause attributes
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'               => 'search-form',
                                        'AccountsSearchForm' => array(
                                            'dynamicStructure' => '1',
                                            'dynamicClauses'   => array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => 'name',
                                                      'contacts' => array(
                                                        'relatedData'   => true,
                                                        'firstName'		=> '',
                                                      ))))));
            $content = $this->runControllerWithExitExceptionAndGetContent('zurmo/default/validateDynamicSearch');
            $this->assertEquals('{"AccountsSearchForm_dynamicClauses":["You must select a value for row 1"]}', $content);

            //Test a form that does validate recursive dynamic clause attributes
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'               => 'search-form',
                                        'AccountsSearchForm' => array(
                                            'dynamicStructure' => '1',
                                            'dynamicClauses'   => array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => 'name',
                                                      'contacts' => array(
                                                        'relatedData'   => true,
                                                        'firstName'		=> 'Jason',
                                                      ))))));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/validateDynamicSearch', true);
            $this->assertEmpty($content);
        }

        public function testDynamicSearchAttributeInputTypes()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Test null attribute
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm',
                                        'rowNumber'                   => 5,
                                        'attributeIndexOrDerivedType' => ''));
            $this->resetPostArray();
            $this->runControllerWithExitExceptionAndGetContent('zurmo/default/dynamicSearchAttributeInput');

            //Test Account attribute
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm',
                                        'rowNumber'                   => 5,
                                        'attributeIndexOrDerivedType' => 'name'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/dynamicSearchAttributeInput');
            $this->assertNotNull($content);

            //Test AccountsSearchForm attribute
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm',
                                        'rowNumber'                   => 5,
                                        'attributeIndexOrDerivedType' => 'anyCountry'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/dynamicSearchAttributeInput');
            $this->assertNotNull($content);

            //todo: test additional types
        }
    }
?>