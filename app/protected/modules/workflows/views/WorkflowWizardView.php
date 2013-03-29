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
     * Base class for working with the workflow wizard
     */
    abstract class WorkflowWizardView extends WizardView
    {
        public static function getControllerId()
        {
            return 'workflows';
        }

        /**
         * @return string
         */
        public function getTitle()
        {
            return Zurmo::t('WorkflowsModule', 'Workflow Wizard');
        }

        /**
         * @return string
         */
        protected static function getStartingValidationScenario()
        {
            return WorkflowWizardForm::MODULE_VALIDATION_SCENARIO;
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            Yii::app()->getClientScript()->registerCoreScript('treeview');
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.modules.workflows.views.assets')) . '/WorkflowUtils.js');
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.elements.assets')) . '/SelectInputUtils.js', CClientScript::POS_END);
            $this->registerClickFlowScript();
            $this->registerModuleClassNameChangeScript();
        }

        protected function registerModuleClassNameChangeScript()
        {
            $moduleClassNameId = get_class($this->model) .  '[moduleClassName]';
            Yii::app()->clientScript->registerScript('moduleForWorkflowChangeScript', "
                $('input:radio[name=\"" . $moduleClassNameId . "\"]').live('change', function()
                    {
                        $('#TriggersForWorkflowWizardView').find('.dynamic-rows').find('ul:first').find('li').remove();
                        $('#TriggersTreeArea').html('');
                        $('." . TriggersForWorkflowWizardView::getZeroComponentsClassName() . "').show();
                        rebuildWorkflowTriggersAttributeRowNumbersAndStructureInput('TriggersForWorkflowWizardView');
                        $('#ActionsForWorkflowWizardView').find('.dynamic-rows').find('ul:first').find('li').remove();
                        $('#actionType option:selected').removeAttr('selected');
                        $('." . ActionsForWorkflowWizardView::getZeroComponentsClassName() . "').show();
                        rebuildWorkflowActionRowNumbers('ActionsForWorkflowWizardView');
                        $('#EmailMessagesForWorkflowWizardView').find('.dynamic-rows').find('ul:first').find('li').remove();
                        $('." . EmailMessagesForWorkflowWizardView::getZeroComponentsClassName() . "').show();
                        " . $this->registerModuleClassNameChangeScriptExtraPart() . "
                    }
                );
            ");
        }

        protected function registerModuleClassNameChangeScriptExtraPart()
        {
        }
    }
?>