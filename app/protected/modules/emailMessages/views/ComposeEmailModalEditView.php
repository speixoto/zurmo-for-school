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

    class ComposeEmailModalEditView extends EditView
    {
        protected function renderTitleContent()
        {
            return null;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'SaveButton', 'label' => Yii::t('Default', 'Send')),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'EmailMessageToRecipients',
                        'EmailMessageCcRecipients',
                        'Files',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'sentDateTime',
                        'sender'
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'recipients', 'type' => 'EmailMessageToRecipients'),
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
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'content', 'type' => 'EmailMessageContent'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'Files'),
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

        protected function resolveActiveFormAjaxValidationOptions()
        {
            $afterValidateAjax = $this->renderConfigSaveAjax(
                static::getFormId(),
                $this->moduleId,
                $this->controllerId,
                'composeEmail');
            return array(
                'enableAjaxValidation' => true,
                'clientOptions' => array(
                    'beforeValidate'    => 'js:beforeValidateAction',
                    'afterValidate'     => 'js:afterValidateAjaxAction',
                    'validateOnSubmit'  => true,
                    'validateOnChange'  => false,
                    'inputContainer'    => 'td',
                    'afterValidateAjax' => $afterValidateAjax,
                )
            );
        }

        public static function getNotificationBarId()
        {
            return 'FlashMessageBar';
        }

        protected function renderConfigSaveAjax($formName, $moduleId, $controllerId, $actionSave)
        {
            return ZurmoHtml::ajax(array(
                    'type' => 'POST',
                    'data' => 'js:$("#' . $formName . '").serialize()',
                    'url'  => Yii::app()->createUrl($moduleId . '/' . $controllerId . '/' . $actionSave, GetUtil::getData()),
                    'complete' => "function(XMLHttpRequest, textStatus){\$('#modalContainer').dialog('close');
                        //find if there is a latest activities portlet
                        $('.LatestActivtiesForPortletView').each(function(){
                            $(this).find('.pager').find('.refresh').find('a').click();
                        });}"
                ));
        }
    }
?>