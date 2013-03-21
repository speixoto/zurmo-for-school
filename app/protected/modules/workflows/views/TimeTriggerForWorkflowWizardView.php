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
     * View class for the time trigger component for the workflow wizard user interface
     */
    class TimeTriggerForWorkflowWizardView extends ComponentForWorkflowWizardView
    {
        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('WorkflowsModule', 'Select Time Trigger');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'timeTriggerPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'timeTriggerNextLink';
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $script = '
                $(".remove-dynamic-row-link.' . TimeTriggerForWorkflowForm::getType() . '").live("click", function(){
                    $(this).parent().remove();
                    $("#ByTimeWorkflowWizardForm_timeTriggerAttribute").val("");
                    return false;
                });
            ';
            Yii::app()->getClientScript()->registerScript('TimeTriggerForWorkflowComponentScript', $script);
        }

        /**
         * @return bool
         */
        protected function isListContentSortable()
        {
            return true;
        }

        /**
         * @return int
         */
        protected function getItemsCount()
        {
            return count($this->model->timeTrigger);
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $content  = '<div>'; //todo: is this div necessary?
            $content .= $this->renderZeroComponentsContentAndWrapper();
            $content .= $this->renderTimeTriggerContentAndWrapper();
            $content .= $this->renderAttributeSelectorContentAndWrapper();
            $content .= '</div>';
            $this->registerScripts();
            return $content;
        }

        /**
         * @return string
         */
        public static function getZeroComponentsClassName()
        {
            return 'NoTimeTrigger';
        }

        /**
         * @return string
         */
        protected function getZeroComponentsMessageContent()
        {
            return '<div class="large-icon"></div><h2>' . Zurmo::t('WorkflowsModule', 'Select a time trigger below') . '</h2>';
        }
        protected function renderZeroComponentsContentAndWrapper()
        {
            return ZurmoHtml::tag('div', array('class' => 'zero-components-view ' .
                   ComponentForWorkflowForm::TYPE_TIME_TRIGGER), $this->getZeroComponentsContent());
        }

        protected function renderAttributeSelectorContentAndWrapper()
        {
            $element                    = new TimeTriggerAttributeStaticDropDownElement($this->model,
                                          'timeTriggerAttribute', $this->form, array('addBlank' => true));
            $element->editableTemplate  = '{content}{error}';
            $attributeSelectorContent   = $element->render();
            return ZurmoHtml::tag('div', array('class' => 'time-trigger-attribute-selector-container'),
                                         $attributeSelectorContent);
        }

        protected function renderTimeTriggerContentAndWrapper()
        {
            if($this->model->timeTriggerAttribute != null)
            {
                $componentType       = TimeTriggerForWorkflowForm::getType();
                $inputPrefixData     = array(get_class($this->model), $componentType);
                $adapter             = new WorkflowAttributeToElementAdapter($inputPrefixData,
                                       $this->model->timeTrigger, $this->form, $componentType);
                $view                = new AttributeRowForWorkflowComponentView($adapter,
                                       1, $inputPrefixData, $this->model->timeTriggerAttribute,
                                       false, true, $componentType);
                $timeTriggerContent  = ZurmoHtml::tag('div', array('class' => 'dynamic-rows'),
                                       ZurmoHtml::tag('ul', array(), $view->render()));
            }
            else
            {
                $timeTriggerContent = ZurmoHtml::tag('div', array('class' => 'dynamic-rows'), ZurmoHtml::tag('ul', array(), ''));
            }
            return ZurmoHtml::tag('div', array('id' => 'time-trigger-container'), $timeTriggerContent);
        }
    }
?>