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

    /**
     * Acts as a helper model to retrieve model attribute and element related information
     */
    class ModelAttributeAndElementDataToMergeItem
    {
        protected $model;

        protected $attribute;

        protected $element;

        protected $primaryModel;

        public function __construct($model, $attribute, $element, $primaryModel)
        {
            $this->model     = $model;
            $this->attribute = $attribute;
            $this->element   = $element;
            $this->primaryModel = $primaryModel;
        }

        public function getAttributeRenderedContent()
        {
            return $this->decorateContent($this->model->{$this->attribute});
        }

        public function getAttributeValuesAndInputIdsForOnClick()
        {
            $interfaces = class_implements($this->element);
            //Do we really need this if we are modifying the metadata?
            if(in_array('DerivedElementInterface', $interfaces))
            {
                $elementClassName = get_class($this->element);
                if($this->element instanceof DropDownElement)
                {
                    $attributeInputIdMap[] = $this->element->getIdForSelectInput();
                }
                else
                {
                    $attributes       = $elementClassName::getModelAttributeNames();
                    foreach($attributes as $attribute)
                    {
                        $attributeInputIdMap[] = $this->getDerivedInputId($attribute);
                    }
                }
            }
            else
            {
                //return $this->getNonDerivedInputId();
                $attributeInputIdMap[] = $this->getNonDerivedInputId();
            }
            return $attributeInputIdMap;
        }

        protected function getNonDerivedInputId()
        {
            return $this->resolveInputId($this->attribute);
        }

        protected function getDerivedInputId($attribute)
        {
            return $this->resolveInputId($attribute);
        }

        private function resolveInputId($attribute)
        {
            if($this->model->$attribute instanceof CustomField)
            {
                $inputId = Element::resolveInputIdPrefixIntoString(array(get_class($this->model), $attribute, 'value'));
            }
            else
            {
                $inputId = Element::resolveInputIdPrefixIntoString(array(get_class($this->model), $attribute));
            }
            return $inputId;
        }

        protected function decorateContent($content)
        {
            if($content != null)
            {
                $inputIds = $this->getAttributeValuesAndInputIdsForOnClick();
                if($this->model->id == $this->primaryModel->id)
                {
                    $style = 'border: 2px dotted #FF0000;margin-left:4px;';
                }
                else
                {
                    $style = 'border: 2px dotted #66367b;margin-left:4px;';
                }
                $value = $this->model->{$this->attribute};
                return ZurmoHtml::link($value, '#', array('style' => $style,
                                                    'id'    => $inputIds[0] . '-' . $value,
                                                    'class' => 'attributePreElementContent'));
//                return ZurmoHtml::tag('span', array('style' => $style,
//                                                    'id'    => $this->getAttributeValuesAndInputIdsForOnClick() . '-' . $value,
//                                                    'class' => 'attributePreElementContent'), $content);
            }
            return null;
        }
    }
?>
