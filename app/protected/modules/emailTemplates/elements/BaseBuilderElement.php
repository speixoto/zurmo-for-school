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

    abstract class BaseBuilderElement
    {
        /**
         * class name for move action link
         */
        const OVERLAY_ACTION_MOVE   = 'action-move';

        /**
         * class name for edit action link
         */
        const OVERLAY_ACTION_EDIT    = 'action-edit';

        /**
         * class name for delete action link
         */
        const OVERLAY_ACTION_DELETE  = 'action-delete';

        /**
         * @var string Id of current element, unique.
         */
        protected $id;

        /**
         * @var array properties such as style
         */
        protected $properties;

        /**
         * @var array actual content.
         */
        protected $content;

        /**
         * @var array extra parameters
         */
        protected $params;

        /**
         * @var object contains model for forms
         */
        protected $model;

        /**
         * @var bool if this element is being rendered for canvas or not.
         * Non-editable rendering behavior varies depending on this.
         * @see resolveCustomDataAttributesNonEditable()
         * @see resolveNonEditableActions()
         */
        protected $renderForCanvas = false;

        /**
         * @return bool If this element should be shown on the drag-n-drop sidebar.
         */
        public static function isUIAccessible()
        {
            return false;
        }

        /**
         * Generate the widget html definition to be put on the left sidebar of drag-n-drop elements.
         * @param string $widgetWrapper the html wrapper tag to use for widget html. Defaults to li.
         * @return string
         */
        public static final function resolveDroppableWidget($widgetWrapper = 'li')
        {
            $label = static::resolveLabel();
            $label = ZurmoHtml::tag('span', array(), $label);
            //TODO: @sergio Do we need resolveThumbnailBaseUrl resolveThumbnailName resolveThumbnailUrl anymore
            // we may need a resolveThumbnailName that will be useded to the icon content or we will use the htmlOptions
            $icon  = ZurmoHtml::tag('i', static::resolveThumbnailHtmlOptions(), 'z');
            $widget  = ZurmoHtml::tag('div', array('class' => 'clearfix'), $icon . $label);
            return ZurmoHtml::tag($widgetWrapper, static::resolveWidgetHtmlOptions(), $widget);
        }

        /**
         * Return true for container type elements
         * @return bool
         */
        public static function isContainerType()
        {
            return false;
        }

        /**
         * Return the name of model to use with the form in editable representation
         * @return string
         */
        public static final function getModelClassName()
        {
            return 'BuilderElementEditableModelForm';
        }

        /**
         * Return translated label for current Element.
         * @throws NotImplementedException
         */
        protected static function resolveLabel()
        {
            throw new NotImplementedException('Children element should specify their own label');
        }

        /**
         * Returns the relative url to the directory containing element thumbnails.
         * @return string
         */
        protected static final function resolveThumbnailBaseUrl()
        {
            return Yii::app()->themeManager->baseUrl . '/default/email-templates/elements/';
        }

        /**
         * Returns the element thumbnail name.
         * @return string
         */
        protected static final function resolveThumbnailName()
        {
            return strtolower(get_called_class()) . '.png';
        }

        /**
         * Returns the relative thumbnail url
         * @return string
         */
        protected static final function resolveThumbnailUrl()
        {
            return static::resolveThumbnailBaseUrl() . static::resolveThumbnailName();
        }

        /**
         * Returns html options to be applied to element thumbnail
         * @return array
         */
        protected static function resolveThumbnailHtmlOptions()
        {
            return array('class' => 'icon-z');
        }

        /**
         * Returns html options to be applied to element's widget html.
         * @return array
         */
        protected static function resolveWidgetHtmlOptions()
        {
            return  array('data-class' => get_called_class(), 'class' => 'builder-element builder-element-droppable');
        }

        /**
         * @param bool $renderForCanvas whether element is being rendered for canvas or not.
         * @param null $id the html dom id.
         * @param null $properties properties for this element, style and such.
         * @param null $content content for this element.
         * @param null $params
         */
        public function __construct($renderForCanvas = false, $id = null, $properties = null, $content = null, $params = null)
        {
            $this->renderForCanvas  = $renderForCanvas;
            $this->initId($id);
            $this->initproperties($properties);
            $this->initContent($content);
            $this->initParams($params);
            $this->initModel();
        }

        /**
         * Render current element as nonEditable with all the bells and whistles
         * @return string
         */
        public final function renderNonEditable()
        {
            $this->registerNonEditableSnippets();
            $elementContent = $this->renderControlContentNonEditable();
            $wrappedContent = $this->renderControlWrapperNonEditable($elementContent);
            return $wrappedContent;
        }

        /**
         * Rending current element's editable representation
         * @return string
         */
        public final function renderEditable()
        {
            if ($this->doesNotSupportEditable())
            {
                throw new NotSupportedException('This element does not support editable representation');
            }
            $formTitle                  = $this->resolveFormatterFormTitle();
            $formContent                = $this->renderFormContent();
            $content                    = $formTitle . $formContent;
            $content                    = ZurmoHtml::tag('div', array('class' => 'element-edit-form-overlay'), $content);
            return $content;
        }

        /**
         * If this element should ever be rendered editable
         * @return bool
         */
        protected function doesNotSupportEditable()
        {
            return false;
        }

        /**
         * Register snippets(javascript, css, etc) required for non-editable view of this element.
         */
        protected function registerNonEditableSnippets()
        {
            $this->registerNonEditableScripts();
            $this->registerNonEditableCss();
        }

        /**
         * Register javascript snippets required for non-editable view of this element.
         */
        protected function registerNonEditableScripts()
        {

        }

        /**
         * Register css snippets required for non-editable view of this element.
         */
        protected function registerNonEditableCss()
        {

        }

        /**
         * Returns the non-editable output for current element.
         * @return string
         */
        protected function renderControlContentNonEditable()
        {
            $content    = $this->renderContentElement(null);
            return $content;
        }

        /**
         * Render current element nonEditable with its wrapper including custom data attributes, properties and overlay actions.
         * @param string $elementContent
         * @return string
         */
        protected final function renderControlWrapperNonEditable($elementContent = '{{dummyContent}}')
        {
            $customDataAttributes   = $this->resolveCustomDataAttributesNonEditable();
            $properties             = $this->resolvePropertiesNonEditable();
            $actionsOverlay         = $this->resolveNonEditableActions();
            $content                = $this->resolveWrapperNonEditable($elementContent, $properties, $customDataAttributes, $actionsOverlay);
            return $content;
        }

        /**
         * Render the actual wrapper for nonEditable representation bundling provided information.
         * @param $elementContent
         * @param $properties
         * @param $customDataAttributes
         * @param $actionsOverlay
         * @return string
         */
        protected function resolveWrapperNonEditable($elementContent, $properties, $customDataAttributes, $actionsOverlay)
        {
            $contentSuffix  = null;
            $htmlOptions    = CMap::mergeArray($properties, $customDataAttributes);
            $content        = $elementContent;
            if (!empty($actionsOverlay))
            {
                    $contentSuffix  .= $actionsOverlay;
            }
            $content    = $this->resolveWrapperNonEditableByContentAndHtmlOptions($content, $htmlOptions);
            if ($contentSuffix !== null)
            {
                $content    .= $contentSuffix;
                $content    = $this->wrapNonEditableElementContent($content);
            }
            return $content;
        }

        /**
         * Wrap non-editable content of element into a wrapper
         * @param $content
         * @return string
         */
        protected function wrapNonEditableElementContent($content)
        {
            $content    = ZurmoHtml::tag('div', array('class' => 'element-wrapper'), $content);
            return $content;
        }

        /**
         * Resolve and return html options of Element wrapper.
         * @return array
         */
        protected function resolveNonEditableElementWrapperHtmlOptions()
        {
            return array('class' => 'element-wrapper');
        }

        /**
         * Resolve and return wrapper using provided content and html options for non-editable representation
         * @param $content
         * @param array $htmlOptions
         * @return string
         */
        protected function resolveWrapperNonEditableByContentAndHtmlOptions($content, array $htmlOptions)
        {
            $content        = ZurmoHtml::tag('div', $htmlOptions, $content);
            return $content;
        }

        /**
         * Resolve element's properties for nonEditable representation.
         * @return string
         */
        protected final function resolvePropertiesNonEditable()
        {
            $properties = array();
            if (isset($this->properties['frontend']))
            {
                // we are not on canvas, may be preview or just generating final newsletter.
                // do not render backend properties.
                $properties = $this->properties['frontend'];
            }
            if ($this->renderForCanvas && isset($this->properties['backend']))
            {
                $properties = CMap::mergeArray($properties, $this->properties['backend']);
            }
            $mergedProperties   = CMap::mergeArray($this->resolveNonEditableWrapperHtmlOptions(), $properties);
            $this->resolveStylePropertiesNonEditable($mergedProperties);
            return $mergedProperties;
        }

        /**
         * Resolve style properties to be applied to nonEditable representation's wrapper as inline style
         * @param array $mergedProperties
         */
        protected final function resolveStylePropertiesNonEditable(array & $mergedProperties)
        {
            if (isset($mergedProperties['style']))
            {
                $mergedProperties['style']  = $this->stringifyProperties($mergedProperties['style'], null, null, ':', ';');
            }
        }

        /**
         * Stringify properties by combing keys and values using a set of prefixes and suffices.
         * @param array $properties
         * @param null $keyPrefix
         * @param null $keySuffix
         * @param null $valuePrefix
         * @param null $valueSuffix
         * @return null|string
         */
        protected final function stringifyProperties(array $properties, $keyPrefix = null, $keySuffix = null,
                                                        $valuePrefix = null, $valueSuffix = null)
        {
            $content    = $this->stringifyArray($properties, $keyPrefix, $keySuffix, $valuePrefix, $valueSuffix);
            return $content;
        }

        /**
         * Stringify an array by combining keys and value using a set of prefixes and suffices.
         * @param array $array
         * @param null $keyPrefix
         * @param null $keySuffix
         * @param null $valuePrefix
         * @param null $valueSuffix
         * @return null|string
         */
        protected final function stringifyArray(array $array, $keyPrefix = null, $keySuffix = null,
                                                    $valuePrefix = null, $valueSuffix = null)
        {
            $content    = null;
            foreach ($array as $key => $value)
            {
                $content .= $keyPrefix . $key . $keySuffix . $valuePrefix . $value . $valueSuffix;
            }
            return $content;
        }

        /**
         * Resolve the custom data attributes for nonEditable representation wrapper.
         * @return null|string
         */
        protected final function resolveCustomDataAttributesNonEditable()
        {
            if (!$this->renderForCanvas)
            {
                return array();
            }
            $cda['data-class']      = get_class($this);
            $cda['data-properties'] = serialize($this->properties);
            $cda['data-content']    = serialize(array());
            if (!$this->isContainerType())
            {
                // we don't want to bloat container type's data-content as it would be recompiled anyway.
                $cda['data-content']    = serialize($this->content);
            }
            return $cda;
        }

        /**
         * Resolve the nonEditable representation's overlay actions for wrapper.
         * @return null|string
         */
        protected final function resolveNonEditableActions()
        {
            if (!$this->renderForCanvas)
            {
                return null;
            }
            $overlayLinksContent    = $this->resolveAvailableNonEditableActionLinkContent();
            $overlayContent         = ZurmoHtml::tag('div', $this->resolveNonEditableActionsHtmlOptions(), $overlayLinksContent);
            return $overlayContent;
        }

        /**
         * Resolve html options for the nonEditable representation's overlay actions container.
         * @return array
         */
        protected function resolveNonEditableActionsHtmlOptions()
        {
            return array('class' => 'builder-element-toolbar',
                            'id' => 'element-actions-' . $this->id);
        }

        /**
         * Resolve the nonEditable representation's overlay action items combined together.
         * @return null|string
         */
        protected final function resolveAvailableNonEditableActionLinkContent()
        {
            $availableActions   = $this->resolveAvailableNonEditableActionsArray();
            $overlayLinkContent = null;
            foreach ($availableActions as $action)
            {
                $iconContent        = ZurmoHtml::tag('i', array('class' => 'icon-' . $action), '');
                $linkContent        = ZurmoHtml::tag('span', array('class' => $action), $iconContent);
                $overlayLinkContent .= $linkContent;
            }
            return $overlayLinkContent;
        }

        /**
         * Return the available overlay actions for nonEditable representation
         * @return array
         */
        protected function resolveAvailableNonEditableActionsArray()
        {
            return array(static::OVERLAY_ACTION_MOVE, static::OVERLAY_ACTION_EDIT, static::OVERLAY_ACTION_DELETE);
        }

        /**
         * Resolve html options for nonEditable representation's wrapper
         * @return array
         */
        protected function resolveNonEditableWrapperHtmlOptions()
        {
            return array('id' => $this->id, 'class' => 'builder-element-non-editable element-data');
        }

        /**
         * Render Editable representation's Form content.
         * @return string
         */
        protected final function renderFormContent()
        {
            $this->registerActiveFormScripts();
            $clipWidget             = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget($this->resolveActiveFormClassName(),
                                                                        $this->resolveActiveFormOptions());
            $formInputContent       = $this->renderFormInputsContent($form);
            $formEnd                = $this->renderFormActionLinks();
            $formEnd                .= $clipWidget->renderEndWidget();

            $content                = $formStart . $formInputContent. $formEnd;
            $content                = ZurmoHtml::tag('div', array('class' => 'wide form'), $content);
            $content                = ZurmoHtml::tag('div', array('class' => 'wrapper'), $content);
            return $content;
        }


        /**
         * Returns string containing all form input fields properly wrapped in containers.
         * @param ZurmoActiveForm $form
         * @return string
         */
        protected function renderFormInputsContent(ZurmoActiveForm $form)
        {
            $contentTabContent      = $this->renderContentTab($form);
            $settingsTabContent     = $this->renderSettingsTab($form);
            $content                = $this->renderBeforeFormLayout($form);
            $content                .= $this->renderWrappedContentAndSettingsTab($contentTabContent, $settingsTabContent);
            $content                .= '<tr><td colspan="2">' . $this->renderHiddenFields($form) . '</td></tr>';
            $content                .= $this->renderAfterFormLayout($form);
            $content                = '<table class="form-fields"><colgroup><col class="col-0"><col class="col-1"></colgroup>' . $content;
            $content                .= '</table>';
            $content                = ZurmoHtml::tag('div', array('class' => 'panel'), $content);
            $content                = ZurmoHtml::tag('div', array('class' => 'left-column full-width'), $content);
            $content                = ZurmoHtml::tag('div', array('class' => 'attributesContainer'), $content);
            return $content;
        }

        /**
         * Rendering and return content for Content tab.
         * @param ZurmoActiveForm $form
         * @return string
         */
        protected function renderContentTab(ZurmoActiveForm $form)
        {
            $content    = $this->renderContentElement($form);
            return $content;
        }

        /**
         * Resolve form title.
         */
        protected function resolveFormTitle()
        {
            return static::resolveLabel();
        }

        /**
         * Resolve form title with some formatting.
         * @return string
         */
        protected function resolveFormatterFormTitle()
        {
            $formTitle                  = ZurmoHtml::tag('h2', array(), $this->resolveFormTitle());
            $formTitle                  = ZurmoHtml::tag('center', array(), $formTitle);
            return $formTitle;
        }

        /**
         * Resolve Class name for Active Form
         * @return string
         */
        protected function resolveActiveFormClassName()
        {
            return 'ZurmoActiveForm';
        }

        /**
         * Resolve Active form options array
         * @return array
         */
        protected final function resolveActiveFormOptions()
        {
            $options = array('id'                       => $this->resolveFormId(),
                            'action'                    => $this->resolveFormActionUrl(),
                            'enableAjaxValidation'      => $this->resolveEnableAjaxValidation(),
                            'clientOptions'             => $this->resolveFormClientOptions(),
                            'htmlOptions'               => $this->resolveFormHtmlOptions());
            $customActiveFormOptions    = $this->resolveActiveFormCustomOptions();
            $options    = CMap::mergeArray($options, $customActiveFormOptions);
            return $options;
        }

        /**
         * Resolve form id
         * @return string
         */
        protected function resolveFormId()
        {
            $formId = $this->id . '-edit-form';
            return $formId;
        }

        /**
         * Resolve form action url. This url is also used by the ajax post.
         * @return mixed
         */
        protected function resolveFormActionUrl()
        {
            return Yii::app()->createUrl('emailTemplates/default/renderElementNonEditableByPost');
        }

        /**
         * Render and return any special hidden fields.
         * @param $form
         */
        protected function renderHiddenFields($form)
        {
            $idHiddenInput          = $this->renderHiddenField('id', $this->id);
            $classNameHiddenInput   = $this->renderHiddenField('className', get_class($this));
            $hiddenFields           = $idHiddenInput . $classNameHiddenInput;
            return $hiddenFields;
        }

        /**
         * Render and return a hiddenField.
         * @param $attributeName
         * @param $value
         * @return string
         */
        protected final function renderHiddenField($attributeName, $value)
        {
            return ZurmoHtml::hiddenField(ZurmoHtml::activeName($this->model, $attributeName),
                                                $value,
                                                array('id' => ZurmoHtml::activeId($this->model, $attributeName)));
         }

        /**
         * Wrap content and settings tab into a tab container and return output.
         * @param null $contentTab
         * @param null $settingsTab
         * @return string
         */
        protected final function renderWrappedContentAndSettingsTab($contentTab = null, $settingsTab = null)
        {
            if (empty($contentTab) && empty($settingsTab))
            {
                throw new NotSupportedException('Content for at least one tab should be provided');
            }

            $settingsTabHyperLink   = null;
            $contentTabHyperLink    = null;
            $settingsTabDiv         = null;
            $contentTabDiv          = null;
            $contentTabClass        = 'active-tab';
            $settingsTabClass       = null;
            if (!empty($contentTab))
            {
                $contentTabHyperLink    = ZurmoHtml::link($this->renderContentTabLabel(), '#tab1',
                                                            array('class' => $contentTabClass));
                $contentTabDiv          = ZurmoHtml::tag('div', array('id' => 'tab1',
                                                                        'class' => $contentTabClass .
                                                                                    ' tab element-edit-form-content-tab'),
                                                                $contentTab);
            }
            else
            {
                $contentTabClass    = null;
                $settingsTabClass   = 'active-tab';
            }

            if (!empty($settingsTab))
            {
                $settingsTabHyperLink   = ZurmoHtml::link($this->renderSettingsTabLabel(), '#tab2',
                                                            array('class' => $settingsTabClass));
                $settingsTabDiv  = ZurmoHtml::tag('div', array('id' => 'tab2',
                                                                    'class' => $settingsTabClass .
                                                                                ' tab element-edit-form-settings-tab'),
                                                                $settingsTab);
            }

            if (isset($contentTabDiv, $settingsTabDiv))
            {
                $this->registerTabbedContentScripts();
            }

            $tabContent             = ZurmoHtml::tag('div', array('class' => 'tabs-nav'),
                                                            $contentTabHyperLink . $settingsTabHyperLink);
            $content                = ZurmoHtml::tag('div', array('class' => 'edit-form-tab-content tabs-container'),
                                                            $tabContent . $contentTabDiv . $settingsTabDiv);
            return $content;
        }

        /**
         * Render Content Tab Label
         * @return string
         */
        protected function renderContentTabLabel()
        {
            return Zurmo::t('Core', 'Content');
        }

        /**
         * Render Settings Tab Label
         * @return string
         */
        protected function renderSettingsTabLabel()
        {
            return Zurmo::t('Core', 'Settings');
        }

        /**
         * Register Javascript to handle tab switches
         */
        protected function registerTabbedContentScripts()
        {
            // TODO: @Shoaibi/@Amit: Critical0: There is bug with tab switch script/css.
            $scriptName = 'element-edit-form-tab-switch-handler';
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                Yii::app()->clientScript->registerScript($scriptName, "
                        $('.tabs-nav a:not(.simple-link)').click( function(event){
                            event.preventDefault();
                            //the menu items
                            $('.active-tab', $(this).parent()).removeClass('active-tab');
                            $(this).addClass('active-tab');
                            //the sections
                            var _old = $('.tab.active-tab'); //maybe add context here for tab-container
                            _old.fadeToggle();
                            var _new = $( $(this).attr('href') );
                            _new.fadeToggle(150, 'linear', function(){
                                _old.removeClass('active-tab');
                                _new.addClass('active-tab');
                            });
                        });
                    ");
            }
        }

        /**
         * Render form action buttons.
         * @return string
         */
        protected function renderFormActionLinks()
        {
            $content    = $this->renderCancelLink();
            $content   .= $this->renderApplyLink();
            $content    = ZurmoHtml::tag('div', array('class' => 'form-toolbar'), $content);
            $content    = ZurmoHtml::tag('div', array('class' => 'view-toolbar-container clearfix dock'), $content);
            $content    = ZurmoHtml::tag('div', array('class' => 'float-bar'), $content);
            return $content;
        }

        /**
         * Render Cancel Action Link
         * @return string
         */
        protected function renderCancelLink()
        {
            $this->registerCancelScript();
            $label  = ZurmoHtml::tag('span', array('class' => 'z-label'), $this->renderCancelLinkLabel());
            $link   = ZurmoHtml::link($label, '#', $this->resolveCancelLinkHtmlOptions());
            return $link;
        }

        /**
         * Resolve Cancel Link html options
         * @return array
         */
        protected function resolveCancelLinkHtmlOptions()
        {
            return array('id' => $this->resolveCancelLinkId(), 'class' => 'cancel-button');
        }

        /**
         * Resolve link id for Cancel Link
         * @return string
         */
        protected function resolveCancelLinkId()
        {
            return 'elementEditFormCancelLink';
        }

        /**
         * Render Label for Cancel Link
         * @return string
         */
        protected function renderCancelLinkLabel()
        {
            return Zurmo::t('Core', 'Cancel');
        }

        /**
         * Render Apply Action Link
         * @return string
         */
        protected function renderApplyLink()
        {
            $this->registerAjaxPostForApplyClickScript();
            $params                = array();
            $params['label']       = $this->renderApplyLinkLabel();
            $params['htmlOptions'] = $this->resolveApplyLinkHtmlOptions();
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }

        /**
         * Resolve html options for Apply link
         * @return array
         */
        protected function resolveApplyLinkHtmlOptions()
        {
            return array('id' => $this->resolveApplyLinkId(), 'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
        }

        /**
         * Resolve link id for apply link
         * @return string
         */
        protected function resolveApplyLinkId()
        {
            return 'elementEditFormApplyLink';
        }

        /**
         * Render label for for Apply Link
         * @return string
         */
        protected function renderApplyLinkLabel()
        {
            return Zurmo::t('Core', 'Apply');
        }

        /**
         * Register any additional Javascript snippets
         */
        protected function registerActiveFormScripts()
        {

        }

        /**
         * Register javascript snippet to handle clicking apply link
         */
        protected function registerAjaxPostForApplyClickScript()
        {
            $hiddenInputId  = ZurmoHtml::activeId($this->model, 'id');
            Yii::app()->clientScript->registerScript('ajaxPostForApplyClick', "
                $('#" . $this->resolveApplyLinkId() . "').unbind('click.ajaxPostForApplyClick');
                $('#" . $this->resolveApplyLinkId() . "').bind('click.ajaxPostForApplyClick', function()
                {
                    emailTemplateEditor.freezeLayoutEditor();
                    var replaceElementId = $('#" . $hiddenInputId . "').val();
                    $.ajax({
                        type : 'POST',
                        data : $('#" .  $this->resolveApplyLinkId() . "').closest('form').serialize(),
                        success: function (html) {
                            $('#' + replaceElementId).replaceWith(html);
                            emailTemplateEditor.unfreezeLayoutEditor();
                            emailTemplateEditor.canvasChanged();
                        }
                    });
                });
            ");
        }

        /**
         * Register javascript snippet to handle clicking cancel link
         */
        protected function registerCancelScript()
        {
            Yii::app()->clientScript->registerScript('cancelLinkClick', "
                $('#" . $this->resolveCancelLinkId() . "').unbind('click.cancelLinkClick');
                $('#" . $this->resolveCancelLinkId() . "').bind('click.cancelLinkClick', function()
                {
                    $('#" . BuilderCanvasWizardView::ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID . "').hide();
                    $('#" . BuilderCanvasWizardView::ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID . "').empty();
                });
            ");
        }

        /**
         * If form should allow ajax validation or not.
         * @return bool
         */
        protected function resolveEnableAjaxValidation()
        {
            return false;
        }

        /**
         * Resolve any special client options
         * @return array
         */
        protected function resolveFormClientOptions()
        {
            return array();
        }

        /**
         * Resolve html options for form.
         * @return array
         */
        protected function resolveFormHtmlOptions()
        {
            return array('class' => 'element-edit-form');
        }

        /**
         * Resolve custom options for form
         * @return array
         */
        protected function resolveActiveFormCustomOptions()
        {
            return array();
        }

        /**
         * Render and return content that should be part of form but added before any input are rendered.
         * @param ZurmoActiveForm $form
         */
        protected function renderBeforeFormLayout(ZurmoActiveForm $form)
        {

        }

        /**
         * Render and return content that should be part of form but added before action links are rendered.
         * @param ZurmoActiveForm $form
         */
        protected function renderAfterFormLayout(ZurmoActiveForm $form)
        {

        }

        /**
         * Generate a unique id
         * @return string
         */
        protected function generateId()
        {
            return (strtolower(get_class($this)) . '_' . uniqid(time() . '_'));
        }

        /**
         * Resolve default properties
         * @return array
         */
        protected function resolveDefaultProperties()
        {
            return array();
        }

        /**
         * Resolve default parameters
         * @return array
         */
        protected function resolveDefaultParams()
        {
            return array();
        }

        /**
         * Initialize Id. Generate a new one if parameter is not set,
         * @param null $id
         */
        protected function initId($id = null)
        {
            if (!isset($id))
            {
                $id     = $this->generateId();
            }
            $this->id   = $id;
        }

        /**
         * Initialize properties. Set to default one if parameter is not set,
         * @param null $properties
         */
        protected function initProperties($properties = null)
        {
            if (!isset($properties))
            {
                $properties   = $this->resolveDefaultProperties();
            }
            $this->properties   = $properties;
        }

        /**
         * Initialize content. Set to default one if parameter is not set,
         * @param null $content
         */
        protected function initContent($content = null)
        {
            if (!isset($content))
            {
                $content        = $this->resolveDefaultContent();
            }
            $this->content      = $content;
        }

        /**
         * init element params
         * @param null $params
         */
        protected function initParams($params = null)
        {
            $defaultParams  = $this->resolveDefaultParams();
            if (!isset($params))
            {
                $params     = $defaultParams;
            }
            else if (isset($params['mergeDefault']) && $params['mergeDefault'] === true)
            {
                $params     = CMap::mergeArray($defaultParams, $params);
            }
            $this->params   = $params;
        }

        /**
         * init element model
         */
        protected function initModel()
        {
            $this->model    = $this->getModel();
        }

        /**
         * Return a model to be used on forms
         * @return BuilderElementEditableModelForm
         */
        protected function getModel()
        {
            $modelClassName = static::getModelClassName();
            return new $modelClassName($this->content, $this->properties);
        }

        /**
         * Render the content element using provided form
         * @param ZurmoActiveForm $form
         * @return string
         */
        protected final function renderContentElement(ZurmoActiveForm $form = null)
        {
            $elementClassName   = $this->resolveContentElementClassName();
            $attributeName      = $this->resolveContentElementAttributeName();
            $params             = $this->resolveContentElementParams();
            $element            = new $elementClassName($this->model, $attributeName, $form, $params);
            if (isset($form))
            {
                $this->resolveContentElementEditableTemplate($element);
            }
            else
            {
                $this->resolveContentElementNonEditableTemplate($element);
            }
            $content            = $element->render();
            return $content;
        }

        /**
         * Resolve editable template for content element.
         * @param Element $element
         */
        protected function resolveContentElementEditableTemplate(Element $element)
        {
            $element->editableTemplate = str_replace('{error}', '', $element->editableTemplate);
        }

        /**
         * Resolve non editable template for content element.
         * @param Element $element
         */
        protected function resolveContentElementNonEditableTemplate(Element $element)
        {
            // we need to put wrapper div inside td else it breaks the table layout output.
            $element->nonEditableTemplate   = '{content}';
        }

        /**
         * Resolve params to send to Content element's construct
         */
        protected function resolveContentElementParams()
        {
            $params = array();
            // we set label to an empty string as a default value.
            // we already hide label in non-editable representation of content element.
            // it is only shown in editable representation, which can also be overriden to hide it.
            // setting it to empty string here isn't to hide it.
            // it is rather to avoid Element trying to do ask ModelForm's model for a label.
            // BuilderElementEditableModelForm does not set a model so we would see an error there.
            $params['labelHtmlOptions'] = array('label' => '');
            return $params;
        }

        /**
         * Returns the default content for current element.
         * @return array
         */
        protected function resolveDefaultContent()
        {
            return array();
        }

        /**
         * Render and Return content for Settings Tab. Returning null hides settings tab from appearing.
         * @param ZurmoActiveForm $form
         * @throws NotImplementedException
         */
        protected function renderSettingsTab(ZurmoActiveForm $form)
        {
            throw new NotImplementedException('Children elements should override it, or remove all calls made to it.');
        }

        /**
         * Resolve the class name of the element to use to render content for editable and non editable representation
         * @throws NotImplementedException
         */
        protected function resolveContentElementClassName()
        {
            throw new NotImplementedException('Children elements should override it, or remove all calls made to it.');
        }

        /**
         * Resolve the attribute name to use to render editable and non-editable representation of content element
         * @throws NotImplementedException
         */
        protected function resolveContentElementAttributeName()
        {
            throw new NotImplementedException('Children elements should override it, or remove all calls made to it.');
        }

        /**
         * Getter for $id
         * @return string
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Getter for $content
         * @param bool $serialized
         * @return array|string
         */
        public function getContent($serialized = false)
        {
            if ($serialized)
            {
                return serialize($this->content);
            }
            return $this->content;
        }

        /**
         * Getter for $properties
         * @param bool $serialized
         * @return array|string
         */
        public function getProperties($serialized = false)
        {
            if ($serialized)
            {
                return serialize($this->properties);
            }
            return $this->properties;
        }

        /**
         * Getter for $renderForCanvas
         * @return bool
         */
        public function getRenderForCanvas()
        {
            return $this->renderForCanvas;
        }

        /**
         * Getter for $params
         * @return array
         */
        public function getParams()
        {
            return $this->params;
        }
    }
?>