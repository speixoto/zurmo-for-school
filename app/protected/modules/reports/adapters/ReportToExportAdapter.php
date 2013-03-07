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
     * Helper class used to convert models into arrays
     */
    class ReportToExportAdapter
    {
        protected $reportResultsRowData;

        public function __construct(ReportResultsRowData $reportResultsRowData)
        {
            $this->reportResultsRowData = $reportResultsRowData;
        }

        public function getData()
        {
            $data   = array();
            foreach($this->reportResultsRowData->getDisplayAttributes() as $key => $displayAttribute)
            {
                $resolvedAttributeName = $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                //$data[] = $resolvedAttributeName;
                echo $resolvedAttributeName . ' battery ' . $displayAttribute->getDisplayElementType() . "\n";

                //get Type of adapter to use.
                //if viaSelect vs. not, that makes it easy too i think


                //shouldResolveValueFromModel($attributeAlias)
if( $displayAttribute->getDisplayElementType() != 'FullName' &&
    $displayAttribute->getDisplayElementType() != 'CurrencyValue')
{
                $className = $displayAttribute->getDisplayElementType() . 'RedBeanModelAttributeValueToExportValueAdapter'; //todo: move to factory
                $adapter = new $className($this->reportResultsRowData, $resolvedAttributeName);
                $adapter->resolveData($data);
            }
                //here we need adapters because full name for example does something special...
                //$data[] = $this->reportResultsRowData->$resolvedAttributeName;
            }
            return $data;
        }

        public function getHeaderData()
        {
            $data = array();
            foreach($this->reportResultsRowData->getDisplayAttributes() as $displayAttribute)
            {
                $data[] = $displayAttribute->getDisplayLabel(); //todo: this is wrong, because currency for example has 2 fields one is amount and other is
                //todo: currency code.... so we really we need to do the same thing.
            }
            return $data;
        }
        
        protected function resolveIdLabelToTitleCaseForExport($id) //todo: where is this used?
        {
            return mb_convert_case($id, MB_CASE_TITLE, "UTF-8");
        }        
    }
?>