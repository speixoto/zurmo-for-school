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

    class SelectBaseTemplateForEmailTemplateWizardView extends ComponentForEmailTemplateWizardView
    {
        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('EmailTemplatesModule', 'Select a Base Template');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'selectBaseTemplatePreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'selectBaseTemplateNextLink';
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $leftSideContent                            =  null;
            $hiddenElements                             = null;
            $this->renderSerializedDataHiddenFields($hiddenElements);

            $leftSideContent                            .= $this->renderSelectBaseTemplateFromPredefinedTemplates();
            $leftSideContent                            .= $this->renderSelectBaseTemplateFromPreviouslyCreatedTemplates();
            $this->renderHiddenElements($hiddenElements, $leftSideContent);

            $content                                    = $this->renderLeftAndRightSideBarContentWithWrappers($leftSideContent);
            return $content;
        }

        protected function renderSelectBaseTemplateFromPredefinedTemplates()
        {
            $elementClassName   = 'SelectBaseTemplateFromPredefinedTemplatesElement';
            $wrapperDivCssId    = 'select-base-template-from-predefined-templates';
            $heading            = 'Predefined Templates';
            $content            = $this->renderSelectBaseTemplateByElementName($elementClassName, $wrapperDivCssId, $heading);
            return $content;
        }

        protected function renderSelectBaseTemplateFromPreviouslyCreatedTemplates()
        {
            $elementClassName   = 'SelectBaseTemplateFromPreviouslyCreatedTemplatesElement';
            $wrapperDivCssId    = 'select-base-template-from-previously-created-templates';
            $heading            = 'My Templates';
            $content            = $this->renderSelectBaseTemplateByElementName($elementClassName, $wrapperDivCssId, $heading);
            return $content;
        }

        protected function renderSelectBaseTemplateByElementName($elementName, $wrapperDivCssId, $heading = null)
        {
            $element                    = new $elementName($this->model, 'baseTemplateId', $this->form);
            $content                    = "<h3>${heading}</h3>";
            $content                    .= $element->render();
            $content                    = ZurmoHtml::tag('ul', $this->resolveSelectBaseTemplateElementWrapperHtmlOptions(),
                                                        $content);
            $this->wrapContentInDiv($content, array('id' => $wrapperDivCssId));
            $this->wrapContentInTableCell($content, array('colspan' => 2));
            $this->wrapContentInTableRow($content);
            return $content;
        }

        protected function resolveSelectBaseTemplateElementWrapperHtmlOptions()
        {
            return array('class' => 'large-block-grid-3 small-block-grid-1 '.
                            'template-thumbs select-base-template-selection');
        }

        protected function renderSerializedDataHiddenFields(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'serializedData[thumbnailUrl]', null);

            $unserializedData   = unserialize($this->model->serializedData);
            $baseTemplateId     = (isset($unserializedData['baseTemplateId']))? $unserializedData['baseTemplateId'] : null;
            $this->renderHiddenField($hiddenElements, 'serializedData[baseTemplateId]', $baseTemplateId);
            $this->renderHiddenField($hiddenElements, 'originalBaseTemplateId', $baseTemplateId);
            $dom                = (isset($unserializedData['dom']))? $unserializedData['dom'] : null;
            $this->renderHiddenField($hiddenElements, 'serializedData[dom]', $dom);
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $this->registerUpdateBaseTemplateIdHiddenInputOnSelectionChangeScript();
            $this->registerPreSelectBaseTemplateScript();
        }

        protected function registerPreSelectBaseTemplateScript()
        {
            Yii::app()->clientScript->registerScript('preSelectBaseTemplateScript', "
                    baseTemplateId  = $('" . static::resolveBaseTemplateIdHiddenInputJQuerySelector() . "').val();
                    if (baseTemplateId == '')
                    {
                        baseTemplateId = $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . " :first').val();
                    }
                    $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . "[value=' + baseTemplateId +']').prop('checked', true);
                    // raise the click event so updateBaseTemplateIdHiddenInputOnSelectionChangeScript can take care of it.
                    $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . "[value=' + baseTemplateId +']').trigger('click');
            ", CClientScript::POS_READY);
        }

        protected function registerUpdateBaseTemplateIdHiddenInputOnSelectionChangeScript()
        {
            Yii::app()->clientScript->registerScript('updateBaseTemplateIdHiddenInputOnSelectionChangeScript', "
                function updateBaseTemplateIdHiddenInputValue(value)
                {
                    $('" . static::resolveBaseTemplateIdHiddenInputJQuerySelector() . "').val(value);
                }

                $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . "').unbind('click');
                $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . "').bind('click', function()
                {
                    originalBaseTemplateId  = $('" . $this->resolveOriginalBaseTemplateIdHiddenInputJQuerySelector() . "').val();
                    currentSelectedValue    = $(this).val();
                    // show warning only on edit when a user has already been to canvas once.
                    if (originalBaseTemplateId != '' && currentSelectedValue != originalBaseTemplateId)
                    {
                        if (!confirm('" . Zurmo::t('EmailTemplatesModule', 'Changing base template would trash any existing design made on canvas.') ."'))
                        {
                            return false;
                        }
                    }
                    updateBaseTemplateIdHiddenInputValue(currentSelectedValue);
                    return true;
                });
                ", CClientScript::POS_END);
        }

        protected function resolveBaseTemplateIdIputNameWithoutSerializedData()
        {
            $name   = ZurmoHtml::activeName($this->model, 'baseTemplateId');
            return $name;
        }

        protected function resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector()
        {
            $inputName          = $this->resolveBaseTemplateIdIputNameWithoutSerializedData();
            $selector           = ":radio[name^=\"${inputName}\"]";
            return $selector;
        }

        protected static function resolveBaseTemplateIdHiddenInputJQuerySelector()
        {
            $id = ZurmoHtml::activeId(new BuilderEmailTemplateWizardForm(), 'serializedData[baseTemplateId]');
            return '#' . $id;
        }

        protected static function resolveOriginalBaseTemplateIdHiddenInputJQuerySelector()
        {
            $id = ZurmoHtml::activeId(new BuilderEmailTemplateWizardForm(), 'originalBaseTemplateId');
            return '#' . $id;
        }

        public static function resolveAdditionalAjaxOptions($formName)
        {
            $ajaxArray                  = array();
            // TODO: @Shoaibi/@Amit/@Sergio/@Jason: Critical0: Shall we lock the page till success/error happens?
            $ajaxArray['success']       = "js:function(data)
                                            {
                                                originalBaseTemplateId  = $('" . static::resolveOriginalBaseTemplateIdHiddenInputJQuerySelector() . "').val();
                                                selectedBaseTemplateId  = $('" . static::resolveBaseTemplateIdHiddenInputJQuerySelector() . "').val();
                                                if (selectedBaseTemplateId != originalBaseTemplateId)
                                                {
                                                    $('#" . BuilderCanvasWizardView::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID . "').trigger('click');
                                                }
                                                $('" . static::resolveOriginalBaseTemplateIdHiddenInputJQuerySelector() . "').val(selectedBaseTemplateId);

                                            }";
            return $ajaxArray;
        }
    }
?>