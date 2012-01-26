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
     * Utility class for adapting mappingData from the import into a mapping view ready array. This would include
     * sample values and header values if specified.
     */
    class ImportWizardMappingViewUtil
    {
        /**
         * @param array   $mappingData
         * @param object  $sample RedBean_OODBBean
         * @param mixed   $headerRow
         */
        public static function resolveMappingDataForView($mappingData, RedBean_OODBBean $sample, $headerRow = null)
        {
            assert('is_array($mappingData)');
            assert('$headerRow == null || is_array($headerRow)');
            foreach ($mappingData as $columnName => $columnData)
            {
                if ($columnData['type'] == 'importColumn')
                {
                    if ($headerRow != null)
                    {
                        $mappingData[$columnName]['headerValue'] = $headerRow[$columnName];
                        $mappingData[$columnName]['sampleValue'] = $sample->$columnName;
                    }
                    else
                    {
                        $mappingData[$columnName]['sampleValue'] = $sample->$columnName;
                    }
                }
                else
                {
                    if ($headerRow != null)
                    {
                        $mappingData[$columnName]['headerValue'] = null;
                    }
                    $mappingData[$columnName]['sampleValue']     = null;
                }
            }
            return $mappingData;
        }

        /**
         * Given a column name, make the basic mapping data array with all the sub array indexes present.
         * @param string $columnName
         */
        public static function makeExtraColumnMappingDataForViewByColumnName($columnName)
        {
            assert('is_string($columnName)');
            return array(
                $columnName => array('type'                        => 'extraColumn',
                                     'attributeIndexOrDerivedType' => null,
                                     'mappingRulesData'            => null,
                                     'headerValue'                 => null,
                                     'sampleValue'                 => null));
        }
    }
?>