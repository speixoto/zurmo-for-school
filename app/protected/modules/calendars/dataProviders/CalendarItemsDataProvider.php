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

    class CalendarItemsDataProvider extends CDataProvider
    {
        protected $savedCalendarSubscriptions;

        protected $moduleClassName;

        protected $savedCalendar;

        protected $startDate;

        protected $endDate;

        /**
         * @param SavedCalendarSubscriptions $savedCalendarSubscriptions
         * @param array $config
         */
        public function __construct(SavedCalendarSubscriptions $savedCalendarSubscriptions, array $config = array())
        {
            $this->savedCalendarSubscriptions = $savedCalendarSubscriptions;
            foreach ($config as $key => $value)
            {
                $this->$key = $value;
            }
        }

        /**
         * See the yii documentation. This function is made public for unit testing.
         * @return int|string
         */
        public function calculateTotalItemCount()
        {
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            $sql = $this->makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter, true);
            $count = ZurmoRedBean::getCell($sql);
            if ($count === null || empty($count))
            {
                $count = 0;
            }
            return $count;
        }

        /**
        * Override so when refresh is true it resets _rowsData
         */
        public function getData($refresh = false)
        {
           // if ($refresh)
           // {
           //     $this->_rowsData = null;
           // }
            return parent::getData($refresh);
        }

        /**
         * @return array
         */
        protected function fetchData()
        {
            return $this->resolveCalendarItems(); //todo: temporary. probably add back in limit and offiset?


            $offset = $this->resolveOffset();
            $limit  = $this->resolveLimit();
            if ($this->getTotalItemCount() == 0)
            {
                return array();
            }
            return $this->runQueryAndGetResolveResultsData($offset, $limit);
        }

        /**
         * See the yii documentation.
         * @return array
         */
        protected function fetchKeys()
        {
            $keys = array();
            foreach ($this->getData() as $data)
            {
                $keys[] = $data->getId();
            }
            return $keys;
        }

        protected function resolveCalendarItems()
        {
            //todo: check if cached _calendarItems. ?

            $calendarItems = array();
            foreach($this->savedCalendarSubscriptions->getMySavedCalendarsAndSelected() as $savedCalendarData)
            {
                if($savedCalendarData[1])
                {
                    $models = $this->resolveRedBeanModelsByCalendar($savedCalendarData[0]);
                    $this->resolveRedBeanModelsToCalendarItems($calendarItems, $models, $savedCalendarData[0]);
                }

            }
            foreach($this->savedCalendarSubscriptions->getSubscribedToSavedCalendarsAndSelected() as $savedCalendarData)
            {
                if($savedCalendarData[1])
                {
                    $models = $this->resolveRedBeanModelsByCalendar($savedCalendarData[0]);
                    $this->resolveRedBeanModelsToCalendarItems($calendarItems, $models, $savedCalendarData[0]);
                }
            }
            return $calendarItems;
        }

        protected function resolveRedBeanModelsByCalendar(SavedCalendar $calendar)
        {
            $models             = array();
            $report             = $this->makeReportBySavedCalendar($calendar);
            $reportDataProvider = new RowsAndColumnsReportDataProvider($report);
            $reportResultsRows  = $reportDataProvider->getData();
            foreach($reportResultsRows as $reportResultsRowData)
            {
                $models[] = $reportResultsRowData->getModel('attribute0'); //todo: even though it is 0 because we only have one displayAttribute, we should
                                                                           //todo: be pulling this from somewhere else instead of statically defining it here. probably...
            }

            //todo: need to set distinct? or we do we set it somewhere else? we need this otherwise we could have duplicate models...
            //todo: we don't want duplicate models in the results from the report data provider.we might have to just block has-many filtering?
            //todo: that might force it to always be distinct

            return $models;
        }

        protected function makeReportBySavedCalendar(SavedCalendar $savedCalendar)
        {
            $moduleClassName  = $savedCalendar->moduleClassName;
            $report           = SavedCalendarToReportAdapter::makeReportBySavedCalendar($savedCalendar);
            $existingFilters  = $report->getFilters();
            $startFilter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(), $report->getType());
            $startFilter->attributeIndexOrDerivedType = $savedCalendar->startAttributeName;
            $startFilter->value                       = $this->startDate;
            $startFilter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER;
            $report->addFilter($startFilter);
            $endFilter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(), $report->getType());
            if($savedCalendar->endAttributeName != null)
            {
                $endFilter->attributeIndexOrDerivedType = $savedCalendar->endAttributeName;
            }
            else
            {
                $endFilter->attributeIndexOrDerivedType = $savedCalendar->startAttributeName;
            }
            $endFilter->value                       = $this->endDate;
            $endFilter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_BEFORE;
            $report->addFilter($endFilter);
            if(count($existingFilters) > 0)
            {
                $report->setFiltersStructure($report->getFiltersStructure() .
                                             '(' . (count($existingFilters) + 1) . ' AND ' . ($existingFilters + 2) . ')');
            }
            else
            {
                $report->setFiltersStructure('1 AND 2');
            }
            $displayAttribute = new DisplayAttributeForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                    $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'id';
            $report->addDisplayAttribute($displayAttribute);
            return $report;
        }

        protected function resolveRedBeanModelsToCalendarItems(& $calendarItems, array $models, SavedCalendar $savedCalendar)
        {
            foreach($models as $model)
            {
                $calendarItems[] = CalendarUtil::makeCalendarItemByModel($model, $savedCalendar);
            }
        }

        public function getModuleClassName()
        {
            return $this->moduleClassName;
        }

        public function getStartDate()
        {
            return $this->startDate;
        }

        public function getEndDate()
        {
            return $this->endDate;
        }

        public function setModuleClassName($moduleClassName)
        {
            $this->moduleClassName = $moduleClassName;
        }

        public function setStartDateTime($startDateTime)
        {
            $this->startDateTime = $startDateTime;
        }

        public function setEndDateTime($endDateTime)
        {
            $this->endDateTime = $endDateTime;
        }

        public function getSavedCalendarSubscriptions()
        {
            return $this->savedCalendarSubscriptions;
        }

        public function setSavedCalendarSubscriptions($savedCalendarSubscriptions)
        {
            $this->savedCalendarSubscriptions = $savedCalendarSubscriptions;
        }
    }
?>