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

     */
    class EmailTemplateHtmlAndTextContentElement extends Element implements DerivedElementInterface
    {
        protected function renderControlNonEditable()
        {
            $emailMessageTemplate = new EmailTemplate();
            assert('$this->attribute == null');
            if ($emailMessageTemplate->htmlContent != null)
            {
                return Yii::app()->format->html($emailMessageTemplate->htmlContent);
            }
            elseif ($emailMessageTemplate->textContent != null)
            {
                return Yii::app()->format->text($emailMessageTemplate->textContent);
            }
        }

        protected function renderControlEditable()
        {
          $content  = '<div class="email-template-textcontent">';
          $content  .= $this->renderTextContentArea();
          $content  .= '</div>';
          $content  .= '<div class="email-template-htmlcontent">';
          $content  .= $this->renderHtmlContentArea();
          $content  .= '</div>';
          return $content;
        }

        protected function renderHtmlContentArea()
        {
            //$inputNameIdPrefix       = $this->attribute;
            $attribute               = 'htmlContent';
            $id                      = $this->getEditableInputId($attribute);
            $htmlOptions             = array();
            $htmlOptions['id']       = $id;
            $htmlOptions['name']     = $this->getEditableInputName($attribute);
            $cClipWidget             = new CClipWidget();
            $cClipWidget->beginClip("Redactor");
            $cClipWidget->widget('application.core.widgets.Redactor', array(
                                        'htmlOptions' => $htmlOptions,
                                        'content'     => $this->model->htmlContent,
            ));
            $cClipWidget->endClip();
            $content                = $cClipWidget->getController()->clips['Redactor'];
            $content                .= $this->form->error($this->model, $attribute);
            return $content;
        }

         protected function renderTextContentArea()
         {
            //$inputNameIdPrefix                        = $this->attribute; // TODO: @Shoaibi: why do we need this?
            $attribute                                  = 'textContent';
            $content                                    = null;
            $textContentElement                         = new TextAreaElement($this->model, 'textContent', $this->form);
            $textContentElement->nonEditableTemplate    = '<div class="text-content">{content}</div>';
            $content                                    .= $textContentElement->render();
            $content                                    .= $this->form->error($this->model, $attribute);
            return $content;
         }
        public static function getModelAttributeNames()
        {
            return array(
                'htmlContent',
                'textContent',
            );
        }
     }
?>
