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
     * Helper class used to convert models into arrays
     */
    class ReportToExportAdapter
    {
        //TODO: @sergio: reporttoexportadaptertest need to adapt this changes
        
        protected $dataProvider;
        
        protected $dataForExport;
        
        protected $headerData;
        
        protected $data;
                
        protected $report;

        public function __construct(ReportDataProvider $dataProvider, Report $report)
        {
            $this->dataProvider         = $dataProvider;
            $this->report               = $report;
            $this->dataForExport        = ExportUtil::getDataForExport($this->dataProvider);
            $this->makeData();
        }
        
        public function getData()
        {    
            $returnHeaderData = array();
            if(get_class($this->dataProvider) == 'MatrixReportDataProvider')
            {                
                $leadingHeaders = $this->getLeadingHeadersDataFromMatrixReportDataProvider();
                foreach ($leadingHeaders as $header)
                {
                    $returnHeaderData[] = $header;
                }                
            }                    
            return array_merge($returnHeaderData, $this->data);
        }

        public function getHeaderData()
        {                                       
            return $this->headerData;
        }

        protected function makeData()
        {            
            if(get_class($this->dataProvider) == 'MatrixReportDataProvider')
            {
                $this->makeDataFromMatrixReportDataProvider();
            }                                        
            else                  
            {
                foreach ($this->dataForExport as $reportResultsRowData)
                {
                    $data             = array();
                    $this->headerData = array();                
                    foreach ($reportResultsRowData->getDisplayAttributes() as $key => $displayAttribute)
                    {                        
                        $resolvedAttributeName = $displayAttribute->resolveAttributeNameForGridViewColumn($key);                    
                        $className             = $this->resolveExportClassNameForReportToExportValueAdapter($displayAttribute);
                        $params                = array();
                        $this->resolveParamsForCurrencyTypes($displayAttribute, $params);
                        $adapter = new $className($reportResultsRowData, $resolvedAttributeName, $params);
                        $adapter->resolveData($data);
                        $adapter->resolveHeaderData($this->headerData);
                    }
                    $this->data[] = $data;                
                }
            }                        
        }
        
        protected function makeDataFromMatrixReportDataProvider()
        {                             
            $data             = array(); 
            $this->headerData = array();
            $exportClassNames = $this->getExportClassNamesByAttributeNameArray();
            foreach ($this->dataForExport as $reportResultsRowData)
            {                                  
                $line   = array();
                $header = array();            
                $key = $this->dataProvider->getXAxisGroupByDataValuesCount();
                $column = array();
                foreach ($this->dataProvider->getDisplayAttributesThatAreYAxisGroupBys() as $displayAttribute)
                {                           
                    $header[]           = $displayAttribute->label;
                    $className          = $this->resolveExportClassNameForReportToExportValueAdapter(
                                            $displayAttribute);
                    $attributeName      = MatrixReportDataProvider::resolveHeaderColumnAliasName(
                                            $displayAttribute->columnAliasName);
                    $params             = array();                                                                                                                
                    $line[]             = $displayAttribute->resolveValueAsLabelForHeaderCell(
                                            $reportResultsRowData->$attributeName);
                }                                     
                foreach ($this->dataProvider->makeXAxisGroupingsForColumnNamesData() as $groupings)
                {                    
                    foreach ($groupings as $key => $column)
                    {                        
                        $header[]     = $reportResultsRowData->getAttributeLabel($column);
                        $params       = array();                                                  
                        $className    = $exportClassNames[$column];
                        $adapter      = new $className($reportResultsRowData, $column, $params);
                        $adapter->resolveData($line);                                                                                                                              
                    }
                }                                                                                                                   
                $data[] = $line;                                
            }                  
            $this->data = array_merge(array($header), $data);
        }
               
        protected function getLeadingHeadersDataFromMatrixReportDataProvider()
        {
            //TODO: @sergio: add test for cover and doc
            $leadingHeaders             = $this->dataProvider->makeAxisCrossingColumnCountAndLeadingHeaderRowsData();
            $previousGroupByValuesCount = 1;
            $headerData = array();
            for ($i = 0; $i < count($leadingHeaders['rows']); $i++)
            {
                $headerRow = array();                
                for ($j = 0; $j < $leadingHeaders['axisCrossingColumnCount']; $j++)
                {
                    $headerRow[] = null;
                }
                for ($k = 0; $k < $previousGroupByValuesCount; $k++)
                {
                    foreach ($leadingHeaders['rows'][$i]['groupByValues'] as $value)
                    {
                        for ($l = 0; $l < $leadingHeaders['rows'][$i]['colSpan']; $l++)
                        {
                            $headerRow[] = $value;                                                
                        }
                    }
                }
                $previousGroupByValuesCount = count($leadingHeaders['rows'][$i]['groupByValues']);
                $headerData[] = $headerRow;
            }            
            return $headerData;
        }

        protected function resolveExportClassNameForReportToExportValueAdapter(DisplayAttributeForReportForm $displayAttribute)
        {
            $displayElementType = $displayAttribute->getDisplayElementType();
            if (@class_exists($displayElementType . 'ForReportToExportValueAdapter'))
            {
                return $displayElementType . 'ForReportToExportValueAdapter';
            }
            else
            {
                return $displayElementType . 'RedBeanModelAttributeValueToExportValueAdapter';
            }
        }

        protected function resolveParamsForCurrencyTypes(DisplayAttributeForReportForm $displayAttribute, & $params)
        {
            assert('is_array($params)');
            if ($displayAttribute->isATypeOfCurrencyValue())
            {
                $params['currencyValueConversionType'] = $this->report->getCurrencyConversionType();
                $params['spotConversionCurrencyCode']  = $this->report->getSpotConversionCurrencyCode();
                $params['fromBaseToSpotRate']          = $this->report->getFromBaseToSpotRate();
            }
        }
        
        protected function getExportClassNamesByAttributeNameArray()
        {
            $array = array();
            $attributeKey = 0;
            for ($i = 0; $i < $this->dataProvider->getXAxisGroupByDataValuesCount(); $i++)
            {
                foreach ($this->dataProvider->resolveDisplayAttributes() as $displayAttribute)
                {
                    if (!$displayAttribute->queryOnly)
                    {
                        $attributeName         = 
                                MatrixReportDataProvider::resolveColumnAliasName($attributeKey);
                        $array[$attributeName] = 
                                $this->resolveExportClassNameForReportToExportValueAdapter($displayAttribute);
                        $attributeKey++;
                    }
                }
            }              
            return $array;
        }
    }
?>