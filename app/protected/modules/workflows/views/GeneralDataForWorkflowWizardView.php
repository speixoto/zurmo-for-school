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
     * View class for the general data component for the workflow wizard user interface
     */
    class GeneralDataForWorkflowWizardView extends ComponentForWorkflowWizardView
    {
        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('WorkflowsModule', 'Save Workflow');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'generalDataPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'generalDataSaveAndRunLink';
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $content           = '<div class="attributesContainer">';
            $element           = new TextElement($this->model, 'name', $this->form);
            $leftSideContent   = '<table><colgroup><col class="col-0"><col class="col-1">' .
                                 '</colgroup><tr>' . $element->render() . '</tr>';
            $element           = new TextAreaElement(
                                 $this->model, 'description', $this->form, array('rows' => 2));
            $leftSideContent  .= '<tr>' . $element->render() . '</tr>';
            $element           = new TriggerOnStaticDropDownElement(
                                 $this->model, 'triggerOn', $this->form);
            $leftSideContent  .= '<tr>' . $element->render() . '</tr>';
            $leftSideContent  .= '</table>';
            $content          .= ZurmoHtml::tag('div', array('class' => 'panel'), $leftSideContent);
            $content          .= '</div>';
            return $content;
        }

        /**
         * @return string
         */
        protected function renderNextPageLinkContent()
        {
            $params = array();
            $params['label']       = Zurmo::t('WorkflowsModule', 'Save and Run');
            $params['htmlOptions'] = array('id' => static::getNextPageLinkId(), 'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $searchElement = new SaveButtonActionElement(null, null, null, $params);
            return $searchElement->render();
        }
    }
?>