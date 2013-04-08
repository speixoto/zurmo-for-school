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

    class EmailTemplateEditAndDetailsView extends SecuredEditAndDetailsView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'    => 'EmailTemplateCancelLink', 'renderType' => 'Edit'),
                            array('type'    => 'SaveButton', 'renderType' => 'Edit'),
                            array('type'    => 'EditLink', 'renderType' => 'Details'),
                            array('type'    => 'EmailTemplateDeleteLink'),
                        ),
                    ),
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'modelClassName', 'type' => 'EmailTemplateModelClassName'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'subject', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * Override to handle security/access resolution on specific elements.
         */
        protected function resolveElementInformationDuringFormLayoutRender(& $elementInformation)
        {
            parent::resolveElementInformationDuringFormLayoutRender($elementInformation);
            if($elementInformation['attributeName'] == 'modelClassName' &&
               $this->model->type == EmailTemplate::TYPE_CONTACT)
            {
                $elementInformation['attributeName'] = null;
                $elementInformation['type']          = 'NoCellNull'; // Not Coding Standard
            }
        }

        protected function renderRightSideFormLayoutForEdit($form)
        {
            return null;
        }

        protected function renderAfterFormLayout($form)
        {
            Yii::app()->clientScript->registerScript(__CLASS__.'_TypeChangeHandler', "
                        $('#EmailTemplate_type_value').unbind('change.action').bind('change.action', function()
                        {
                            selectedOptionValue                 = $(this).find(':selected').val();
                            modelClassNameDropDown              = $('#EmailTemplate_modelClassName_value');
                            modelClassNameTr                    = modelClassNameDropDown.parent().parent().parent();
                            animationSpeed                      = 400;
                            if (selectedOptionValue == " . EmailTemplate::TYPE_WORKFLOW . ")
                            {
                                modelClassNameTr.show(animationSpeed);
                            }
                            else if (selectedOptionValue == " . EmailTemplate::TYPE_CONTACT . ")
                            {
                                modelClassNameTr.hide(animationSpeed, function()
                                    {
                                    modelClassNameDropDown.val('Contact');
                                    });
                            }
                            else
                            {
                            }
                        }
                        );
                    ");
            $content  = $this->resolveRenderHiddenModelClassNameElement($form);
            $content .= $this->renderHtmlAndTextContentElement($this->model, null, $form);
            return $content;
        }

        protected function resolveRenderHiddenModelClassNameElement(ZurmoActiveForm $form)
        {
            if($this->model->type == EmailTemplate::TYPE_CONTACT)
            {
                return $form->hiddenField($this->model, 'modelClassName', array());
            }
        }

        protected function getNewModelTitleLabel()
        {
            return Zurmo::t('Default', 'Create EmailTemplatesModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules());
        }

        protected function renderAfterFormLayoutForDetailsContent($form = null)
        {
            return $this->renderHtmlAndTextContentElement($this->model, null, $form) .
                        parent::renderAfterFormLayout($form);
        }

        protected function renderHtmlAndTextContentElement($model, $attribute, $form)
        {
            $element = new EmailTemplateHtmlAndTextContentElement($model, $attribute , $form);
            if (!is_null($form))
            {
                $this->resolveElementDuringFormLayoutRender($element);
            }
            else
            {
            }
            return ZurmoHtml::tag('div', array('class' => 'email-template-combined-content'), $element->render());
        }

        protected function resolveElementDuringFormLayoutRender(& $element)
        {
            if ($this->alwaysShowErrorSummary())
            {
                $element->editableTemplate = str_replace('{error}', '', $element->editableTemplate);
            }
            else
            {
            }
        }

        protected function alwaysShowErrorSummary()
        {
            return true;
        }
    }
?>
