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

    class LeadImportSanitizerUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
        }

        public function testSanitizeValueBySanitizerTypesForLeadStateTypeThatIsRequired()
        {
            $contactStates = ContactState::getAll();
            $this->assertEquals(6, count($contactStates));

            //Test a required contact state with no value or default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('type' => 'importColumn', 'mappingRulesData' => array(
                                               'DefaultLeadStateIdMappingRuleForm' =>
                                               array('defaultStateId' => null)));
            $sanitizerUtilTypes        = LeadStateAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Contact', null, null, 'column_0',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'Contact - The status is required.  Neither a value nor a default was specified.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a required contact state with a valid value, and a default value. The valid value should come through.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('type' => 'importColumn', 'mappingRulesData' => array(
                                               'DefaultLeadStateIdMappingRuleForm' =>
                                               array('defaultStateId' => $contactStates[0]->id)));
            $sanitizerUtilTypes        = LeadStateAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Contact', null, $contactStates[1]->id, 'column_0',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($contactStates[1], $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required contact state with no value, and a default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('type' => 'importColumn', 'mappingRulesData' => array(
                                               'DefaultLeadStateIdMappingRuleForm' =>
                                               array('defaultStateId' => $contactStates[0]->id)));
            $sanitizerUtilTypes        = LeadStateAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Contact', null, null, 'column_0',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($contactStates[0], $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required contact state with a value that is invalid
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('type' => 'importColumn', 'mappingRulesData' => array(
                                               'DefaultLeadStateIdMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = LeadStateAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Contact', null, 'somethingnotright', 'column_0',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'Contact - Status specified does not exist.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a required contact state with a state that is for leads, not contacts.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('type' => 'importColumn', 'mappingRulesData' => array(
                                               'DefaultLeadStateIdMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = LeadStateAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Contact', null, $contactStates[5]->id, 'column_0',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'Contact - Status specified is invalid.';
            $this->assertEquals($compareMessage, $messages[0]);
        }
    }
?>