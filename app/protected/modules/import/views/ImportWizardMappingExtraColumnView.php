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
     * Class for rendering an extraColumn type row. This is rendered when a user in the user interface
     * on the import mapping view, clicks 'Add Field'.
     */
    class ImportWizardMappingExtraColumnView extends View
    {
        protected $model;

        protected $mappingDataMetadata;

        /**
         * @param ImportWizardForm $model
         * @param array $mappingDataMetadata
         * @param array $mappableAttributeIndicesAndDerivedTypes
         */
        public function __construct(ImportWizardForm $model, $mappingDataMetadata, $mappableAttributeIndicesAndDerivedTypes)
        {
            assert('is_array($model->mappingData) && count($model->mappingData) > 0');
            assert('is_array($mappingDataMetadata)');
            assert('is_array($mappableAttributeIndicesAndDerivedTypes)');
            $this->model                                      = $model;
            $this->mappingDataMetadata                        = $mappingDataMetadata;
            $this->mappableAttributeIndicesAndDerivedTypes    = $mappableAttributeIndicesAndDerivedTypes;
        }

        public function render()
        {
            return $this->renderContent();
        }

        protected function renderContent()
        {
            $this->renderScripts();
            $mappingFormLayoutUtil                   = ImportToMappingFormLayoutUtil::make(
                                                       get_class($this->model),
                                                       new ZurmoActiveForm(),
                                                       $this->model->importRulesType,
                                                       $this->mappableAttributeIndicesAndDerivedTypes);
            $mappingDataMetadataWithRenderedElements = $this->resolveMappingDataMetadataWithRenderedElements(
                                                       $mappingFormLayoutUtil,
                                                       $this->mappingDataMetadata,
                                                       $this->model->firstRowIsHeaderRow,
                                                       $this->model->importRulesType,
                                                       $this->model->id);
            return MappingFormLayoutUtil::renderMappingDataMetadataWithRenderedElements(
                   $mappingDataMetadataWithRenderedElements);
        }

        /**
         * Renders special scripts required for displaying the view.  Renders scripts for dropdown styling and interaction.
         */
        protected function renderScripts()
        {
            DropDownUtil::registerScripts(CClientScript::POS_END);
        }

        /**
         * @param MappingFormLayoutUtil $mappingFormLayoutUtil
         * @param array $mappingDataMetadata
         * @param $firstRowIsHeaderRow
         * @param $importRulesType
         * @param int $id
         * @return array
         */
        protected function resolveMappingDataMetadataWithRenderedElements($mappingFormLayoutUtil, $mappingDataMetadata,
                                                                          $firstRowIsHeaderRow, $importRulesType, $id)
        {
            assert('$mappingFormLayoutUtil instanceof MappingFormLayoutUtil');
            assert('is_int($id)');
            $ajaxOnChangeUrl  = Yii::app()->createUrl("import/default/mappingRulesEdit", array('id' => $id));
            $metadata         = array();
            $metadata['rows'] = array();
            foreach ($mappingDataMetadata as $columnName => $mappingDataRow)
            {
                assert('$mappingDataRow["type"] == "extraColumn"');
                $row          = array();
                $row['cells'] = array();

                $firstCell  = $mappingFormLayoutUtil->renderAttributeAndColumnTypeContent(
                                                                       $columnName,
                                                                       $mappingDataRow['type'],
                                                                       $mappingDataRow['attributeIndexOrDerivedType'],
                                                                       $ajaxOnChangeUrl);
                $firstCell .= $mappingFormLayoutUtil->renderMappingRulesElements(
                                      $columnName,
                                      $mappingDataRow['attributeIndexOrDerivedType'],
                                      $importRulesType,
                                      $mappingDataRow['type'],
                                      array());

                $row['cells'][] = $firstCell;
                if ($firstRowIsHeaderRow)
                {
                    $row['cells'][] = '&#160;';
                }
                $row['cells'][] = '&#160;'; //Never any sample data for the extraColumn
                $metadata['rows'][] = $row;
            }
            return $metadata;
        }
    }
?>