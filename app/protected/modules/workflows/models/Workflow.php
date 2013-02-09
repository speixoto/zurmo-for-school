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

    class Workflow extends CComponent
    {
        const TYPE_ON_SAVE  = 'OnSave';

        const TYPE_BY_TIME  = 'ByTime';

        private $description;

        /**
         * Id of the saved workflow if it has already been saved
         * @var integer
         */
        private $id;

        private $moduleClassName;

        private $name;

        private $type;

        private $triggersStructure;

        private $timeTrigger                = array();

        private $triggers                   = array();

        private $actions                    = array();

        public static function getTypeDropDownArray()
        {
            return array(self::TYPE_ON_SAVE  => Yii::t('Default', 'On-Save'),
                         self::TYPE_BY_TIME  => Yii::t('Default', 'Time-Based'));
        }

        /**
         * Based on the current user, return the workflow supported modules and their display labels.  Only include modules
         * that the user has a right to access.
         * @return array of module class names and display labels.
         */
        public static function getWorkflowSupportedModulesAndLabelsForCurrentUser()
        {
            $moduleClassNamesAndLabels = array();
            $modules = Module::getModuleObjects();
            foreach (self::getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo() as $moduleClassName)
            {
                if($moduleClassName::getStateMetadataAdapterClassName() != null)
                {
                    $workflowRules = WorkflowRules::makeByModuleClassName($moduleClassName);
                    $label         = $workflowRules->getVariableStateModuleLabel(Yii::app()->user->userModel);
                }
                else
                {
                    $label = $moduleClassName::getModuleLabelByTypeAndLanguage('Plural');
                }
                if($label != null)
                {
                    $moduleClassNamesAndLabels[$moduleClassName] = $label;
                }
            }
            return $moduleClassNamesAndLabels;
        }

        public static function getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo()
        {
            $moduleClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                if($module::canHaveWorkflow())
                {
                    if (WorkflowSecurityUtil::canCurrentUserCanAccessModule(get_class($module)))
                    {
                        $moduleClassNames[] = get_class($module);
                    }
                }
            }
            return $moduleClassNames;
        }

        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Yii::t('Default', '(Unnamed)');
            }
            return $this->name;
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

        public function setTriggersStructure($triggersStructure)
        {
            assert('is_string($triggersStructure)');
            $this->triggersStructure = $triggersStructure;
        }

        public function getTriggersStructure()
        {
            return $this->triggersStructure;
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
            assert('$type == self::TYPE_ON_SAVE || $type == self::TYPE_BY_TIME');
            $this->type = $type;
        }

        public function isNew()
        {
            //todo:
            return true;
        }

        public function getTimeTrigger()
        {
            return $this->timeTrigger;
        }

        public function setTimeTrigger(TimeTriggerForWorkflowForm $timeTrigger)
        {
            $this->timeTrigger = $timeTrigger;
        }

        public function removeTimeTrigger()
        {
            $this->timeTrigger = null;
        }

        public function addTrigger(TriggerForWorkflowForm $trigger)
        {
            $this->triggers[] = $trigger;
        }

        public function removeAllTriggers()
        {
            $this->triggers   = array();
        }

        public function getActions()
        {
            return $this->actions;
        }

        public function addAction(ActionForWorkflowForm $action)
        {
            $this->actions[] = $action;
        }

        public function removeAllActions()
        {
            $this->actions   = array();
        }
    }
?>