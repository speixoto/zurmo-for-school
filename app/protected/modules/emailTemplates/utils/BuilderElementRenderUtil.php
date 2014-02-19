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
     * Class BuilderElementRenderUtil
     * Utility class to render builder elements. This should always be preferred over directly invoking
     * Builder element's construct.
     */
    class BuilderElementRenderUtil
    {
        /**
         * Render an element as editable
         * @param $className
         * @param bool $renderForCanvas
         * @param null $id
         * @param null $properties
         * @param null $content
         * @param null $params
         * @return string
         */
        public static function renderEditable($className, $renderForCanvas = false, $id = null,
                                              $properties = null, $content = null, $params = null)
        {
            $element    = static::resolveElement($className, $renderForCanvas, $id, $properties, $content, $params);
            $content    = $element->renderEditable();
            static::resolveContentForScripts($content);
            return $content;
        }

        /**
         * Render an element as noneditable
         * @param $className
         * @param bool $renderForCanvas
         * @param bool $wrapElementInRow
         * @param null $id
         * @param null $properties
         * @param null $content
         * @param null $params
         * @return string
         */
        public static function renderNonEditable($className, $renderForCanvas = false, $wrapElementInRow = false,
                                                 $id = null, $properties = null, $content = null, $params = null)
        {
            $element        = static::resolveElement($className, $renderForCanvas, $id, $properties, $content, $params);
            if (!$wrapElementInRow)
            {
                $content        = $element->renderNonEditable();
            }
            else
            {
                // we could have built the arrays ourselves but better to be bit slow and relay on the element
                // logic than hardcode anything here.
                $elementData    = static::resolveSerializedDataByElement($element);
                $columnElement  = static::resolveElement('BuilderColumnElement', $renderForCanvas, null, null, $elementData);
                $columnData     = static::resolveSerializedDataByElement($columnElement);
                $rowElement     = static::resolveElement('BuilderRowElement', $renderForCanvas, null, null, $columnData);
                $content        = $rowElement->renderNonEditable();
            }
            static::resolveContentForScripts($content);
            return $content;
        }

        /**
         * Resolve a builder element
         * @param $className
         * @param bool $renderForCanvas
         * @param null $id
         * @param null $properties
         * @param null $content
         * @param null $params
         * @return BaseBuilderElement
         */
        protected static function resolveElement($className, $renderForCanvas = false, $id = null,
                                                 $properties = null, $content = null, $params = null)
        {
            $element    = new $className($renderForCanvas, $id, $properties, $content, $params);
            return $element;
        }

        /**
         * Resolve serialized data array for an element.
         * @param BaseBuilderElement $element
         * @param bool $serializedProperties
         * @param bool $serializedContent
         * @return array
         */
        public static function resolveSerializedDataByElement(BaseBuilderElement $element, $serializedProperties = false,
                                                              $serializedContent = false)
        {
            return array($element->getId() => array(
               'class'          => get_class($element),
                'properties'    => $element->getProperties($serializedProperties),
                'content'       => $element->getContent($serializedContent),
            ));
        }

        /**
         * Resolve content for css and js
         * @param $content
         */
        protected static function resolveContentForScripts(& $content)
        {
            // this disables the footer but breaks stuff for some reason.
            //Yii::app()->clientScript->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
        }
    }
?>