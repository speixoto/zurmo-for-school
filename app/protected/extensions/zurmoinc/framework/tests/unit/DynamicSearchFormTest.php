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

    class DynamicSearchFormTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        
        public function testValidateDynamicStructure()
        {
            
            /*
             * Test valide use of parenthesis
             */
            $searchForm = new AAASearchFormTestModel(new A());                                                          
            $searchForm->dynamicClauses   = array(
                                    array('structurePosition' => '1'),
                                    array('structurePosition' => '2'));
            $searchForm->dynamicStructure = ('(1 AND 2 )');
            $searchForm->validateDynamicStructure('dynamicStructure','');            
            $this->assertFalse($searchForm->hasErrors());
            
            
            /*
             * Test valide use of operators
             */
            $searchForm = new AAASearchFormTestModel(new A());                                                          
            $searchForm->dynamicClauses   = array(
                                    array('structurePosition' => '1'),
                                    array('structurePosition' => '2'));
            $searchForm->dynamicStructure = ('1 AND 2 ');
            $searchForm->validateDynamicStructure('dynamicStructure','');
            $this->assertFalse($searchForm->hasErrors());            
            $searchForm->dynamicStructure = ('1 and 2 ');
            $searchForm->validateDynamicStructure('dynamicStructure','');
            $this->assertFalse($searchForm->hasErrors());            
            $searchForm->dynamicStructure = ('1 oR 2 ');
            $searchForm->validateDynamicStructure('dynamicStructure','');
            $this->assertFalse($searchForm->hasErrors());            
            
            /*
             * Test invalide use of parenthesis
             */                       
            $searchForm = new AAASearchFormTestModel(new A());                                                          
            $searchForm->dynamicClauses   = array(
                                    array('structurePosition' => '1'),
                                    array('structurePosition' => '2'));
            $searchForm->dynamicStructure = ('1 ( 2');
            $searchForm->validateDynamicStructure('dynamicStructure','');
            $this->assertTrue($searchForm->hasErrors());            
            $searchForm->dynamicStructure = ('1 ) 2 )');
            $searchForm->validateDynamicStructure('dynamicStructure','');
            $this->assertTrue($searchForm->hasErrors());            
            $searchForm->dynamicStructure = ('1 ( 2 ())))');
            $searchForm->validateDynamicStructure('dynamicStructure','');
            $this->assertTrue($searchForm->hasErrors());            
            
            /*
             * Test if its used a number that isnt a structurePosition
             */            
            $searchForm = new AAASearchFormTestModel(new A());                                                          
            $searchForm->dynamicClauses   = array(
                                    array('structurePosition' => '1'),
                                    array('structurePosition' => '2'));
            $searchForm->dynamicStructure = ('1 AND 3');
            $searchForm->validateDynamicStructure('dynamicStructure',array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->dynamicStructure = ('1 AND 10');
            $searchForm->validateDynamicStructure('dynamicStructure',array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->dynamicStructure = ('1 AND -5');
            $searchForm->validateDynamicStructure('dynamicStructure',array());
            $this->assertTrue($searchForm->hasErrors());
            
            
            /*
             * Test invalid use of operators
             */            
            $searchForm = new AAASearchFormTestModel(new A());                                                          
            $searchForm->dynamicClauses   = array(
                                    array('structurePosition' => '1'),
                                    array('structurePosition' => '2'));
            $searchForm->dynamicStructure = ('1 + 2');
            $searchForm->validateDynamicStructure('dynamicStructure',array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->dynamicStructure = ('1 A* 2');
            $searchForm->validateDynamicStructure('dynamicStructure',array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->dynamicStructure = ('1 * 2');
            $searchForm->validateDynamicStructure('dynamicStructure',array());
            $this->assertTrue($searchForm->hasErrors());
            
            
            /*
             * Test other invalid expressions
             */            
            $searchForm = new AAASearchFormTestModel(new A());                                                          
            $searchForm->dynamicClauses   = array(
                                    array('structurePosition' => '1'),
                                    array('structurePosition' => '2'));
            $searchForm->dynamicStructure = ('1 OR OR 2');
            $searchForm->validateDynamicStructure('dynamicStructure',array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->dynamicStructure = ('1 AND ( ) 2');
            $searchForm->validateDynamicStructure('dynamicStructure',array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->dynamicStructure = ('OR 2 AND 1');
            $searchForm->validateDynamicStructure('dynamicStructure',array());
            $this->assertTrue($searchForm->hasErrors());
                                      
        }
        
    }
?>