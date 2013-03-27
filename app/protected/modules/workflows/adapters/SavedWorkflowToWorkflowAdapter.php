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

    class SavedWorkflowToWorkflowAdapter
    {
        public static function makeWorkflowBySavedWorkflow($savedWorkflow)
        {
            $workflow = new Workflow();
            if($savedWorkflow->id > 0)
            {
                $workflow->setId((int)$savedWorkflow->id);
            }
            $workflow->setDescription($savedWorkflow->description);
            $workflow->setIsActive((bool)$savedWorkflow->isActive);
            $workflow->setModuleClassName($savedWorkflow->moduleClassName);
            $workflow->setName($savedWorkflow->name);
            $workflow->setOrder((int)$savedWorkflow->order);
            $workflow->setType($savedWorkflow->type);
            $workflow->setTriggerOn($savedWorkflow->triggerOn);
            if($savedWorkflow->serializedData != null)
            {
                $unserializedData = unserialize($savedWorkflow->serializedData);
                if(isset($unserializedData['triggersStructure']))
                {
                    $workflow->setTriggersStructure($unserializedData['triggersStructure']);
                }
                self::makeComponentFormAndPopulateWorkflowFromData(
                            $unserializedData[ComponentForWorkflowForm::TYPE_TRIGGERS], $workflow, 'Trigger');
                self::makeActionForWorkflowFormAndPopulateWorkflowFromData(
                            $unserializedData[ComponentForWorkflowForm::TYPE_ACTIONS],  $workflow);
                self::makeEmailAlertForWorkflowFormAndPopulateWorkflowFromData(
                    $unserializedData[ComponentForWorkflowForm::TYPE_EMAIL_ALERTS],  $workflow);
                if(isset($unserializedData['timeTrigger']))
                {
                    $moduleClassName = $workflow->getModuleClassName();
                    $timeTrigger     = new TimeTriggerForWorkflowForm($moduleClassName,
                                                                      $moduleClassName::getPrimaryModelName(),
                                                                      $workflow->getType());
                    $timeTrigger->setAttributes($unserializedData['timeTrigger']);
                    $workflow->setTimeTrigger($timeTrigger);
                    $workflow->setTimeTriggerAttribute($timeTrigger->getAttributeIndexOrDerivedType());
                }
            }
            return $workflow;
        }

        public static function resolveWorkflowToSavedWorkflow(Workflow $workflow, SavedWorkflow $savedWorkflow)
        {
            $savedWorkflow->description     = $workflow->getDescription();
            $savedWorkflow->isActive        = $workflow->getIsActive();
            $savedWorkflow->moduleClassName = $workflow->getModuleClassName();
            $savedWorkflow->name            = $workflow->getName();
            $savedWorkflow->order           = $workflow->getOrder();
            $savedWorkflow->triggerOn       = $workflow->getTriggerOn();
            $savedWorkflow->type            = $workflow->getType();
            $data = array();
            $data['triggersStructure']      = $workflow->getTriggersStructure();
            $data[ComponentForWorkflowForm::TYPE_TRIGGERS]                     =
                  self::makeArrayFromComponentFormsAttributesData($workflow->getTriggers());
            $data[ComponentForWorkflowForm::TYPE_ACTIONS]                      =
                  self::makeArrayFromActionForWorkflowFormAttributesData($workflow->getActions());
            $data[ComponentForWorkflowForm::TYPE_EMAIL_ALERTS]                      =
                self::makeArrayFromEmailAlertForWorkflowFormAttributesData($workflow->getEmailAlerts());
            if($workflow->getTimeTrigger() != null)
            {
                $data['timeTrigger'] = self::makeArrayFromTimeTriggerForWorkflowFormAttributesData(
                                       $workflow->getTimeTrigger());
            }
            $savedWorkflow->serializedData   = serialize($data);
        }

        public static function makeArrayFromEmailAlertForWorkflowFormAttributesData(Array $componentFormsData)
        {
            $data = array();
            foreach($componentFormsData as $key => $emailAlertForWorkflowForm)
            {
                foreach($emailAlertForWorkflowForm->getAttributes() as $attribute => $value)
                {
                    $data[$key][$attribute] = $value;
                }
                foreach($emailAlertForWorkflowForm->getEmailAlertRecipients() as
                        $emailAlertRecipientKey => $workflowEmailAlertRecipientForm)
                {
                    foreach($workflowEmailAlertRecipientForm->getAttributes() as $attribute => $value)
                    {
                        $data[$key][EmailAlertForWorkflowForm::EMAIL_ALERT_RECIPIENTS]
                             [$emailAlertRecipientKey][$attribute] = $value;
                    }
                }
            }
            return $data;
        }

        protected static function makeArrayFromTimeTriggerForWorkflowFormAttributesData(
                                  TimeTriggerForWorkflowForm $timeTriggerForWorkflowForm)
        {
            $data = array();
            foreach($timeTriggerForWorkflowForm->getAttributes() as $attribute => $value)
            {
                $data[$attribute] = $value;
            }
            return $data;
        }

        protected static function makeArrayFromComponentFormsAttributesData(Array $componentFormsData)
        {
            $data = array();
            foreach($componentFormsData as $key => $componentForm)
            {
                foreach($componentForm->getAttributes() as $attribute => $value)
                {
                    $data[$key][$attribute] = $value;
                }
            }
            return $data;
        }

        protected static function makeArrayFromActionForWorkflowFormAttributesData(Array $componentFormsData)
        {
            $data = array();
            foreach($componentFormsData as $key => $actionForWorkflowForm)
            {
                foreach($actionForWorkflowForm->getAttributes() as $attribute => $value)
                {

                    $data[$key][$attribute] = $value;
                }
                foreach($actionForWorkflowForm->getActionAttributes() as $actionAttribute => $workflowActionAttributeForm)
                {
                    foreach($workflowActionAttributeForm->getAttributes() as $attribute => $value)
                    {
                        $data[$key][ActionForWorkflowForm::ACTION_ATTRIBUTES][$actionAttribute][$attribute] = $value;
                    }
                }
            }
            return $data;
        }

        protected static function makeComponentFormAndPopulateWorkflowFromData($componentFormsData, $workflow, $componentPrefix)
        {
            $moduleClassName    = $workflow->getModuleClassName();
            $addMethodName      = 'add' . $componentPrefix;
            $componentClassName = $componentPrefix . 'ForWorkflowForm';
            $rowKey             = 0;
            foreach($componentFormsData as $componentFormData)
            {
                $component      = new $componentClassName($moduleClassName,
                                                          $moduleClassName::getPrimaryModelName(),
                                                          $workflow->getType(), $rowKey);
                $component->setAttributes($componentFormData);
                $workflow->{$addMethodName}($component);
                $rowKey ++;
            }
        }

        protected static function makeActionForWorkflowFormAndPopulateWorkflowFromData($componentFormsData, $workflow)
        {
            $moduleClassName    = $workflow->getModuleClassName();
            $rowKey             = 0;
            foreach($componentFormsData as $componentFormData)
            {
                $component      = new ActionForWorkflowForm($moduleClassName::getPrimaryModelName(),
                                                            $workflow->getType(), $rowKey);
                $component->setAttributes($componentFormData);
                $workflow->addAction($component);
                $rowKey ++;
            }
        }

        protected static function makeEmailAlertForWorkflowFormAndPopulateWorkflowFromData($componentFormsData, $workflow)
        {
            $moduleClassName    = $workflow->getModuleClassName();
            $rowKey             = 0;
            foreach($componentFormsData as $componentFormData)
            {
                $component      = new EmailAlertForWorkflowForm($moduleClassName::getPrimaryModelName(),
                                                                $workflow->getType(), $rowKey);
                $component->setAttributes($componentFormData);
                $workflow->addEmailAlert($component);
                $rowKey ++;
            }
        }
    }
?>