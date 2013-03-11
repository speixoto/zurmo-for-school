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
     * Helper class for adapting one of an email alert's recipients to a set of appropriate Elements
     */
    class WorkflowEmailAlertRecipientToElementAdapter
    {
        protected $emailAlertRecipientType;

        protected $model;

        protected $form;

        protected $inputPrefixData;

        public function __construct(WorkflowEmailAlertRecipientForm $model, WizardActiveForm $form,
                                    $emailAlertRecipientType, $inputPrefixData)
        {

            assert('is_string($emailAlertRecipientType)');
            assert('is_array($inputPrefixData)');
            $this->model                   = $model;
            $this->form                    = $form;
            $this->emailAlertRecipientType = $emailAlertRecipientType;
            $this->inputPrefixData         = $inputPrefixData;
        }

        /**
         * @return string
         */
        public function getContent()
        {
            $this->form->setInputPrefixData($this->inputPrefixData);
            $content = $this->getRecipientContent();
            $this->form->clearInputPrefixData();
            return $content;
        }

        /**
         * @return string
         */
        protected function getRecipientContent()
        {
            $content                             = null;
            ZurmoHtml::resolveDivWrapperForContent($this->model->getTypeLabel(),  $content, 'email-alert-recipient-label');
            $content                            .= $this->renderTypeContent();
            $content                            .= $this->renderRecipientTypeContent();
            $content                            .= $this->renderValueContent();
            return $content;
        }

        protected function renderTypeContent()
        {
            $name        = Element::resolveInputNamePrefixIntoString($this->inputPrefixData) . '[type]';
            $id          = Element::resolveInputIdPrefixIntoString($this->inputPrefixData) . 'type';
            $htmlOptions = array('id' => $id);
            return ZurmoHtml::hiddenField($name, $this->emailAlertRecipientType, $htmlOptions);
        }

        protected function renderRecipientTypeContent()
        {
            $params                 = array('inputPrefix' => $this->inputPrefixData);
            $recipientTypeElement   = new EmailMessageRecipientTypesStaticDropDownElement(
                                          $this->model, 'recipientType', $this->form, $params);
            $recipientTypeElement->editableTemplate  = '{content}{error}';
            return $recipientTypeElement->render();
        }

        protected function renderValueContent()
        {
            return 'the value todo:'; //todo:
            $params           = array('inputPrefix' => $this->inputPrefixData);
            $valueElementType = $this->model->getValueElementType();
            if($valueElementType != null)
            {
                $valueElementClassName = $valueElementType . 'Element';
                $valueElement          = new $valueElementClassName($this->model, 'value', $this->form, $params);
                if($valueElement instanceof NameIdElement)
                {
                    $valueElement->setIdAttributeId('value');
                    $valueElement->setNameAttributeName('stringifiedModelForValue');
                }
                if($valueElement instanceof MixedDropDownTypesForWorkflowActionAttributeElement)
                {
                    $valueElement->editableTemplate = '<div class="value-data">{content}{error}</div>';
                }
                elseif($valueElement instanceof MixedDateTypesForWorkflowActionAttributeElement ||
                       $valueElement instanceof MixedDateTimeTypesForWorkflowActionAttributeElement)
                {
                    $valueElement->editableTemplate = '<div class="value-data has-date-inputs">{content}{error}</div>';
                }
                else
                {
                    $startingDivStyleFirstValue     = null;
                    if ($this->model->type == WorkflowActionAttributeForm::TYPE_STATIC_NULL)
                    {
                        $startingDivStyleFirstValue         = "display:none;";
                        $valueElement->params['disabled']   = 'disabled';
                    }
                    $valueElement->editableTemplate = '<div class="value-data"><div class="first-value-area" style="' .
                        $startingDivStyleFirstValue . '">{content}{error}</div></div>';
                }
                return $valueElement->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>