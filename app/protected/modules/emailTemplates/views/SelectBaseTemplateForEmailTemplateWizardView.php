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
        protected static $defaultRadioElementEditableTemplate;

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
            $this->registerScripts();
            static::$defaultRadioElementEditableTemplate = '{content}';
            $leftSideContent                            =  '<table><colgroup><col class="col-0"><col class="col-1">' .
                                                            '</colgroup>';
            $hiddenElements                             = null;
            $this->renderSerializedDataHiddenFields($hiddenElements);
            $this->wrapContentInTableCell($hiddenElements, array('colspan' => 2));
            $this->wrapContentInTableRow($hiddenElements);

            $leftSideContent                            .= $this->renderSelectBaseTemplateFromPredefinedTemplates();
            $leftSideContent                            .= $this->renderSelectBaseTemplateFromPreviouslyCreatedTemplates();
            $leftSideContent                            .= $hiddenElements;
            $leftSideContent                            .= '</table>';
            $this->wrapContentInDiv($leftSideContent, array('class' => 'panel'));
            $this->wrapContentInDiv($leftSideContent, array('class' => 'left-column'));

            $content                                    = '<div class="attributesContainer">';
            $content                                    .= $leftSideContent;
            $content                                    .= '</div>';
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
            $element->editableTemplate  = static::$defaultRadioElementEditableTemplate;
            $content                    = "<h3>${heading}</h3>";
            $content                    .= $element->render();
            $content                    = ZurmoHtml::tag('ul', array('class' => 'large-block-grid-3 small-block-grid-1 '.
                                                                    'template-thumbs select-base-template-selection'),
                                                        $content);
            $this->wrapContentInDiv($content, array('id' => $wrapperDivCssId));
            $this->wrapContentInTableCell($content, array('colspan' => 2));
            $this->wrapContentInTableRow($content);
            return $content;
        }

        protected function renderSerializedDataHiddenFields(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'serializedData[thumbnailUrl]', null);

            $unserializedData   = unserialize($this->model->serializedData);
            $baseTemplateId     = (isset($unserializedData['baseTemplateId']))? $unserializedData['baseTemplateId'] : null;
            $this->renderHiddenField($hiddenElements, 'serializedData[baseTemplateId]', $baseTemplateId);
            $dom                = (isset($unserializedData['dom']))? $unserializedData['dom'] : null;
            $this->renderHiddenField($hiddenElements, 'serializedData[dom]', $dom);
            $properties         = (isset($unserializedData['properties']))? $unserializedData['properties'] : null;
            $this->renderHiddenField($hiddenElements, 'serializedData[properties]', $properties);
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
                    baseTemplateId  = $('" . $this->resolveBaseTemplateIdHiddenInputJQuerySelector() . "').val();
                    if (baseTemplateId == '')
                    {
                        baseTemplateId = $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . " :first').val();
                        // TODO: @Shoaibi/@Jason/@Nabil: Critical0: we should not need to even call this, there is an even for it.
                        updateBaseTemplateIdHiddenInputValue(baseTemplateId);
                    }
                    $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . "[value=' + baseTemplateId +']').prop('checked', true);
            ", CClientScript::POS_READY);
        }

        protected function registerUpdateBaseTemplateIdHiddenInputOnSelectionChangeScript()
        {
            Yii::app()->clientScript->registerScript('updateBaseTemplateIdHiddenInputOnSelectionChangeScript', "
                function updateBaseTemplateIdHiddenInputValue(value)
                {
                    $('" . $this->resolveBaseTemplateIdHiddenInputJQuerySelector() . "').val(value);
                    console.log('Updated to ' + value);
                }

                $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . "').unbind('change');
                $('" . $this->resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector() . "').bind('change', function()
                {
                    updateBaseTemplateIdHiddenInputValue($(this).val());
                }
                );", CClientScript::POS_END);
        }

        protected function resolveBaseTemplateIdIputNameWithoutSerializedData()
        {
            return get_class($this->model) . '[baseTemplateId]';
        }

        protected function resolveBaseTemplateIdRadioInputWithoutSerializedDataJQuerySelector()
        {
            $inputName          = $this->resolveBaseTemplateIdIputNameWithoutSerializedData();
            $selector           = ":radio[name^=\"${inputName}\"]";
            return $selector;
        }

        protected function resolveBaseTemplateIdHiddenInputJQuerySelector()
        {
            return '#' . get_class($this->model) .'_serializedData_baseTemplateId';
        }
    }
?>