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

    class SummationReportDataProvider extends ReportDataProvider
    {
        protected function isReportValidType()
        {
            if($this->report->getType() != Report::TYPE_SUMMATION)
            {
                throw new NotSupportedException();
            }
        }

        public function calculateTotalItemCount()
        {
        $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
        $sql                    = $this->makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter);
        echo $sql . "<BR>";
        $rows                   = R::getAll($sql);
        $count                  = count($rows);
        echo 'the count ' . $count . "<BR>";
        return $count;
        }

        public function getChartData()
        {

            $resultsData              = $this->fetchChartData();
            $firstSeriesAttributeName = $this->resolveChartFirstSeriesAttributeNameForReportResultsRowData();
            $firstRangeAttributeName  = $this->resolveChartFirstRangeAttributeNameForReportResultsRowData();
            $chartData = array();
            foreach ($resultsData as $data)
            {
                $chartData[] = array('value'        => $data->$firstRangeAttributeName, //todo: still need to resolve for currency for this.
                                     'displayLabel' => strval($data->$firstSeriesAttributeName));
            }
            return $chartData;
        }

        protected function fetchChartData()
        {
            //todO: $totalItemCount = $this->getTotalItemCount(); if too many rows over 100? then we should block or limit or something not sure...
            return $this->runQueryAndGetResolveResultsData(null, null);
        }

        public function resolveFirstSeriesLabel()
        {
            foreach($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstSeries)
                {
                    return $displayAttribute->label;
                }
            }
        }

        public function resolveFirstRangeLabel()
        {
            foreach($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstRange)
                {
                    return $displayAttribute->label;
                }
            }
        }

        protected function resolveChartFirstSeriesAttributeNameForReportResultsRowData()
        {
            foreach($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstSeries)
                {
                    return $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                }
            }
        }

        protected function resolveChartFirstRangeAttributeNameForReportResultsRowData()
        {
            foreach($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstRange)
                {
                    return $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                }
            }
        }
    }
?>