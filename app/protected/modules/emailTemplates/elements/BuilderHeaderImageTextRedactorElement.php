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

    class BuilderHeaderImageTextRedactorElement extends BuilderImageRedactorElement
    {
        const TEXT_NAME = 'text';

        const IMAGE_NAME = 'image';

        public function __construct($model, $attribute, $form = null, array $params = array())
        {
            assert('$attribute == null || is_string($attribute)');
            assert('is_array($params)');
            $this->model     = $model;
            $this->attribute = $attribute;
            $this->form      = $form;
            $this->params    = $params;
        }

        protected function renderControlNonEditable()
        {
            assert('$this->attribute != null');
            return $this->model->{$this->attribute}[static::IMAGE_NAME] . $this->model->{$this->attribute}[static::TEXT_NAME];
        }

        protected function renderControlEditable()
        {
            assert('$this->attribute != null');
            $cClipWidget  = new CClipWidget();
            $cClipWidget->beginClip("Redactor");
            $cClipWidget->widget('application.core.widgets.Redactor', $this->resolveRedactorOptions());
            $cClipWidget->endClip();
            $content    = Zurmo::t('EmailTemplatesModule', 'Choose your logo');
            $content   .= $cClipWidget->getController()->clips['Redactor'];
            $content   .= '</br>';
            $content   .= Zurmo::t('EmailTemplatesModule', 'Your description');
            $content   .= $this->renderTextInputContent();
            return $content;
        }

        protected function renderTextInputContent()
        {
            $name  = $this->getEditableInputName($this->attribute, static::TEXT_NAME);
            $value = $this->model->{$this->attribute}[static::TEXT_NAME];
            return ZurmoHtml::textField($name, $value, $this->resolveTextFieldHtmlOptions());
        }

        protected function getRedactorContent()
        {
            return $this->model->{$this->attribute}[static::IMAGE_NAME];
        }

        protected function resolveHtmlOptions()
        {
            $id                      = $this->getEditableInputId($this->attribute, static::IMAGE_NAME);
            $htmlOptions             = array();
            $htmlOptions['id']       = $id;
            $htmlOptions['name']     = $this->getEditableInputName($this->attribute, static::IMAGE_NAME);
            return $htmlOptions;
        }

        protected function resolveTextFieldHtmlOptions()
        {
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId($this->attribute, static::TEXT_NAME);
            return $htmlOptions;
        }
    }
?>