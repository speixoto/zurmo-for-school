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

        public $queryOnly = false;

        public $valueUsedAsDrillDownFilter = false;

        public $madeViaSelectInsteadOfViaModel = false;

        /**
         * Indicates the model alias for working with the resultsRowData. Sometimes there can be the same related model
         * more than once via different relations.  This makes sure the resultsRowData knows which model to use. Only applies
         * when the display attribute is made via the model and not via the select
         * @var string
         */
        protected $modelAliasUsingTableAliasName;

        public static function getType()
        {
            return static::TYPE_DISPLAY_ATTRIBUTES;
        }

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
            if(count($attributeNames) != 6)
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



        public function resolveAttributeNameForGridViewColumn($key)
        {
            assert('is_int($key)');
            $modelToReportAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
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

        public function getRawValueRelatedAttribute()
        {
            $modelToReportAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
            return $modelToReportAdapter->getRawValueRelatedAttribute($this->getResolvedAttribute());
        }

        /**
         * Raw values such as those used by the header x-axis or y-axis rows/columns need to be translated. An example
         * is a dropdown where the value is the raw database value and needs to be properly translated for display.
         * Another example is dynamic __User, where the value is the user id, and needs to be stringified to the User
         * model.
         * @param $value
         * @return string
         */
        public function resolveValueAsLabelForHeaderCell($value)
        {
            $tContent             = null;
            $translatedValue      = $value;
            $resolvedAttribute    = $this->getResolvedAttribute();
            $displayElementType   = $this->getDisplayElementType();
            $modelToReportAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
            if($modelToReportAdapter->getModel()->isAttribute($resolvedAttribute) &&
               $modelToReportAdapter->getModel()->isRelation($resolvedAttribute) &&
               !$modelToReportAdapter->getModel()->isOwnedRelation($resolvedAttribute))
            {
                $relationModelClassName = $modelToReportAdapter->getModel()->getRelationModelClassName($resolvedAttribute);
                $relatedModel = $relationModelClassName::getById((int)$value);
                if($relatedModel->isAttribute('serializedLabels'))
                {
                    $translatedValue     = $relatedModel->resolveTranslatedNameByLanguage(Yii::app()->language);
                }
            }
            elseif($displayElementType == 'User')
            {
                $user            = User::getById((int)$value);
                $translatedValue = strval($user);
            }
            elseif($displayElementType == 'DropDown')
            {
                $customFieldData = CustomFieldDataModelUtil::getDataByModelClassNameAndAttributeName(
                                   $this->getResolvedAttributeModelClassName(), $this->getResolvedAttribute());
                $dataAndLabels   = CustomFieldDataUtil::getDataIndexedByDataAndTranslatedLabelsByLanguage(
                                   $customFieldData, Yii::app()->language);
                if(isset($dataAndLabels[$value]))
                {
                    $translatedValue = $dataAndLabels[$value];
                }
            }
            elseif($displayElementType == 'CheckBox')
            {
                if($value)
                {
                    $translatedValue = Yii::t('Default', 'Yes');
                }
                elseif($value == false && $value != '')
                {
                    $translatedValue = Yii::t('Default', 'No');
                }
            }
            if($translatedValue === null)
            {
                $translatedValue = '';
            }
            return $translatedValue;
        }
    }
?>