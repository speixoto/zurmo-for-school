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
     * Base class for all report wizard form models
     */
    abstract class ReportWizardForm extends CFormModel
    {
        const MODULE_VALIDATION_SCENARIO             = 'ValidateForModule';

        const FILTERS_VALIDATION_SCENARIO            = 'ValidateForFilters';

        const DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO = 'ValidateForDisplayAttributes';

        const DRILLDOWN_DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO = 'ValidateForDisplayAttributes';

        const ORDER_BYS_VALIDATION_SCENARIO          = 'ValidateForOrderBys';

        const GROUP_BYS_VALIDATION_SCENARIO          = 'ValidateForGroupBys';

        const CHART_VALIDATION_SCENARIO              = 'ValidateForChart';

        const GENERAL_DATA_VALIDATION_SCENARIO       = 'ValidateForGeneralData';

        public $description;

        /**
         * Id of the SavedReport model if available.
         * @var integer
         */
        public $id;

        public $moduleClassName;

        public $name;

        public $type;

        public $ownerId;

        public $ownerName;

        public $filtersStructure;

        public $filters                    = array();

        public $groupBys                   = array();

        public $orderBys                   = array();

        public $displayAttributes          = array();

        public $drillDownDisplayAttributes = array();

        protected $isNew = false;

        /**
         * Object containing information on how to setup permissions for the new models that are created during the
         * import process.
         * @var object ExplicitReadWriteModelPermissions
         * @see ExplicitReadWriteModelPermissions
         */
        protected $explicitReadWriteModelPermissions;

        /**
         * Mimics the expected interface by the views when calling into
         * a form or model.
         */
        public function getId()
        {
            return $this->id;
        }

        public function isNew()
        {
            return $this->isNew;
        }

        public function setIsNew()
        {
            $this->isNew = true;
        }

        public function rules()
        {
            return array(
                array('description', 	     'type',               'type' => 'string'),
                array('name', 			     'type',        	   'type' => 'string'),
                array('name', 			     'length',   		   'max' => 64),
                array('name', 			     'required', 		   'on' => self::GENERAL_DATA_VALIDATION_SCENARIO),
                array('moduleClassName',     'type',     		   'type' => 'string'),
                array('moduleClassName',     'length',             'max' => 64),
                array('moduleClassName',     'required', 		   'on' => self::MODULE_VALIDATION_SCENARIO),
                array('type', 		         'type',     		   'type' => 'string'),
                array('type', 			     'length',   		   'max' => 64),
                array('type', 			     'required'),
                array('ownerId',   	         'type',     		   'type' => 'integer'),
                array('ownerId',   		     'required', 		   'on' => self::GENERAL_DATA_VALIDATION_SCENARIO),
                array('ownerName', 		     'required', 		   'on' => self::GENERAL_DATA_VALIDATION_SCENARIO),
                array('filters', 		     'validateFilters',
                                             'on' => self::FILTERS_VALIDATION_SCENARIO),
                array('filtersStructure', 	 'validateFiltersStructure',
                                             'on' => self::FILTERS_VALIDATION_SCENARIO),
                array('displayAttributes',   'validateDisplayAttributes',
                                             'on' => self::DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO),
                array('drillDownAttributes', 'validateDrillDownDisplayAttributes',
                                             'on' => self::DRILLDOWN_DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO),
                array('orderBys', 		     'validateOrderBys',   'on' => self::ORDER_BYS_VALIDATION_SCENARIO),
                array('groupBys', 		     'validateGroupBys',   'on' => self::GROUP_BYS_VALIDATION_SCENARIO),
                array('chart', 		         'validateChart',      'on' => self::CHART_VALIDATION_SCENARIO),
            );
        }

        public function attributeLabels()
        {
            return array(
                'name'           => Yii::t('Default', 'Name'),
                'ownerId'        => Yii::t('Default', 'Owner Id'),
                'ownerName'      => Yii::t('Default', 'Owner Name'),
            );
        }

        public function getExplicitReadWriteModelPermissions()
        {
            return $this->explicitReadWriteModelPermissions;
        }

        public function setExplicitReadWriteModelPermissions(ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            $this->explicitReadWriteModelPermissions = $explicitReadWriteModelPermissions;
        }

        public function validateFilters()
        {
            return $this->validateComponent(ComponentForReportForm::TYPE_FILTERS, 'filters');
        }

        public function validateFiltersStructure()
        {
            if(count($this->filters) > 0)
            {
                if(null != $errorMessage = SQLOperatorUtil::
                           resolveValidationForATemplateSqlStatementAndReturnErrorMessage($this->filtersStructure,
                           count($this->filters)))
                {
                    $this->addError('filtersStructure', $errorMessage);
                }
            }
        }

        public function validateOrderBys()
        {
            return $this->validateComponent(ComponentForReportForm::TYPE_ORDER_BYS, 'orderBys');
        }

        public function validateDisplayAttributes()
        {
            return $this->validateComponent(ComponentForReportForm::TYPE_DISPLAY_ATTRIBUTES, 'displayAttributes');
        }

        public function validateDrillDownDisplayAttributes()
        {
            return $this->validateComponent(ComponentForReportForm::TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES, 'drillDownDisplayAttributes');
        }

        public function validateGroupBys()
        {
            return $this->validateComponent(ComponentForReportForm::TYPE_GROUP_BYS, 'groupBys');
        }

        public function validateChart()
        {
            //todo:
        }

        protected function validateComponent($componentType, $componentName)
        {
            assert('is_string($componentType)');
            assert('is_string($componentName)');
            $passedValidation = true;
            $count            = 0;
            foreach($this->{$componentName} as $model)
            {
                if(!$model->validate())
                {
                    foreach($model->getErrors() as $attribute => $error)
                    {
                        $attributePrefix = static::resolveErrorAttributePrefix($componentType, $count);
                        $this->addError( $attributePrefix . $attribute, $error);
                    }
                    $passedValidation = false;
                }
                $count ++;
            }
            return $passedValidation;
        }

        protected static function resolveErrorAttributePrefix($treeType, $count)
        {
            assert('is_string($treeType)');
            assert('is_int($count)');
            return $treeType . '_' . $count . '_';
        }
    }
?>