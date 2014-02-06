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

    class GeneralDataForEmailTemplateWizardView extends ComponentForEmailTemplateWizardView
    {
        protected $defaultTextAndDropDownElementEditableTemplate    = '<th>{label}<span class="required">*</span></th><td colspan="{colspan}">{content}</td>';

        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('Core', 'General');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'generalDataCancelLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'generalDataSaveAndRunLink';
        }

        protected function renderNextPageLinkLabel()
        {
            if ($this->model->builtType != EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE)
            {
                return Zurmo::t('Core', 'Save');
            }
            return parent::renderNextPageLinkLabel();
        }

        protected function renderPreviousPageLinkLabel()
        {
            return Zurmo::t('Core', 'Cancel');
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            // TODO: @Shoaibi/@Jason/@Amit: Critical: Everything red, property set on panel, wrong.
            $leftSideContentPrefix                      = $this->form->errorSummary($this->model);
            $leftSideContent                            = null;
            $hiddenElements                             = null;

            $this->renderType($hiddenElements);
            $this->renderBuiltType($hiddenElements);
            $this->renderIsDraft($hiddenElements);
            $this->renderLanguage($hiddenElements);
            $this->renderModelClassName($leftSideContent, $hiddenElements);
            $this->renderName($leftSideContent);
            $this->renderSubject($leftSideContent);
            $this->renderFiles($leftSideContent);
            $this->renderPlainTextAndHtmlContent($leftSideContent);
            $this->renderHiddenElements($hiddenElements, $leftSideContent);

            $rightSideContent                           = $this->renderRightSideFormLayout();

            $content                                    = $this->renderLeftAndRightSideBarContentWithWrappers(
                                                                                                $leftSideContent,
                                                                                                $rightSideContent,
                                                                                                $leftSideContentPrefix);
            return $content;
        }

        protected function renderName(& $content)
        {
            $this->renderTextElement($content, 'name', $this->defaultTextAndDropDownElementEditableTemplate);
        }

        protected function renderSubject(& $content)
        {
            $this->renderTextElement($content, 'subject', $this->defaultTextAndDropDownElementEditableTemplate);
        }

        protected function renderType(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'type', Yii::app()->request->getQuery('type'));
        }

        protected function renderBuiltType(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'builtType', Yii::app()->request->getQuery('builtType'));
        }

        protected function renderIsDraft(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'isDraft', (int)$this->model->isDraft);
        }

        protected function renderLanguage(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'language', $this->model->language);
        }

        protected function renderModelClassName(& $content, & $hiddenElements)
        {
            if ($this->model->type == EmailTemplate::TYPE_WORKFLOW)
            {
                $element                    = new EmailTemplateModelClassNameElement($this->model,
                                                                                        'modelClassName',
                                                                                        $this->form);
                $element->editableTemplate  = $this->defaultTextAndDropDownElementEditableTemplate;
                $modelClassNameContent      = $element->render();
                $this->wrapContentInTableRow($modelClassNameContent);
                $content                    .= $modelClassNameContent;
            }
            else
            {
                $this->renderHiddenField($hiddenElements, 'modelClassName', 'Contact');
            }
        }

        protected function renderFiles(& $content)
        {
            $element            = new FilesElement($this->model, null, $this->form);
            $this->wrapContentInDiv($filesContent);
            $filesContent       = $element->render();
            $this->wrapContentInTableRow($filesContent);
            $content            .= $filesContent;
        }

        protected function renderPlainTextAndHtmlContent(& $content)
        {
            $element = new EmailTemplateHtmlAndTextContentElement($this->model, null, $this->form);
            $element->editableTemplate  = '{label}{content}';
            $contentAreasContent        = $element->render();
            $this->wrapContentInDiv($contentAreasContent, array('class' => 'email-template-combined-content'));
            $contentAreasContent = ZurmoHtml::tag('td', array('colspan' => 2), $contentAreasContent);
            $this->wrapContentInTableRow($contentAreasContent);
            $content            .= $contentAreasContent;
        }

        /**
         * @return string
         */
        protected function renderRightSideFormLayout()
        {
            $content  = '<h3>' . Zurmo::t('ZurmoModule', 'Rights and Permissions') . '</h3><div id="owner-box">';
            $element  = new OwnerNameIdElement($this->model, 'null', $this->form);
            $element->editableTemplate = '{label}{content}{error}';
            $content .= $element->render().'</div>';
            $element  = new EmailTemplateExplicitReadWriteModelPermissionsElement($this->model,
                                                                    'explicitReadWriteModelPermissions', $this->form);
            $element->editableTemplate = '{label}{content}{error}';
            $content .= $element->render();
            return $content;
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $this->registerSetIsDraftToZeroScript();
        }

        protected function registerSetIsDraftToZeroScript()
        {
            Yii::app()->clientScript->registerScript('setIsDraftToZero', "
                function setIsDraftToZero()
                {
                    $('" . $this->resolveIsDraftdHiddenInputJQuerySelector() ."').val(0);
                }
                ", CClientScript::POS_END);
        }

        protected function resolveIsDraftdHiddenInputJQuerySelector()
        {
            return '#' . get_class($this->model) .'_isDraft';
        }
    }
?>