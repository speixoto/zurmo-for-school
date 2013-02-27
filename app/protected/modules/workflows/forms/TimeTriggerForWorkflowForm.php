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
     * Component form for a time trigger definition
     */
    class TimeTriggerForWorkflowForm extends TriggerForWorkflowForm
    {
        /**
         * @var integer.  Example: Account name is xyz for 1 hour.  The duration seconds would be set to 3600
         */
        public $durationSeconds;

        /**
         * @return string component type
         */
        public static function getType()
        {
            return static::TYPE_TIME_TRIGGER;
        }

        /**
         * @return array
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('durationSeconds', 'type', 'type' => 'integer'),
            ));
        }

        /**
         * @return array
         * @throws NotSupportedException if the attributeIndexOrDerivedType has not been populated yet
         */
        public function getOperatorValuesAndLabels()
        {
            if($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }
            $type = $this->getAvailableOperatorsType();
            $data = array();
            ModelAttributeToWorkflowOperatorTypeUtil::resolveOperatorsToIncludeByType($data, $type);
            $data[OperatorRules::TYPE_DOES_NOT_CHANGE] = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_DOES_NOT_CHANGE);
            if($type != ModelAttributeToWorkflowOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_BOOLEAN &&
               $type != ModelAttributeToWorkflowOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_HAS_ONE)
            {
                $data[OperatorRules::TYPE_IS_EMPTY]      = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_EMPTY);
                $data[OperatorRules::TYPE_IS_NOT_EMPTY]  = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_NOT_EMPTY);
            }
            return $data;
        }

        public function getDurationValuesAndLabels()
        {
            if($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }

            $modelToWorkflowAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToWorkflowAdapter();
            $type = $modelToWorkflowAdapter->getDisplayElementType($this->getResolvedAttribute());
            $data = array();
            if($type == 'DateTime')
            {
                //include hours
                //include positive  as IS XXX FROM NOW
                //include negative  as IS YYY AGO
                return $this->makeDurationValuesAndLabels(true, true, true, true);
            }
            elseif($type == 'Date')
            {
                return $this->makeDurationValuesAndLabels(true, true, true, false);
                //do not include hours
                //include positive  as IS XXX FROM NOW
                //include negative  as IS YYY AGO
            }
            else
            {
                return $this->makeDurationValuesAndLabels(true, false, false, true);
                //include hours
                //include positive   FOR XXX
            }

            return $data;
            ModelAttributeToWorkflowOperatorTypeUtil::resolveOperatorsToIncludeByType($data, $type);
            $data[OperatorRules::TYPE_DOES_NOT_CHANGE] = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_DOES_NOT_CHANGE);
            if($type != ModelAttributeToWorkflowOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_BOOLEAN &&
                $type != ModelAttributeToWorkflowOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_HAS_ONE)
            {
                $data[OperatorRules::TYPE_IS_EMPTY]      = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_EMPTY);
                $data[OperatorRules::TYPE_IS_NOT_EMPTY]  = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_NOT_EMPTY);
            }
            return $data;
        }

        protected function makeDurationValuesAndLabels($includePositiveDuration = false,
                                                       $includeNegativeDuration = false,
                                                       $isTimeBased             = false,
                                                       $includeHours            = true)
        {
            assert('is_bool($includePositiveDuration)');
            assert('is_bool($includeNegativeDuration)');
            assert('is_bool($isTimeBased)');
            assert('is_bool($includeHours)');
            $data = array();
            if($includeNegativeDuration)
            {
                if($isTimeBased)
                {
                    $data[-31104000] = Zurmo::t('WorkflowsModule', '{n} year ago|{n} years ago', array(1));
                    $data[-15552000] = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(180));
                    $data[-12960000] = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(150));
                    $data[-10368000] = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(120));
                    $data[-7776000]  = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(90));
                    $data[-5184000]  = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(60));
                    $data[-2592000]  = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(30));
                    $data[-1814400]  = Zurmo::t('WorkflowsModule', '{n} week ago|{n} weeks ago', array(3));
                    $data[-1209600]  = Zurmo::t('WorkflowsModule', '{n} week ago|{n} weeks ago', array(2));
                    $data[-604800]   = Zurmo::t('WorkflowsModule', '{n} week ago|{n} weeks ago', array(1));
                    $data[-864000]   = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(10));
                    $data[-432000]   = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(5));
                    $data[-345600]   = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(4));
                    $data[-259200]   = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(3));
                    $data[-172800]   = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(2));
                    $data[-86400]    = Zurmo::t('WorkflowsModule', '{n} day ago|{n} days ago', array(1));
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            if($includeNegativeDuration && $includeHours)
            {
                if($isTimeBased)
                {
                    $data[-43200] = Zurmo::t('WorkflowsModule', '{n} hour ago|{n} hours ago', array(12));
                    $data[-28800] = Zurmo::t('WorkflowsModule', '{n} hour ago|{n} hours ago', array(8));
                    $data[-14400] = Zurmo::t('WorkflowsModule', '{n} hour ago|{n} hours ago', array(4));
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            if($includePositiveDuration && $includeHours)
            {
                if($isTimeBased)
                {
                    $data[14400] = Zurmo::t('WorkflowsModule', '{n} hour from now|{n} hours from now', array(4));
                    $data[28800] = Zurmo::t('WorkflowsModule', '{n} hour from now|{n} hours from now', array(8));
                    $data[43200] = Zurmo::t('WorkflowsModule', '{n} hour from now|{n} hours from now', array(12));
                }
                else
                {
                    $data[14400] = Zurmo::t('WorkflowsModule', 'for {n} hour|for {n} hours', array(4));
                    $data[28800] = Zurmo::t('WorkflowsModule', 'for {n} hour|for {n} hours', array(8));
                    $data[43200] = Zurmo::t('WorkflowsModule', 'for {n} hour|for {n} hours', array(12));
                }
            }
            if($includePositiveDuration)
            {
                if($isTimeBased)
                {
                    $data[86400]    = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(1));
                    $data[172800]   = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(2));
                    $data[259200]   = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(3));
                    $data[345600]   = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(4));
                    $data[432000]   = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(5));
                    $data[864000]   = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(10));
                    $data[604800]   = Zurmo::t('WorkflowsModule', '{n} week from now|{n} weeks from now', array(1));
                    $data[1209600]  = Zurmo::t('WorkflowsModule', '{n} week from now|{n} weeks from now', array(2));
                    $data[1814400]  = Zurmo::t('WorkflowsModule', '{n} week from now|{n} weeks from now', array(3));
                    $data[2592000]  = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(30));
                    $data[5184000]  = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(60));
                    $data[7776000]  = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(90));
                    $data[10368000] = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(120));
                    $data[12960000] = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(150));
                    $data[15552000] = Zurmo::t('WorkflowsModule', '{n} day from now|{n} days from now', array(180));
                    $data[31104000] = Zurmo::t('WorkflowsModule', '{n} year from now|{n} years from now', array(1));
                }
                else
                {
                    $data[86400]    = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(1));
                    $data[172800]   = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(2));
                    $data[259200]   = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(3));
                    $data[345600]   = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(4));
                    $data[432000]   = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(5));
                    $data[864000]   = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(10));
                    $data[604800]   = Zurmo::t('WorkflowsModule', 'for {n} week|{n} weeks', array(1));
                    $data[1209600]  = Zurmo::t('WorkflowsModule', 'for {n} week|{n} weeks', array(2));
                    $data[1814400]  = Zurmo::t('WorkflowsModule', 'for {n} week|{n} weeks', array(3));
                    $data[2592000]  = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(30));
                    $data[5184000]  = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(60));
                    $data[7776000]  = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(90));
                    $data[10368000] = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(120));
                    $data[12960000] = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(150));
                    $data[15552000] = Zurmo::t('WorkflowsModule', 'for {n} day|{n} days', array(180));
                    $data[31104000] = Zurmo::t('WorkflowsModule', 'for {n} year|{n} years', array(1));
                }
            }
            return $data;
        }
    }
?>