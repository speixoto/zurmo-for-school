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
     * Adds specific input id/name/value handling for Workflow trigger usage
     */
    class MixedDateTypesForWorkflowElement extends MixedDateTypesForWizardElement
    {
        public function __construct($model, $attribute, $form = null, array $params = array())
        {
            assert('$model instanceof TriggerForWorkflowForm || $model instanceof TimeTriggerForWorkflowForm');
            parent::__construct($model, $attribute, $form, $params);
        }

        protected function getValueTypeDropDownArray()
        {
            if($this->model instanceof TimeTriggerForWorkflowForm)
            {
                return MixedDateTimeTypesSearchFormAttributeMappingRules::getTimeOnlyValueTypesAndLabels();
            }
            $valueTypesAndLabels = MixedDateTimeTypesSearchFormAttributeMappingRules::getTimeBasedValueTypesAndLabels();
            if($this->model->getWorkflowType() == Workflow::TYPE_BY_TIME && $this->model->getAttribute() != null)
            {
                $valueTypesAndLabels[MixedDateTypesSearchFormAttributeMappingRules::TYPE_DOES_NOT_CHANGE] = Zurmo::t('Core', 'Does Not Change');
            }
            elseif($this->model->getWorkflowType() == Workflow::TYPE_ON_SAVE && $this->model->getAttribute() != null)
            {
                $valueTypesAndLabels[MixedDateTypesSearchFormAttributeMappingRules::TYPE_DOES_NOT_CHANGE] = Zurmo::t('Core', 'Does Not Change');
                $valueTypesAndLabels[MixedDateTypesSearchFormAttributeMappingRules::TYPE_CHANGES]         = Zurmo::t('Core', 'Changes');
                $valueTypesAndLabels[MixedDateTypesSearchFormAttributeMappingRules::TYPE_WAS_ON]          = Zurmo::t('Core', 'Was On');
                $valueTypesAndLabels[MixedDateTypesSearchFormAttributeMappingRules::TYPE_BECOMES_ON]      = Zurmo::t('Core', 'Becomes On');
            }
            elseif($this->model->getWorkflowType() == Workflow::TYPE_ON_SAVE && $this->model->getAttribute() == null)
            {
                $valueTypesAndLabels[MixedDateTypesSearchFormAttributeMappingRules::TYPE_DOES_NOT_CHANGE] = Zurmo::t('Core', 'Does Not Change');
                $valueTypesAndLabels[MixedDateTypesSearchFormAttributeMappingRules::TYPE_CHANGES]         = Zurmo::t('Core', 'Changes');
            }
            return $valueTypesAndLabels;
        }

        protected function getEditableValueTypeHtmlOptions()
        {
            $htmlOptions = parent::getEditableValueTypeHtmlOptions();
            if($this->model instanceof TimeTriggerForWorkflowForm && isset($htmlOptions['empty']))
            {
                unset($htmlOptions['empty']);
            }
            return $htmlOptions;
        }
    }
?>