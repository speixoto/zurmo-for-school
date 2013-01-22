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

    class ChartForReportForm extends ConfigurableMetadataModel
    {
        /**
         * Type of chart
         * @var string
         */
        public $type;

        public $firstSeries;

        public $firstRange;

        public $secondSeries;

        public $secondRange;

        private $availableSeriesDataAndLabels;

        private $availableRangeDataAndLabels;

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('type',                    'type', 'type' => 'string'),
                array('firstSeries',  	 		 'type', 'type' => 'string'),
                array('firstRange',  	 		 'type', 'type' => 'string'),
                array('secondSeries',  	 		 'type', 'type' => 'string'),
                array('secondRange',  	 		 'type', 'type' => 'string'),
                array('type',             		 'validateSeriesAndRange'),
            ));
        }

        public function attributeLabels()
        {
            return array();
        }

        public function __construct($availableSeriesDataAndLabels = array(), $availableRangeDataAndLabels = array())
        {
            assert('is_array($availableSeriesDataAndLabels) || $availableSeriesDataAndLabels == null');
            assert('is_array($availableRangeDataAndLabels) || $availableRangeDataAndLabels == null');
            $this->availableSeriesDataAndLabels = $availableSeriesDataAndLabels;
            $this->availableRangeDataAndLabels  = $availableRangeDataAndLabels;
        }

        public function getModuleClassName()
        {
            return $this->moduleClassName;
        }

        public function getModelClassName()
        {
            return $this->modelClassName;
        }

        public function validateSeriesAndRange()
        {
            $passedValidation = true;
            if($this->type != null)
            {
                if($this->firstSeries == null)
                {
                    $this->addError('firstSeries', Yii::t('Default', 'First Series cannot be blank.'));
                    $passedValidation = false;
                }
                if($this->firstRange == null)
                {
                    $this->addError('firstRange', Yii::t('Default', 'First Range cannot be blank.'));
                    $passedValidation = false;
                }
                if(in_array($this->type, ChartRules::getChartTypesRequiringSecondInputs()) && $this->secondSeries == null)
                {
                    $this->addError('secondSeries', Yii::t('Default', 'Second Series cannot be blank.'));
                    $passedValidation = false;
                }
                if(in_array($this->type, ChartRules::getChartTypesRequiringSecondInputs()) && $this->secondRange == null)
                {
                    $this->addError('secondRange', Yii::t('Default', 'Second Range cannot be blank.'));
                    $passedValidation = false;
                }
                if($this->firstSeries != null && $this->secondSeries != null && $this->firstSeries == $this->secondSeries)
                {
                    $this->addError('secondSeries', Yii::t('Default', 'Second Series must be unique.'));
                    $passedValidation = false;
                }
                if($this->firstRange != null && $this->secondRange != null && $this->firstRange == $this->secondRange)
                {
                    $this->addError('secondRange', Yii::t('Default', 'Second Range must be unique.'));
                    $passedValidation = false;
                }
            }
            return $passedValidation;
        }

        public function getTypeDataAndLabels()
        {
            $data  = array();
            $types = ChartRules::availableTypes();
            foreach($types as $type)
            {
                 $data[$type] = ChartRules::getTranslatedTypeLabel($type);
            }
            return $data;
        }

        public function getAvailableFirstSeriesDataAndLabels()
        {
            return $this->availableSeriesDataAndLabels;
        }

        public function getAvailableFirstRangeDataAndLabels()
        {
            return $this->availableRangeDataAndLabels;
        }

        public function getAvailableSecondSeriesDataAndLabels()
        {
            return $this->availableSeriesDataAndLabels;
        }

        public function getAvailableSecondRangeDataAndLabels()
        {
            return $this->availableRangeDataAndLabels;
        }
    }
?>