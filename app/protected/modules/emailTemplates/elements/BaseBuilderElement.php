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
        const OVERLAY_ACTION_MOVE   = 'action-move';

        const OVERLY_ACTION_EDIT    = 'action-edit';

        const OVERLY_ACTION_DELETE  = 'action-delete';

        protected $id;

        protected $properties;

        protected $content;

        protected $renderForCanvas = false;

        abstract protected function resolveDefaultContent();

        abstract protected function renderControlNonEditable();

        public static function isUIAccessible()
        {
            return false;
        }

        public static function resolveDroppableWidget($widgetWrapper = 'li')
        {
            $label          = static::resolveLabel();
            $label          = ZurmoHtml::tag('span', array(), $label);
            $thumbnail      = ZurmoHtml::image(static::resolveThumbnailUrl(),
                                                get_called_class(),
                                                static::resolveThumbnailHtmlOptions());
            $widget         = $thumbnail . $label;
            $widget         = ZurmoHtml::tag('div', array('class' => 'clearfix'), $widget);;
            $widget         = ZurmoHtml::tag($widgetWrapper, static::resolveWidgetHtmlOptions(), $widget);
            return $widget;
        }

        protected static function resolveLabel()
        {
            throw new NotImplementedException('Children element should specify their own label');
        }

        protected static function resolveThumbnailBaseUrl()
        {
            return Yii::app()->themeManager->baseUrl . '/default/email-templates/elements/';
        }

        protected static function resolveThumbnailName()
        {
            return strtolower(get_called_class()) . '.png';
        }

        protected static function resolveThumbnailUrl()
        {
            return static::resolveThumbnailBaseUrl() . static::resolveThumbnailName();
        }

        protected static function resolveThumbnailHtmlOptions()
        {
            return array('class' => 'builder-element-droppable-thumbnail');
        }

        protected static function resolveWidgetHtmlOptions()
        {
            return  array('id' => get_called_class(), 'class' => 'builder-element builder-element-droppable');
        }

        public function __construct($renderForCanvas = false, $id = null, $properties = null, $content = null)
        {
            $this->renderForCanvas  = $renderForCanvas;
            $this->initId($id);
            $this->initproperties($properties);
            $this->initContent($content);
        }

        public function renderNonEditable()
        {
            $elementContent = $this->renderWrappedControlNonEditableContent();
            $wrappedContent = $this->renderControlWrapperNonEditable($elementContent);
            return $wrappedContent;
        }

        protected function renderWrappedControlNonEditableContent()
        {
            $elementContent = $this->renderControlNonEditable();
            $content        = ZurmoHtml::tag('div', $this->resolveControlNonEditableContentHtmlOptions(), $elementContent);
            return $content;
        }

        protected function resolveControlNonEditableContentHtmlOptions()
        {
            return array('class' => 'builder-element-content');
        }

        protected function renderControlWrapperNonEditable($elementContent = '{{dummyContent}}')
        {
            $customDataAttributes   = $this->resolveCustomDataAttributesNonEditable();
            $properties             = $this->resolvePropertiesNonEditable();
            $actionsOverlay         = $this->resolveNonEditableActions();
            $content                = $this->resolveWrapperNonEditable($elementContent, $properties, $customDataAttributes, $actionsOverlay);
            return $content;
        }

        protected function resolveWrapperNonEditable($elementContent, $properties, $customDataAttributes, $actionsOverlay)
        {
            $content        = '<table id="' . $this->id . '" ';
            $content        .= $properties;
            $content        .= $customDataAttributes;
            $content        .= '>';
            $content        .= '<tr><td>' . $elementContent;
            if (!empty($actionsOverlay))
            {
                $content    .= $actionsOverlay;
            }
            $content        .= '</td></tr></table>';
            return $content;
        }

        protected function resolvePropertiesNonEditable()
        {
            $mergedProperties   = CMap::mergeArray($this->resolveNonEditableWrapperHtmlOptions(), $this->properties);
            $styleProperties    = $this->resolveStylePropertiesNonEditable($mergedProperties);
            $nonStyleProperties = $this->resolveNonStylePropertiesNonEditable($mergedProperties);
            $properties         = $styleProperties . ' ' . $nonStyleProperties;
            return $properties;
        }

        protected function resolveStylePropertiesNonEditable(array & $mergedProperties)
        {
            if (isset($mergedProperties['style']))
            {
                $style  = $mergedProperties['style'];
                unset($mergedProperties['style']);

                $styleStringified       = $this->stringifyProperties($style, null, null, ':', ';');
                $styleStringified       = " style='${styleStringified}' ";
                return $styleStringified;
            }
        }

        protected function resolveNonStylePropertiesNonEditable(array $mergedProperties)
        {
            $nonStyleProperties = ' ';
            $nonStyleProperties .= $this->stringifyProperties($mergedProperties, null, '=', "'", "' ");
            return $nonStyleProperties;
        }

        protected function stringifyProperties(array $properties, $keyPrefix = null, $keySuffix = null,
                                                    $valuePrefix = null, $valueSuffix = null)
        {
            $content    = $this->stringifyArray($properties, $keyPrefix, $keySuffix, $valuePrefix, $valueSuffix);
            return $content;
        }

        protected function stringifyArray(array $array, $keyPrefix = null, $keySuffix = null,
                                          $valuePrefix = null, $valueSuffix = null)
        {
            $content    = null;
            foreach ($array as $key => $value)
            {
                $content .= $keyPrefix . $key . $keySuffix . $valuePrefix . $value . $valueSuffix;
            }
            return $content;
        }

        protected function resolveCustomDataAttributesNonEditable()
        {
            if (!$this->renderForCanvas)
            {
                return null;
            }
            $cda    = " data-class='" . get_class($this) . "'";
            $cda    .= " data-properties='" . serialize($this->properties) . "'";
            $cda    .= " data-content='" . serialize($this->content) . "' ";
            return $cda;
        }

        protected function resolveNonEditableActions()
        {
            if (!$this->renderForCanvas)
            {
                return null;
            }
            $overlayLinksContent    = $this->resolveAvailableNonEditableActionLinkContent();
            $overlayContent         = ZurmoHtml::tag('div', $this->resolveNonEditableActionsHtmlOptions(), $overlayLinksContent);
            return $overlayContent;

        }

        protected function resolveNonEditableActionsHtmlOptions()
        {
            return array('class' => 'builder-element-toolbar',
                            'id' => 'element-actions-' . $this->id);
        }

        protected function resolveAvailableNonEditableActionLinkContent()
        {
            $availableActions   = $this->resolveAvailableNonEditableActionsArray();
            $overlayLinkContent = null;
            foreach ($availableActions as $action)
            {
                $linkContent        = ZurmoHtml::tag('i', array('class' => $action), '');
                $linkContent        = ZurmoHtml::link($linkContent, '#', array('class' => "${action}-link"));
                $overlayLinkContent .= $linkContent;
            }
            return $overlayLinkContent;
        }

        protected function resolveAvailableNonEditableActionsArray()
        {
            return array(static::OVERLAY_ACTION_MOVE, static::OVERLY_ACTION_EDIT, static::OVERLY_ACTION_DELETE);
        }

        protected function resolveNonEditableWrapperHtmlOptions()
        {
            return array('class' => 'builder-element-non-editable element-data');
        }


        public function renderEditable()
        {

        }

        protected function generateId()
        {
            return (strtolower(get_class($this)) . '_' . uniqid(time() . '_'));
        }

        protected function resolveDefaultProperties()
        {
            return array();
        }

        protected function initId($id = null)
        {
            if (!isset($id))
            {
                $id     = $this->generateId();
            }
            $this->id   = $id;
        }

        protected function initProperties($properties = null)
        {
            if (!isset($properties))
            {
                $properties   = $this->resolveDefaultProperties();
            }
            $this->properties   = $properties;
        }

        protected function initContent($content = null)
        {
            if (!isset($content))
            {
                $content        = $this->resolveDefaultContent();
            }
            $this->content      = $content;
        }
    }
?>