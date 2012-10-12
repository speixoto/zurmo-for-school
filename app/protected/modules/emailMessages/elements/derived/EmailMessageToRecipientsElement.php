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
     * Display email message to recipients.
     */
    class EmailMessageToRecipientsElement extends Element implements DerivedElementInterface
    {
        protected function renderControlNonEditable()
        {
            assert('$this->model instanceof EmailMessage');
            $toContent  = Yii::app()->format->html('To: ' . EmailMessageMashableActivityRules::
                            getRecipientsContent($this->model->recipients, EmailMessageRecipient::TYPE_TO));
            $content    = CHtml::tag('div', array(), $toContent);
            $ccContent  = Yii::app()->format->html('Cc: ' . EmailMessageMashableActivityRules::
                            getRecipientsContent($this->model->recipients, EmailMessageRecipient::TYPE_CC));
            $content   .= CHtml::tag('div', array(), $ccContent);
            $bccContent = Yii::app()->format->html('Bcc: ' . EmailMessageMashableActivityRules::
                            getRecipientsContent($this->model->recipients, EmailMessageRecipient::TYPE_BCC));
            $content   .= CHtml::tag('div', array(), $bccContent);
            return $content;
        }

        protected function renderControlEditable()
        {
            assert('$this->model instanceof EmailMessage');
            $toContent  = $this->renderTokenInput('to');
            $toContent .= ZurmoHtml::link('Cc/Bcc', '#', array('onclick' => "js:$('#cc-bcc-fields').toggle();"));
            $ccContent  = $this->renderTokenInput('cc');
            $bccContent = $this->renderTokenInput('bcc');
            return $toContent . CHtml::tag('div',
                                           array('id' => 'cc-bcc-fields',
                                                 'style'   => 'display: none;'
                                               ),
                                           $ccContent . $bccContent);
        }

        protected function renderTokenInput($prefix)
        {
            $inputId   = $this->getEditableInputId($this->attribute, $prefix);
            $inputName = $this->getEditableInputName($this->attribute, $prefix);
            $content   = $this->form->labelEx($this->model,
                                            $this->attribute,
                                            array('for' => $inputId,
                                                  'label' => ucfirst($prefix)));

            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModelElement");
            $cClipWidget->widget('application.core.widgets.MultiSelectAutoComplete', array(
                'name'        => $inputName,
                'id'          => $inputId,
                'jsonEncodedIdsAndLabels'   => CJSON::encode($this->getExistingPeopleRelationsIdsAndLabels($prefix)),
                'sourceUrl'   => Yii::app()->createUrl('emailMessages/default/autoCompleteForMultiSelectAutoComplete'),
                'htmlOptions' => array(
                    'disabled' => $this->getDisabledValue(),
                    ),
                'hintText' => Yii::t('Default', 'Type name or email'),
                'onAdd'    => $this->getOnAddContent(),
                'onDelete' => $this->getOnDeleteContent(),
            ));
            $cClipWidget->endClip();
            $content  .= $cClipWidget->getController()->clips['ModelElement'];
            return $content;
        }

        protected function renderLabel()
        {
            if ($this->form === null)
            {
                return $this->getDisplayName();
            }
            else
            {
                return $this->form->labelEx($this->model,
                                            $this->attribute,
                                            array('for' => $this->getEditableInputId(),
                                                  'label' => $this->getDisplayName()));
            }
        }

        public static function getDisplayName()
        {
            return Yii::t('Default', 'Recipients');
        }

        public static function getModelAttributeNames()
        {
            return array();
        }

        protected function getOnAddContent()
        {
        }

        protected function getOnDeleteContent()
        {
        }

        protected function getExistingPeopleRelationsIdsAndLabels($prefix)
        {
            $existingPeople = array();
            foreach ($this->model->recipients as $recipient)
            {
                if($recipient->type == constant('EmailMessageRecipient::TYPE_' . strtoupper($prefix)))
                {
                    $existingPeople[] = array('id'   => $recipient->toAddress,
                                              'name' => $recipient->toName . ' (' . $recipient->toAddress . ')');
                }
            }
            return $existingPeople;
        }
    }
?>