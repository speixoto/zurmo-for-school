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
        const PREDEFINED_TEMPLATES_DIV_ID                       = 'select-base-template-from-predefined-templates';

        const PREDEFINED_TEMPLATES_ELEMENT_CLASS_NAME           = 'SelectBaseTemplateFromPredefinedTemplatesElement';

        const PREVIOUSLY_CREATED_TEMPLATES_DIV_ID               = 'select-base-template-from-previously-created-templates';

        const PREVIOUSLY_CREATED_TEMPLATES_ELEMENT_CLASS_NAME   = 'SelectBaseTemplateFromPreviouslyCreatedTemplatesElement';

        const BASE_TEMPLATE_RADIO_BUTTON_ATTRIBUTE_NAME         = 'baseTemplateId';

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

            $content                                    = $leftSideContent;
            return $content;
        }

        protected function renderSelectBaseTemplateFromPredefinedTemplates()
        {
            $elementClassName   = static::PREDEFINED_TEMPLATES_ELEMENT_CLASS_NAME;
            $wrapperDivCssId    = static::PREDEFINED_TEMPLATES_DIV_ID;
            $heading            = Zurmo::t('EmailTemplatesModule', 'Predefined Templates');
            $content            = $this->renderSelectBaseTemplateByElementName($elementClassName, $wrapperDivCssId, $heading);
            return $content;
        }

        protected function renderSelectBaseTemplateFromPreviouslyCreatedTemplates()
        {
            $elementClassName   = static::PREVIOUSLY_CREATED_TEMPLATES_ELEMENT_CLASS_NAME;
            $wrapperDivCssId    = static::PREVIOUSLY_CREATED_TEMPLATES_DIV_ID;
            $heading            = Zurmo::t('EmailTemplatesModule', 'My Templates');
            $content            = $this->renderSelectBaseTemplateByElementName($elementClassName, $wrapperDivCssId, $heading);
            return $content;
        }

        protected function renderSelectBaseTemplateByElementName($elementName, $wrapperDivCssId, $heading = null)
        {
            $element = new $elementName($this->model, static::BASE_TEMPLATE_RADIO_BUTTON_ATTRIBUTE_NAME, $this->form);
            if(null != $content = $element->render())
            {
                $content = ZurmoHtml::tag('ul', array('class' => 'clearfix'), $content);
                $content = "<h3>${heading}</h3>" . $content;
                $this->wrapContentInDiv($content, array('id' => $wrapperDivCssId, 'class' => 'templates-chooser-list clearfix'));
            }
            return $content;
        }

        protected function renderSerializedDataHiddenFields(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'serializedData[thumbnailUrl]', null);

            $unserializedData   = CJSON::decode($this->model->serializedData);
            $baseTemplateId     = (isset($unserializedData['baseTemplateId']))? $unserializedData['baseTemplateId'] : null;
            $this->renderHiddenField($hiddenElements, 'serializedData[baseTemplateId]', $baseTemplateId);
            $this->renderHiddenField($hiddenElements, 'originalBaseTemplateId', $baseTemplateId);
            $this->renderHiddenField($hiddenElements, BuilderCanvasWizardView::CACHED_SERIALIZED_DATA_ATTRIBUTE_NAME . '[dom]', null);
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $this->registerUpdateBaseTemplateIdHiddenInputOnSelectionChangeScript();
            $this->registerPreSelectBaseTemplateScript();
            $this->registerPopulateBaseTemplatesScript();
            $this->registerUpdateBaseTemplatesByDivIdScript();
            $this->registerReloadPreviouslyCreatedTemplatesScript();
            $this->registerResetBaseTemplateIdScript();
            $this->registerResetOriginalBaseTemplateIdScript();
            $this->registerResetSerializedDomDataScript();
        }

        protected function registerResetBaseTemplateIdScript()
        {
            Yii::app()->clientScript->registerScript('resetBaseTemplateIdScript', "
                function resetBaseTemplateId()
                {
                    $('" . static::resolveBaseTemplateIdHiddenInputJQuerySelector() . "').val('');
                }
            ", CClientScript::POS_HEAD);
        }

        protected function registerResetOriginalBaseTemplateIdScript()
        {
            Yii::app()->clientScript->registerScript('resetOriginalBaseBaseTemplateIdScript', "
                function resetOriginalBaseBaseTemplateId()
                {
                    $('" . static::resolveOriginalBaseTemplateIdHiddenInputJQuerySelector() . "').val('');
                }
            ", CClientScript::POS_HEAD);
        }

        protected function registerResetSerializedDomDataScript()
        {
            Yii::app()->clientScript->registerScript('resetSerializedDomDataScript', "
                function resetSerializedDomData()
                {
                    $('" . $this->resolveSerializedDomDataHiddenInputJQuerySelector() . "').val('');
                }
            ", CClientScript::POS_HEAD);
        }

        protected function registerPreSelectBaseTemplateScript()
        {
            Yii::app()->clientScript->registerScript('preSelectBaseTemplateScript', "
                function preSelectBaseTemplate()
                {
                    baseTemplateId  = $('" . static::resolveBaseTemplateIdHiddenInputJQuerySelector() . "').val();
                    if (baseTemplateId == '')
                    {
                        baseTemplateId = $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . " :first').val();
                    }
                    $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . "[value=' + baseTemplateId +']').prop('checked', true);
                    // raise the click event so updateBaseTemplateIdHiddenInputOnSelectionChangeScript can take care of it.
                    $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . "[value=' + baseTemplateId +']').trigger('click');
                }
                preSelectBaseTemplate();
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

        protected function registerPopulateBaseTemplatesScript()
        {
            Yii::app()->clientScript->registerScript('populateBaseTemplatesScript', "
                function populateBaseTemplates(elementClassName, elementModelClassName, elementAttributeName, elementFormClassName, elementParams, divId)
                {
                    var requestData    = { elementClassName: elementClassName, elementModelClassName: elementModelClassName,
                                    elementAttributeName: elementAttributeName, elementFormClassName: elementFormClassName,
                                    elementParams: elementParams };

                    " . ZurmoHtml::ajax($this->resolvePopulateBaseTemplateAjaxOptions()) . "
                }", CClientScript::POS_HEAD);
        }

        protected function resolvePopulateBaseTemplateAjaxOptions()
        {
            $ajaxArray                  = array();
            $ajaxArray['cache']         = 'false';
            $ajaxArray['url']           = $this->resolveBaseTemplateOptionsUrl();
            $ajaxArray['type']          = 'GET';
            $ajaxArray['data']          = "js:requestData";
            $ajaxArray['success']       = "js:function(data, status, request)
                                        {
                                            updateBaseTemplatesByDivId(divId, data);
                                        }";
            return $ajaxArray;
        }

        protected function registerUpdateBaseTemplatesByDivIdScript()
        {
            Yii::app()->clientScript->registerScript('updateBaseTemplatesByDivIdScript', "
                function updateBaseTemplatesByDivId(divId, data)
                {
                    $('div#' + divId + ' ul').html(data);
                }", CClientScript::POS_HEAD);
        }

        protected function registerReloadPreviouslyCreatedTemplatesScript()
        {
            Yii::app()->clientScript->registerScript('reloadPreviouslyCreatedTemplatesScript', "
                function reloadPreviouslyCreatedTemplates(elementParams)
                {
                    elementClassName        = '" . static::PREVIOUSLY_CREATED_TEMPLATES_ELEMENT_CLASS_NAME . "';
                    elementModelClassName   = '" . get_class($this->model) . "';
                    elementAttributeName    = '" . static::BASE_TEMPLATE_RADIO_BUTTON_ATTRIBUTE_NAME. "';
                    elementFormClassName    = '" . get_class($this->form) . "';
                    divId                   = '" . static::PREVIOUSLY_CREATED_TEMPLATES_DIV_ID . "';
                    populateBaseTemplates(elementClassName, elementModelClassName, elementAttributeName, elementFormClassName, elementParams, divId);
                }", CClientScript::POS_HEAD);
        }

        protected function resolveBaseTemplateOptionsUrl()
        {
            return $this->resolveRelativeUrl('renderBaseTemplateOptions');
        }

        protected function resolveBaseTemplateIdInputNameWithoutSerializedData()
        {
            $name   = ZurmoHtml::activeName($this->model, static::BASE_TEMPLATE_RADIO_BUTTON_ATTRIBUTE_NAME);
            return $name;
        }

        protected function resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector()
        {
            $inputName          = $this->resolveBaseTemplateIdInputNameWithoutSerializedData();
            $selector           = ":radio[name^=\"${inputName}\"]";
            return $selector;
        }

        protected function resolveSerializedDomDataHiddenInputJQuerySelector()
        {
            $id = ZurmoHtml::activeId($this->model, 'serializedData[dom]');
            return '#' . $id;
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
            // TODO: @Shoaibi/@Amit/@Sergio/@Jason: Critical0: Shall we lock the page till success/error happens?
            $ajaxArray                                      = parent::resolveAdditionalAjaxOptions($formName);
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