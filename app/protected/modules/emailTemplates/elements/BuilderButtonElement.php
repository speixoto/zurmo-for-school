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

    class BuilderButtonElement extends BaseBuilderTableWrappedElement
    {
        public static function isUIAccessible()
        {
            return true;
        }

        protected static function resolveLabel()
        {
            return Zurmo::t('EmailTemplatesModule', 'Button');
        }

        protected function resolveDefaultProperties()
        {
            $properties              = array(
                'backend'       => array(
                    'sizeClass'         => 'button',
                    'text'              => Zurmo::t('EmailTemplatesModule', 'Click Here'),
                    'width'             => '100%',
                ),
                'frontend'      => array(
                    'href'              => Yii::app()->createAbsoluteUrl('/'),
                    'target'            => '_blank',
                )
            );
            return $properties;
        }

        protected function renderControlContentNonEditable()
        {
            $target                 = null;
            $href                   = null;
            extract($this->properties['frontend']);
            $text                   = $this->properties['backend']['text'];
            $label                  = ZurmoHtml::tag('strong', array(), $text);
            $frontendOptions        = $this->resolveFrontendPropertiesNonEditable();
            $htmlOptions            = $this->resolveDefaultHtmlOptionsForLink();
            $options                = CMap::mergeArray($htmlOptions, $frontendOptions);
            $content                = ZurmoHtml::link($label, $href, $options);
            return $content;
        }

        protected function renderSettingsTab(ZurmoActiveForm $form)
        {
            $propertiesForm      = BuilderButtonElementPropertiesEditableElementsUtil::render($this->model, $form);
            $propertiesForm     .= BuilderElementBackgroundPropertiesEditableElementsUtil::render($this->model, $form);
            $propertiesForm     .= BuilderElementTextPropertiesEditableElementsUtil::render($this->model, $form, array('line-height', 'text-align'));
            $propertiesForm     .= BuilderElementBorderPropertiesEditableElementsUtil::render($this->model, $form);
            return $propertiesForm;
        }

        protected function renderContentTab(ZurmoActiveForm $form)
        {
            return null;
        }

        protected function resolveDefaultHtmlOptionsForLink()
        {
            return array('class' => 'newsletter-button');
        }

        protected function resolveNonEditableWrapperHtmlOptions()
        {
            $htmlOptions            = parent::resolveNonEditableWrapperHtmlOptions();
            $htmlOptions['class']   .= ' ' . $this->properties['backend']['sizeClass'];
            return $htmlOptions;
        }

        protected function resolveNonEditableContentWrappingTdOptions()
        {
            $htmlOptions            = $this->resolveNonEditableContentWrappingTdHtmlOptions();
            $htmlOptions['align']   = ArrayUtil::getArrayValue($this->properties['backend'], 'align');
            $width                  = ArrayUtil::getNestedValue($this->properties, "backend['width']");
            if ($width)
            {
                $htmlOptions['width']   = $width;
            }
            return $htmlOptions;
        }

        protected function resolveNonEditableContentWrappingTdHtmlOptions()
        {
            return array('class' => 'button-container');
        }
    }
?>