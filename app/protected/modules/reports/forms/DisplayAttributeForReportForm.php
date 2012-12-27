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

    class DisplayAttributeForReportForm extends ComponentForReportForm
    {
        const COLUMN_ALIAS_PREFIX = 'col';
        /**
         * @var integer the counter for generating automatic column alias names
         */
        protected static $count = 0;

        public $label;

        public $columnAliasName;

        /**
         * Indicates the model alias for working with the resultsRowData. Sometimes there can be the same related model
         * more than once via different relations.  This makes sure the resultsRowData knows which model to use. Only applies
         * when the display attribute is made via the model and not via the select
         * @var string
         */
        protected $modelAliasUsingTableAliasName;

        public function __construct($moduleClassName, $modelClassName, $reportType)
        {
            parent::__construct($moduleClassName, $modelClassName, $reportType);
            $this->columnAliasName = self::COLUMN_ALIAS_PREFIX . static::$count++;
        }
        /**
         * Makes sure the attributeIndexOrDerivedType always populates first before label otherwise any
         * custom label gets wiped out.
         * (non-PHPdoc)
         * @see ComponentForReportForm::attributeNames()
         */
        public function attributeNames()
        {
            $attributeNames = parent::attributeNames();
            if(count($attributeNames) != 3)
            {
                throw new NotSupportedException();
            }
            array_unshift( $attributeNames, array_pop( $attributeNames ) );
            return $attributeNames;
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('label', 'required'),
                array('label', 'type', 'type' => 'string'),
            ));
        }

        public function __set($name, $value)
        {
            parent::__set($name, $value);
            if ($name == 'attributeIndexOrDerivedType')
            {
                $this->label = $this->getDisplayLabel();
            }
        }

        public static function resetCount()
        {
            self::$count = 0;
        }

        public function setModelAliasUsingTableAliasName($modelAliasUsingTableAliasName)
        {
            assert('is_string($modelAliasUsingTableAliasName)');
            $this->modelAliasUsingTableAliasName = $modelAliasUsingTableAliasName;
        }

        public function getModelAliasUsingTableAliasName()
        {
            return $this->modelAliasUsingTableAliasName;
        }

        public function getDisplayElementType()
        {
            if($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }
            $moduleClassName      = $this->getResolvedAttributeModuleClassName();
            $modelClassName       = $this->getResolvedAttributeModelClassName();
            $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                                    make($moduleClassName, $modelClassName, $this->reportType);
            return $modelToReportAdapter->getDisplayElementType($this->getResolvedAttribute());
        }

        public function resolveAttributeNameForGridViewColumn($key)
        {
            assert('is_int($key)');
            $moduleClassName      = $this->getResolvedAttributeModuleClassName();
            $modelClassName       = $this->getResolvedAttributeModelClassName();
            $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                                    make($moduleClassName, $modelClassName, $this->reportType);
            if($modelToReportAdapter->isDisplayAttributeMadeViaSelect($this->getResolvedAttribute()))
            {
                return $this->columnAliasName;
            }
            return ReportResultsRowData::resolveAttributeNameByKey($key);
        }

        public function isALinkableAttribute()
        {
            $resolvedAttribute = $this->getResolvedAttribute();
            if($resolvedAttribute == 'name' || $resolvedAttribute == 'FullName')
            {
                return true;
            }
            return false;
        }

        public function isATypeOfCurrencyValue()
        {
            $displayElementType = $this->getDisplayElementType();
            if($displayElementType == 'CalculatedCurrencyValue' ||
                $displayElementType == 'CurrencyValue')
            {
                return true;
            }
            return false;
        }
    }
?>