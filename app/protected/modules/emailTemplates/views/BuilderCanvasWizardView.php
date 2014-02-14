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

    class BuilderCanvasWizardView extends ComponentForEmailTemplateWizardView
    {
        const REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID    = 'refresh-canvas-from-saved-template';

        const CACHED_SERIALIZED_DATA_ATTRIBUTE_NAME         = 'cachedSerializedData';

        const CANVAS_IFRAME_ID                              = 'canvas-iframne';

        const PREVIEW_IFRAME_ID                             = 'preview-iframe';

        const ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID        = 'element-edit-form-overlay-contaier';

        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('EmailTemplatesModule', 'Canvas');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'builderCanvasPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'builderCanvasSaveLink';
        }

        /**
         * @return string
         */
        public static function getFinishLinkId()
        {
            return 'builderCanvasFinishLink';
        }

        protected function renderNextPageLinkLabel()
        {
            return Zurmo::t('Core', 'Save');
        }

        protected function renderFinishLinkLabel()
        {
            return Zurmo::t('Core', 'Finish');
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            // TODO: @Shoaibi: Critical1: Load left sidebar and canvas here.
            // TODO: @Shoaibi: Critical1: Hidden elements for all serializedData Indexes.
            $hiddenElements                             = null;

            $leftSidebarContent                         = ZurmoHtml::tag('h3', array(), 'Elements');
            $leftSidebarContent                         .= $this->resolveElementsSidebarContent();
            $this->renderCachedSerializedDataHiddenField($hiddenElements);
            $this->renderHiddenElements($hiddenElements, $leftSidebarContent);
            $this->renderRefreshCanvasLink($leftSidebarContent);

            $rightSidebarContent                        = $this->resolveCanvasContent();

            $this->wrapContentForLeftSideBar($leftSidebarContent);
            $this->wrapContentForRightSideBar($rightSidebarContent);
            $content                                    = $leftSidebarContent . $rightSidebarContent;
            $content                                    = ZurmoHtml::tag('section', $this->resolveContentHtmlOptions(),
                                                                                    $content);
            $this->wrapContentForAttributesContainer($content);
            return $content;
        }

        protected function renderCachedSerializedDataHiddenField(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, static::CACHED_SERIALIZED_DATA_ATTRIBUTE_NAME, null);
        }

        protected function resolveCachedSerializedDataHiddenInputJQuerySelector()
        {
            return '#' . get_class($this->model) . '_' . static::CACHED_SERIALIZED_DATA_ATTRIBUTE_NAME;
        }

        protected function resolveContentHtmlOptions()
        {
            return array('id' => 'builder', 'class' => 'strong-right');
        }

        protected function renderRefreshCanvasLink(& $content)
        {
            $linkContent    = ZurmoHtml::link('Reload Canvas', '#', $this->resolveRefreshCanvasLinkHtmlOptions());
            $this->wrapContentInTableCell($linkContent, array('colspan' => 2));
            $this->wrapContentInTableRow($linkContent);
            $content            .= $linkContent;
        }

        protected function resolveRefreshCanvasLinkHtmlOptions()
        {
            return array('id' => static::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID, 'style' => 'display:none');
        }

        protected function renderActionLinksContent()
        {
            $content    = parent::renderActionLinksContent();
            $content    .= $this->renderFinishLinkContent();
            return $content;
        }

        protected function renderFinishLinkContent()
        {
            $params                = array();
            $params['label']       = $this->renderFinishLinkLabel();
            $params['htmlOptions'] = $this->resolveFinishLinkHtmlOptions();
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }

        protected function resolveFinishLinkHtmlOptions()
        {
            return array('id' => static::getFinishLinkId(), 'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
        }

        protected function generateWidgetTagsForUIAccessibleElements($uiAccessibleElements)
        {
            $content    = null;
            foreach ($uiAccessibleElements as $element)
            {
                $content    .=  $element::resolveDroppableWidget('li');
            }
            $content        = ZurmoHtml::tag('ul', $this->resolveWidgetTagsWrapperHtmlOptions(), $content);
            return $content;
        }

        protected function resolveWidgetTagsWrapperHtmlOptions()
        {
            return array('id' => 'building-blocks', 'class' => 'clearfix builder-elements builder-elements-droppable');
        }

        protected function resolveElementsSidebarContent()
        {
            $uiAccessibleElements   = PathUtil::getAllUIAccessibleBuilderElementClassNames();
            $content                = $this->generateWidgetTagsForUIAccessibleElements($uiAccessibleElements);
            $this->wrapContentInDiv($content, $this->resolveElementsSidebarHtmlOptions());
            $content                .= ZurmoHtml::tag('div', array('id' => static::ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID), '');
            $this->wrapContentInTableCell($content, array('colspan' => 2));
            $this->wrapContentInTableRow($content);
            return $content;
        }

        protected function resolveElementsSidebarHtmlOptions()
        {
            return array('id' => 'droppable-element-sidebar');
        }

        protected function resolveCanvasContent()
        {
            // TODO: @Shoaibi: Critical2: This is just dummy content to see how things will look so we can hook-in css and js.
            /*
            $canvasContent = '<iframe src="http://zurb.com/ink/downloads/templates/hero.html" id="preview-template"' .
                                ' seamless="seamless" frameborder="0" width="100%" height="100%"></iframe>';
            */
            $element        = new BuilderTextElement(true, 'dummyId', array("style" => array(
                                                                                "font-size" => "20px",
                                                                                "color" => "red",
                                                                                "background-color" => "#ccc"),
                                                                            'some-non-style-property' => 3));
            $canvasContent  = $element->renderNonEditable();
            $canvasContent  .= '<br/>';
            $canvasContent  .= $element->renderEditable();
            $this->wrapContentInDiv($canvasContent, array('id' => 'canvas'));
            return $canvasContent;
        }

        protected function resolveUiAccessibleContainerTypeElementClassNames($jsonEncoded = false)
        {
            $elements   = PathUtil::getAllUIAccessibleContainerTypeBuilderElementClassNames();
            if ($jsonEncoded)
            {
                return CJSON::encode($elements);
            }
            return $elements;
        }

        protected function resolveElementEditableActionUrl()
        {
            return $this->resolveRelativeUrl('renderElementEditable');
        }

        protected function resolveElementNonEditableActionUrl()
        {
            return $this->resolveRelativeUrl('renderElementNonEditable');
        }

        protected function resolveRelativeUrl($action)
        {
            return Yii::app()->createUrl($this->getModuleId() . '/' . $this->getControllerId() . '/' . $action);
        }

        protected function registerScripts()
        {
            // TODO: @Shoaibi/@Sergio: Critical5: Did i miss any JS here?
            parent::registerScripts();
            $this->registerRefreshCanvasFromSavedTemplateScript();
            $this->registerSetIsDraftToZeroOnClickingFinishScript();
            $this->registerBindElementNonEditableActionsOverlayScript();
            $this->registerElementDragAndDropScript();
            $this->registerPreviewModalScript();
            $this->registerSerializedDataCompilationFunctionsScript();
            $this->registerCanvasSaveScript();
            $this->registerCanvasFinishScript();
            $this->registerCanvasChangedScript();
        }

        protected function registerRefreshCanvasFromSavedTemplateScript()
        {
            Yii::app()->clientScript->registerScript('refreshCanvasFromSavedTemplateScript', "
                $('#" . static::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID . "').unbind('click');
                $('#" . static::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID . "').bind('click', function()
                {
                    // TODO: @Shoaibi: Critical2: Implement to refresh canvas div by making ajax to a url with templateId
                    return false;
                });
                ", CClientScript::POS_READY);
        }

        protected function registerSetIsDraftToZeroOnClickingFinishScript()
        {
            Yii::app()->clientScript->registerScript('setIsDraftToZeroOnClickingFinishScript', "
                $('#" . static::getFinishLinkId() . "').unbind('click.setIsDraftToZero');
                $('#" . static::getFinishLinkId() . "').bind('click.setIsDraftToZero', function()
                {
                    setIsDraftToZero();
                });
                ", CClientScript::POS_END);
        }

        protected function registerBindElementNonEditableActionsOverlayScript()
        {
            Yii::app()->clientScript->registerScript('bindElementNonEditableActionsOverlayScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: Add JS to bind element actions
                ", CClientScript::POS_READY);
        }

        protected function registerElementDragAndDropScript()
        {
            Yii::app()->clientScript->registerScript('elementDragAndDropScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: Add JS
                ", CClientScript::POS_READY);
        }

        protected function registerCanvasSaveScript()
        {
            Yii::app()->clientScript->registerScript('canvasSaveScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: Add JS
                // TODO: @Sergio/@Shoaibi: Critical2: What to do about: BuilderEmailTemplateWizardView:111
                ");
        }

        protected function registerCanvasFinishScript()
        {
            Yii::app()->clientScript->registerScript('canvasFinishScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: Add JS
                // TODO: @Sergio/@Shoaibi: Critical2: What to do about: BuilderEmailTemplateWizardView:115
                ");
        }

        protected function registerSerializedDataCompilationFunctionsScript()
        {
            Yii::app()->clientScript->registerScript('serializedDataCompilationFunctionsScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: Add JS
                ", CClientScript::POS_END);
        }

        protected function registerPreviewModalScript()
        {
            Yii::app()->clientScript->registerScript('previewModalScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: Add JS
                ");
        }

        protected function registerCanvasChangedScript()
        {
            Yii::app()->clientScript->registerScript('canvasChangedScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: Attach an event(canvasChanged) to window object. register event handler that clears the cached serialized data from hidden input
                ");
        }
    }
?>