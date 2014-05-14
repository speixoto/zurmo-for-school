<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * The view for lead conversion, no account, just shows a complete
     * conversion button.
     */
    class LeadConvertAccountSkipView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        public function __construct($controllerId, $moduleId, $modelId)
        {
            $this->controllerId   = $controllerId;
            $this->moduleId       = $moduleId;
            $this->modelId        = $modelId;
        }

        /**
         * Renders content for a view.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $content = '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'NoRequiredsActiveForm',
                                                                array('id' => static::getFormId(),
                                                                      'enableAjaxValidation' => false,
                                                                      'htmlOptions' => $this->resolveFormHtmlOptions())
                                                            );
            $content .= $formStart;
            $content .= $this->renderFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div>';
            return $content;
        }

        protected function renderFormLayout($form = null)
        {
            $content  = '<table class="form-fields">';
            $content .= '<colgroup>';
            $content .= '<col style="width:100%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr>';
            $content .= '<th>' . Zurmo::t('LeadsModule', 'Complete LeadsModuleSingularLowerCaseLabel conversion without ' .
                                                   'selecting or creating an AccountsModuleSingularLowerCaseLabel.',
                                                   LabelUtil::getTranslationParamsForAllModules()) . '</th>';
            $content .= '</tr>';
            $content .= '</tbody>';
            $content .= '</table>';
            $cancelLink = new CancelConvertLinkActionElement($this->controllerId, $this->moduleId, $this->modelId);
            $content .= '<div class="view-toolbar-container clearfix"><div class="form-toolbar">';
            $element  =   new SaveButtonActionElement($this->controllerId, $this->moduleId,
                                                      null,
                                                      array('htmlOptions' =>
                                                          array('name'   => 'AccountSkip', 'id' => 'AccountSkip',
                                                                'params' => array('AccountSkip' => true)),
                                                                'label'  => Zurmo::t('ZurmoModule', 'Complete Conversion')));
            $content .= $element->render();
            $content .= $cancelLink->render();
            $content .= '</div></div>';
            return $content;
        }

        protected static function getFormId()
        {
            return 'account-skip-form';
        }

        protected function resolveFormHtmlOptions()
        {
            $data = array('onSubmit' => 'js:return $(this).attachLoadingOnSubmit("' . static::getFormId() . '")');
            return $data;
        }
    }
?>
