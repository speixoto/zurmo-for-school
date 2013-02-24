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
     * View class for the actions component for the workflow wizard user interface
     */
    class ActionsForWorkflowWizardView extends ComponentForWorkflowWizardView
    {
        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('WorkflowsModule', 'Select Actions');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'actionsPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'actionsNextLink';
        }

        public function registerScripts()
        {
            parent::registerScripts();
            $chartTypesRequiringSecondInputs = ChartRules::getChartTypesRequiringSecondInputs();
            $script = ''; //todO:
            Yii::app()->getClientScript()->registerScript('xx', $script);
        }

        /**
         * @return bool
         */
        protected function isListContentSortable()
        {
            return true;
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $inputPrefixData   = array(get_class($this->model), get_class($this->model->chart));
            $this->form->setInputPrefixData($inputPrefixData);
            $params            = array('inputPrefix' => $inputPrefixData);
            $content           = '<div class="attributesContainer">';
            //$element           = new ChartTypeRadioStaticDropDownForReportElement($this->model->chart, 'type', $this->form,
            //    array_merge($params, array('addBlank' => true)));
            //= $element->render();
            $content          .= 'zero-model image and view<BR>';
            $content          .= 'existing actions div<BR>';
            $content          .= 'action picker<BR>';
            $content          .= '</div>';
            $this->form->clearInputPrefixData();
            $this->registerScripts();
            return $content;
        }
    }
?>