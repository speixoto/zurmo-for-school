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
        const HIDDEN_ID = 'hiddenId';

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
            $this->renderId($hiddenElements);
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
            $this->renderHiddenField($hiddenElements, 'type', $this->model->type);
        }

        protected function renderBuiltType(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'builtType', $this->model->builtType);
        }

        protected function renderIsDraft(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'isDraft', (int)$this->model->isDraft);
        }

        protected function renderId(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, static::HIDDEN_ID, (int)$this->model->id);
        }

        protected function renderLanguage(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'language', $this->model->language);
        }

        protected function renderModelClassName(& $content, & $hiddenElements)
        {
            if ($this->model->isWorkflowTemplate())
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
            $params  = array('redactorPlugins' => "['mergeTags']");
            $element = new EmailTemplateHtmlAndTextContentElement($this->model, null, $this->form, $params);
            $element->editableTemplate  = '{label}{content}';
            $right = ZurmoHtml::tag('div', array('class' => 'email-template-combined-content'), $element->render());
            $right = ZurmoHtml::tag('td', array(), $right);
            //todo: placed last so redactor is already initialized first. just a trick for the css right now
            $title = ZurmoHtml::tag('h3', array(), Zurmo::t('Default', 'Merge Tags'));
            $left = $this->renderMergeTagsView();
            $left = ZurmoHtml::tag('th', array(), $title . $left);
            $content .= $left . $right;
        }

        protected function renderMergeTagsView()
        {
            $view = new MergeTagsView('EmailTemplate',
                                      get_class($this->model) . '_textContent',
                                      get_class($this->model) . '_htmlContent', false); //todo: get these last 2 values dynamically
            return $view->render();
        }

        /**
         * @return string
         */
        protected function renderRightSideFormLayout()
        {
            $elementEditableTemplate        = '{label}{content}{error}';
            $ownerElement                   = new OwnerNameIdElement($this->model, 'null', $this->form);
            $ownerElement->editableTemplate = $elementEditableTemplate;
            $ownerElementContent            = $ownerElement->render();
            $ownerElementContent            = ZurmoHtml::tag('div', array('id' => 'owner-box'), $ownerElementContent);

            $permissionsElement             = new EmailTemplateExplicitReadWriteModelPermissionsElement($this->model,
                                                                    'explicitReadWriteModelPermissions', $this->form);
            $permissionsElement->editableTemplate = $elementEditableTemplate;
            $permissionsElementContent      = $permissionsElement->render();
            $content                        = ZurmoHtml::tag('h3', array(), Zurmo::t('ZurmoModule', 'Rights and Permissions'));
            $content                        .= $ownerElementContent . $permissionsElementContent;
            return $content;
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $this->registerSetIsDraftToZeroScript();
            $this->registerTrashSomeDataOnModuleChangeScript();
        }

        protected function registerSetIsDraftToZeroScript()
        {
            Yii::app()->clientScript->registerScript('setIsDraftToZero', "
                function setIsDraftToZero()
                {
                    $('" . $this->resolveIsDraftHiddenInputJQuerySelector() ."').val(0);
                }
                ", CClientScript::POS_END);
        }

        protected function resolveIsDraftHiddenInputJQuerySelector()
        {
            $id = ZurmoHtml::activeId($this->model, 'isDraft');
            return '#' . $id;
        }

        protected function resolveModuleClassNameJQuerySelector()
        {
            $name               = ZurmoHtml::activeName($this->model, 'modelClassName');
            $selector           = "select[name^=\"${name}\"]";
            return $selector;
        }

        protected static function resolveTemplateIdHiddenInputJQuerySelector()
        {
            $id = ZurmoHtml::activeId(new BuilderEmailTemplateWizardForm(), static::HIDDEN_ID);
            return '#' . $id;
        }

        protected function registerTrashSomeDataOnModuleChangeScript()
        {
            if (!$this->model->isWorkflowTemplate())
            {
                return;
            }
            Yii::app()->clientScript->registerScript('trashSomeDataOnModuleChangeScript', "
                $('" . $this->resolveModuleClassNameJQuerySelector() . "').unbind('change.trashSomeDataOnModuleChange');
                $('" . $this->resolveModuleClassNameJQuerySelector() . "').bind('change.trashSomeDataOnModuleChange', function()
                {
                    $('#" . ZurmoHtml::activeId($this->model, 'textContent') . "').val('');
                    if (" . intval($this->model->isPastedHtmlTemplate()) . ")
                    {
                        // TODO: @Shoaibi/@Sergio: Critical2: How to do this.
                        //var htmlContentId       = '" . ZurmoHtml::activeId($this->model, 'htmlContent') . "';
                        //var htmlContentElement  = $('#' + htmlContentId);
                        //htmlContentElement.val('');
                        //$('.redactor_editor').html('');
                    }

                    else if (" . intval($this->model->isBuilderTemplate()) . ")
                    {
                        $('" . $this->resolveIsDraftHiddenInputJQuerySelector() ."').val(1);
                        resetBaseTemplateId();
                        resetOriginalBaseBaseTemplateId();
                        resetSerializedDomData();
                        var params  = JSON.parse('{ \"modelClassName\" : \"' + $(this).val() + '\" }');
                        reloadPreviouslyCreatedTemplates(params)
                        preSelectBaseTemplate();
                    }
                });
                ");
        }

        public static function resolveAdditionalAjaxOptions($formName)
        {
            // TODO: @Shoaibi/@Amit/@Sergio/@Jason: Critical0: Shall we lock the page till success/error happens?
            $ajaxArray                  = parent::resolveAdditionalAjaxOptions($formName);
            $ajaxArray['success']       = "js:function(data)
                                            {
                                                if ('create' == '" . Yii::app()->getController()->getAction()->getId() . "')
                                                {
                                                    //update id
                                                    $('" . static::resolveTemplateIdHiddenInputJQuerySelector() . "').val(data.id);
                                                }
                                            }";
            return $ajaxArray;
        }

        public static function resolveCanvasActionUrl()
        {
            return Yii::app()->createUrl('emailTemplates/default/renderCanvas', array('id' => 0));
        }
    }
?>