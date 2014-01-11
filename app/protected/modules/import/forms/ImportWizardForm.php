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

    /**
     * This form works with the import wizard views to collect data from the user interface and validate it.
     * MappingRules data is not validated using this form, however the mapping rules data is collected and stored
     * in the mappingData array.
     * @see MappingRuleFormAndElementTypeUtil::validateMappingRuleForms
     */
    class ImportWizardForm extends ConfigurableMetadataModel
    {
        /**
         * Set externally as the import model id when available;
         * @var integer
         */
        public $id;

        /**
         * @var string
         */
        public $importRulesType;

        /**
         * Array of file upload specific information including name, type, and size.
         * @var array
         */
        public $fileUploadData;

        /**
         * String of delimiter character to use for each row column.  Defaults to ,
         * @var string
         */
        public $rowColumnDelimiter = ','; // Not Coding Standard

        /**
         * String of the enclosure character to use for each row column.  Defaults to "
         * @var string
         */
        public $rowColumnEnclosure = '"';

        /**
         * True/false whether the import file's first row is a header row or not.
         * @var boolean
         */
        public $firstRowIsHeaderRow;

        /**
         * Object containing information on how to setup permissions for the new models that are created during the
         * import process.
         * @var object ExplicitReadWriteModelPermissions
         * @see ExplicitReadWriteModelPermissions
         */
        protected $explicitReadWriteModelPermissions;

        /**
         * Mapping data array indexed by column name containing the mapping rules, attribute index or derived type, and
         * type information.
         * @var array
         */
        public $mappingData;

        public function rules()
        {
            return array(
                //default validators must come before the required validators
                array('rowColumnDelimiter',  'default', 'value' => ',', 'setOnEmpty' => true), // Not Coding Standard
                array('rowColumnEnclosure',  'default', 'value' => '"', 'setOnEmpty' => true),
                array('importRulesType',     'required'),
                array('rowColumnDelimiter',  'required'),
                array('rowColumnEnclosure',  'required'),
                array('fileUploadData',      'type', 'type' => 'array'),
                array('rowColumnDelimiter',  'type', 'type' => 'string'),
                array('rowColumnEnclosure',  'type', 'type' => 'string'),

                array('firstRowIsHeaderRow', 'boolean'),
                array('mappingData',         'type', 'type' => 'array'),
                array('newPassword',         'validateMappingData', 'on'   => 'saveMappingData'),
            );
        }

        public function attributeLabels()
        {
            return array(
                'importRulesType'                   => Zurmo::t('ImportModule', 'Module To Import To'),
                'fileUploadData'                    => Zurmo::t('ImportModule', 'File Upload Data'),
                'rowColumnDelimiter'                => Zurmo::t('ImportModule', 'Delimiter'),
                'rowColumnEnclosure'                => Zurmo::t('ImportModule', 'Qualifier'),
                'firstRowIsHeaderRow'               => Zurmo::t('ImportModule', 'First Row is Header Row'),
                'explicitReadWriteModelPermissions' => Zurmo::t('ImportModule', 'Model Permissions'),
                'mappingData'                       => Zurmo::t('ImportModule', 'Mapping Data'),
            );
        }

        public function getExplicitReadWriteModelPermissions()
        {
            return $this->explicitReadWriteModelPermissions;
        }

        public function setExplicitReadWriteModelPermissions($explicitReadWriteModelPermissions)
        {
            assert($explicitReadWriteModelPermissions instanceof ExplicitReadWriteModelPermissions); // Not Coding Standard
            $this->explicitReadWriteModelPermissions = $explicitReadWriteModelPermissions;
        }

        /**
         * Validation used in the saveMappingData scenario to make sure the mapping data is correct based on
         * user input. Runs several different validations on the data.  This does not validate the validity of the
         * mapping rules data itself. That is done seperately.
         * @see MappingRuleFormAndElementTypeUtil::validateMappingRuleForms
         * @param string $attribute
         * @param array $params
         */
        public function validateMappingData($attribute, $params)
        {
            assert('$this->importRulesType != null');
            assert('$this->mappingData != null');
            $atLeastOneAttributeMappedOrHasRules   = false;
            $attributeMappedOrHasRulesMoreThanOnce = false;
            $mappedAttributes                      = array();
            $importRulesClassName                  = ImportRulesUtil::
                                                     getImportRulesClassNameByType($this->importRulesType);
            foreach ($this->mappingData as $columnName => $data)
            {
                if ($data['attributeIndexOrDerivedType'] != null)
                {
                    $atLeastOneAttributeMappedOrHasRules = true;
                    if (in_array($data['attributeIndexOrDerivedType'], $mappedAttributes))
                    {
                        $attributeMappedOrHasRulesMoreThanOnce = true;
                    }
                    else
                    {
                        $mappedAttributes[] = $data['attributeIndexOrDerivedType'];
                    }
                }
            }
            if ($attributeMappedOrHasRulesMoreThanOnce)
            {
                $this->addError('mappingData', Zurmo::t('ImportModule', 'You can only map each field once.'));
            }
            if (!$atLeastOneAttributeMappedOrHasRules)
            {
                $this->addError('mappingData', Zurmo::t('ImportModule', 'You must map at least one of your import columns.'));
            }
            $mappedAttributeIndicesOrDerivedAttributeTypes = ImportMappingUtil::
                                                             getMappedAttributeIndicesOrDerivedAttributeTypesByMappingData(
                                                             $this->mappingData);
            $requiredAttributeCollection                   = $importRulesClassName::
                                                             getRequiredAttributesCollectionNotIncludingReadOnly();
            $mappedAttributeImportRulesCollection          = AttributeImportRulesFactory::makeCollection(
                                                             $this->importRulesType,
                                                             $mappedAttributeIndicesOrDerivedAttributeTypes);
            if (!ImportRulesUtil::areAllRequiredAttributesMappedOrHaveRules($requiredAttributeCollection,
                                                                            $mappedAttributeImportRulesCollection))
            {
                $attributesLabelContent = null;
                foreach ($requiredAttributeCollection as $noteUsed => $attributeData)
                {
                    if ($attributesLabelContent != null)
                    {
                        $attributesLabelContent .= ', ';
                    }
                    $attributesLabelContent .= $attributeData['attributeLabel'];
                }
                $this->addError('mappingData', Zurmo::t('ImportModule', 'All required fields must be mapped or added: {attributesLabelContent}',
                                                      array('{attributesLabelContent}' => $attributesLabelContent)));
            }
            try
            {
                ImportRulesUtil::checkIfAnyAttributesAreDoubleMapped($mappedAttributeImportRulesCollection);
            }
            catch (ImportAttributeMappedMoreThanOnceException $e)
            {
                $this->addError('mappingData', Zurmo::t('ImportModule', 'The following field is mapped more than once. {message}',
                                               array('{message}' => $e->getMessage())));
            }
        }

        /**
         * Validation used when a file is uploaded on the import section.
         * It validates that a column delimeter value was introduced on the form.
         * @return bool If the value is not empty, returns true, otherwise returns false.
         */
        public function validateRowColumnDelimeterIsNotEmpty()
        {
            if (empty($this->rowColumnDelimiter))
            {
                return false;
            }
            return true;
        }

        /**
         * Validation used when a file is uploaded on the import section.
         * It validates that a column enclosure value was introduced on the form.
         * @return bool If the value is not empty, returns true, otherwise returns false.
         */
        public function validateRowColumnEnclosureIsNotEmpty()
        {
            if (empty($this->rowColumnEnclosure))
            {
                return false;
            }
            return true;
        }
    }
?>