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

    class Report
    {
        const TYPE_ROWS_AND_COLUMNS           = 'RowsAndColumns';

        const TYPE_SUMMATION                  = 'Summation';

        const TYPE_MATRIX                     = 'Matrix';

        const CURRENCY_CONVERSION_TYPE_ACTUAL = 1;

        const CURRENCY_CONVERSION_TYPE_BASE   = 2;

        const CURRENCY_CONVERSION_TYPE_SPOT   = 3;

        private $description;

        private $explicitReadWriteModelPermissions;

        /**
         * Id of the saved report if it has already been saved
         * @var integer
         */
        private $id;

        private $moduleClassName;

        private $name;

        private $owner;

        private $type;

        private $filtersStructure;

        private $filters                    = array();

        private $orderBys                   = array();

        private $displayAttributes          = array();

        private $drillDownDisplayAttributes = array();

        private $groupBys                   = array();

        private $chart;

        private $currencyConversionType;

        private $spotConversionCurrencyCode;

        public static function getTypeDropDownArray()
        {
            return array(self::TYPE_ROWS_AND_COLUMNS  => Yii::t('Default', 'Rows and Columns'),
                         self::TYPE_SUMMATION         => Yii::t('Default', 'Summation'),
                         self::TYPE_MATRIX            => Yii::t('Default', 'Matrix'),);
        }

        /**
         * Based on the current user, return the reportable modules and thier display labels.  Only include modules
         * that the user has a right to access.
         * @return array of module class names and display labels.
         */
        public static function getReportableModulesAndLabelsForCurrentUser()
        {
            $moduleClassNamesAndLabels = array();
            $modules = Module::getModuleObjects();
            foreach (self::getReportableModulesClassNamesCurrentUserHasAccessTo() as $moduleClassName)
            {
                $moduleClassNamesAndLabels[$moduleClassName] = $moduleClassName::getModuleLabelByTypeAndLanguage('Plural');
            }
            return $moduleClassNamesAndLabels;
        }

        public static function getReportableModulesClassNamesCurrentUserHasAccessTo()
        {
            $moduleClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                if($module::isReportable())
                {
                    if (RightsUtil::canUserAccessModule(get_class($module), Yii::app()->user->userModel))
                    {
                        $moduleClassNames[] = get_class($module);
                    }
                }
            }
            return $moduleClassNames;
        }

        public function getModuleClassName()
        {
            return $this->moduleClassName;
        }

        public function setModuleClassName($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $this->moduleClassName = $moduleClassName;
        }

        public function getDescription()
        {
            return $this->description;
        }

        public function setDescription($description)
        {
            assert('is_string($description)');
            $this->description = $description;
        }

        public function setFiltersStructure($filtersStructure)
        {
            assert('is_string($filtersStructure)');
            $this->filtersStructure = $filtersStructure;
        }

        public function getFiltersStructure()
        {
            return $this->filtersStructure;
        }

        public function getId()
        {
            return $this->id;
        }

        public function setId($id)
        {
            assert('is_int($id)');
            $this->id = $id;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setName($name)
        {
            assert('is_string($name)');
            $this->name = $name;
        }

        public function getType()
        {
            return $this->type;
        }

        public function setType($type)
        {
            assert('is_string($type)');
            $this->type = $type;
        }

        public function getCurrencyConversionType()
        {
            return $this->currencyConversionType;
        }

        public function setCurrencyConversionType($currencyConversionType)
        {
            assert('is_int($currencyConversionType)');
            $this->currencyConversionType = $currencyConversionType;
        }

        public function getSpotConversionCurrencyCode()
        {
            return $this->spotConversionCurrencyCode;
        }

        public function setSpotConversionCurrencyCode($spotConversionCurrencyCode)
        {
            assert('is_string($spotConversionCurrencyCode)');
            $this->spotConversionCurrencyCode = $spotConversionCurrencyCode;
        }

        public function isNew()
        {
            //todo:
            return true;
        }

        public function getOwner()
        {
            if($this->owner == null)
            {
                $this->owner = Yii::app()->user->userModel;
            }
            return $this->owner;
        }

        public function setOwner(User $owner)
        {
            $this->owner = $owner;
        }

        public function getFilters()
        {
            return $this->filters;
        }

        public function addFilter(FilterForReportForm $filter)
        {
            $this->filters[] = $filter;
        }

        public function removeAllFilters()
        {
            $this->filters   = array();
        }

        public function getGroupBys()
        {
            return $this->groupBys;
        }

        public function addGroupBy(GroupByForReportForm $groupBy)
        {
            $this->groupBys[] = $groupBy;
        }

        public function removeAllGroupBys()
        {
            $this->groupBys   = array();
        }

        public function getOrderBys()
        {
            return $this->orderBys;
        }

        public function addOrderBy(OrderByForReportForm $orderBy)
        {
            $this->orderBys[] = $orderBy;
        }

        public function removeAllOrderBys()
        {
            $this->orderBys   = array();
        }

        public function getDisplayAttributes()
        {
            return $this->displayAttributes;
        }

        public function addDisplayAttribute(DisplayAttributeForReportForm $displayAttribute)
        {
            $this->displayAttributes[] = $displayAttribute;
        }

        public function removeAllDisplayAttributes()
        {
            $this->displayAttributes   = array();
        }

        public function getDrillDownDisplayAttributes()
        {
            return $this->drillDownDisplayAttributes;
        }

        public function addDrillDownDisplayAttribute(DrillDownDisplayAttributeForReportForm $drillDownDisplayAttribute)
        {
            $this->drillDownDisplayAttributes[] = $drillDownDisplayAttribute;
        }

        public function removeAllDrillDownDisplayAttributes()
        {
            $this->drillDownDisplayAttributes   = array();
        }

        public function getChart()
        {
            if($this->chart == null)
            {
                $this->chart     = new ChartForReportForm();
            }
            return $this->chart;
        }

        public function setChart(ChartForReportForm $chart)
        {
            $this->chart = $chart;
        }

        public function getExplicitReadWriteModelPermissions()
        {
            if($this->explicitReadWriteModelPermissions == null)
            {
                $this->explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            }
            return $this->explicitReadWriteModelPermissions;
        }

        public function setExplicitReadWriteModelPermissions(ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            $this->explicitReadWriteModelPermissions = $explicitReadWriteModelPermissions;
        }

        public function areRuntimeFiltersPresent()
        {
            foreach($this->getFilters() as $filter)
            {
                if($filter->availableAtRunTime)
                {
                    return true;
                }
            }
            return false;
        }

        public function getDisplayAttributeIndex($attribute)
        {
            foreach($this->displayAttributes as $key => $displayAttribute)
            {
                if($attribute == $displayAttribute->attributeIndexOrDerivedType)
                {
                    return $key;
                }
            }
            return null;
        }

        public function resolveGroupBysAsFilters(Array $getData)
        {
            $newStartingStructurePosition = count($this->filters) + 1;
            $structure = null;
            foreach($this->getGroupBys() as $groupBy)
            {
                $index = ReportResultsRowData::resolveDataParamKeyForDrillDown($groupBy->attributeIndexOrDerivedType);
                $value = $getData[$index];
                $filter                              = new FilterForReportForm($groupBy->getModuleClassName(),
                                                       $groupBy->getModelClassName(),
                                                       $this->type);
                $filter->attributeIndexOrDerivedType = $groupBy->attributeIndexOrDerivedType;
                //todO: not sure how this will work when doing group modifiers... its not exactly value type but not even sure it will resolve at all.
                //todo: hmm. do we make it resolve as a between the range? or just use the where YEAR(createddatetime) = '2001', hmm. the other problem is this is not timezone sensitive.. but then
                //todo: again the grouping is not timezone sensitive, too bad for now.
                $filter->operator                    = OperatorRules::TYPE_EQUALS;
                $filter->value                       = $value;
                $this->addFilter($filter);
                if($structure != null)
                {
                    $structure .= ' AND ';
                }
                $structure .= $newStartingStructurePosition;
                $newStartingStructurePosition ++;
            }
            $structure = '(' . $structure . ')';
            if($this->filtersStructure != null)
            {
                $this->filtersStructure .= ' AND ';
            }
            $this->filtersStructure .= $structure;
        }
    }
?>