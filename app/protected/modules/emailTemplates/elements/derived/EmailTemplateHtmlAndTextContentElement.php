<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * Element used to display text and html areas on EmailTemplateEditAndDetailsView
     */
    class EmailTemplateHtmlAndTextContentElement extends Element implements DerivedElementInterface
    {
        const HTML_CONTENT_INPUT_NAME = 'htmlContent';

        const TEXT_CONTENT_INPUT_NAME = 'textContent';

        public static function getModelAttributeNames()
        {
            return array(
                static::HTML_CONTENT_INPUT_NAME,
                static::TEXT_CONTENT_INPUT_NAME,
            );
        }

        public static function renderModelAttributeLabel($name)
        {
            $labels = static::renderLabels();
            return $labels[$name];
        }

        protected static function renderLabels()
        {
            $labels = array(
                            Zurmo::t('EmailTemplatesModule', 'Html Content'),
                            Zurmo::t('EmailTemplatesModule', 'Text Content'),
                            );
            return array_combine(static::getModelAttributeNames(), $labels);
        }

        protected function renderHtmlContentAreaLabel()
        {
            return static::renderModelAttributeLabel(static::HTML_CONTENT_INPUT_NAME);
        }

        protected function resolveTabbedContent($plainTextContent, $htmlContent)
        {
            // TODO: @Shoaibi/@Amit Display both of them in separate tabs, we need a toggle here.
            $plainTextDiv = ZurmoHtml::tag('div',
                                                array('class' => 'email-template-' . static::TEXT_CONTENT_INPUT_NAME),
                                                $plainTextContent);
            $htmlContentDiv = ZurmoHtml::tag('div',
                                                array('class' => 'email-template-' . static::HTML_CONTENT_INPUT_NAME),
                                                $htmlContent);
            return ZurmoHtml::tag('div', array('class' => 'email-template-content'), $plainTextDiv.$htmlContentDiv);
        }

        protected function renderControlNonEditable()
        {
            assert('$this->attribute == null');
            return $this->resolveTabbedContent($this->model->textContent, $this->model->htmlContent);
        }

        protected function renderControlEditable()
        {
            return $this->resolveTabbedContent($this->renderTextContentArea(), $this->renderHtmlContentArea());
        }

        // REVIEW : @Shoaibi Create a HTML element out of it.
        protected function renderHtmlContentArea()
        {
            $id                      = $this->getEditableInputId(static::HTML_CONTENT_INPUT_NAME);
            $htmlOptions             = array();
            $htmlOptions['id']       = $id;
            $htmlOptions['name']     = $this->getEditableInputName(static::HTML_CONTENT_INPUT_NAME);
            $cClipWidget             = new CClipWidget();
            $cClipWidget->beginClip("Redactor");
            $cClipWidget->widget('application.core.widgets.Redactor', array(
                                        'htmlOptions' => $htmlOptions,
                                        'content'     => $this->model->htmlContent,
                                ));
            $cClipWidget->endClip();
                            // TODO: @Shoaibi/@Amit <label> either needs a line break at the end or margin-bottom.
            $content                 = ZurmoHtml::label($this->renderHtmlContentAreaLabel(), $id);
            $content                .= $cClipWidget->getController()->clips['Redactor'];
            $content                .= $this->renderHtmlContentAreaError();
            return $content;
        }

         protected function renderTextContentArea()
         {
            $textContentElement                         = new TextAreaElement($this->model, static::TEXT_CONTENT_INPUT_NAME, $this->form);
            $textContentElement->editableTemplate       = $this->editableTemplate;
            return $textContentElement->render();
         }

        protected function renderHtmlContentAreaError()
        {
            if (strpos($this->editableTemplate, '{error}') !== false)
            {
                return $this->form->error($this->model, static::HTML_CONTENT_INPUT_NAME);
            }
            else
            {
                return null;
            }
        }

        protected function renderLabel()
        {
            return null;
        }
     }
?>
