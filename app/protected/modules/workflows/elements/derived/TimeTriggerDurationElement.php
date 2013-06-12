<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Display the duration derived attributes including the sign, type, and duration in seconds
     */
    class TimeTriggerDurationElement extends Element
    {
        protected function renderControlEditable()
        {
            $content  = $this->renderEditableDurationIntervalTextField() . "\n";
            $content .= $this->renderEditableDurationTypeDropDownField() . "\n";
            $content .= $this->renderEditableDurationSignDropDownField() . "\n";
            return $content;
        }

        protected function renderEditableDurationIntervalTextField()
        {
            $id = $this->getEditableInputId('durationInterval');
            $htmlOptions = array(
                'name' => $this->getEditableInputName('durationInterval'),
                'id'   => $id,
            );
            $textField = $this->form->textField($this->model, 'durationInterval', $htmlOptions);
            $error     = $this->form->error    ($this->model, 'durationInterval', array('inputID' => $id), true, true);
            return $textField . $error;
        }

        protected function renderEditableDurationSignDropDownField()
        {
            $dropDownArray = $this->getDurationSignDropDownArray();
            $id = $this->getEditableInputId('durationSign');
            $htmlOptions = array(
                'name'  => $this->getEditableInputName('durationSign'),
                'id'    => $id,
            );
            return $this->form->dropDownList($this->model, 'durationSign', $dropDownArray, $htmlOptions);
        }

        protected function renderEditableDurationTypeDropDownField()
        {
            $dropDownArray = $this->getDurationTypeDropDownArray();
            $id = $this->getEditableInputId('durationType');
            $htmlOptions = array(
                'name'  => $this->getEditableInputName('durationType'),
                'id'    => $id,
            );
            return $this->form->dropDownList($this->model, 'durationType', $dropDownArray, $htmlOptions);
        }

        protected function getDurationSignDropDownArray()
        {
            return array(TimeTriggerForWorkflowForm::DURATION_SIGN_POSITIVE => Zurmo::t('WorkflowsModule', 'From now'),
                         TimeTriggerForWorkflowForm::DURATION_SIGN_NEGATIVE => Zurmo::t('WorkflowsModule', 'Ago'));
        }

        protected function getDurationTypeDropDownArray()
        {
            return array(TimeTriggerForWorkflowForm::DURATION_TYPE_DAY  => Zurmo::t('Core', 'Day(s)'),
                         TimeTriggerForWorkflowForm::DURATION_TYPE_WEEK => Zurmo::t('Core', 'Week(s)'),
                         TimeTriggerForWorkflowForm::DURATION_TYPE_MONTH => Zurmo::t('Core', 'Month(s)'),
                         TimeTriggerForWorkflowForm::DURATION_TYPE_YEAR => Zurmo::t('Core', 'Year(s)'));
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            if ($this->form === null)
            {
                return $this->getFormattedAttributeLabel();
            }
            $id = $this->getEditableInputId($this->attribute, 'emailAddress');
            return $this->form->labelEx($this->model, $this->attribute, array('for' => $id));
        }
    }
?>
