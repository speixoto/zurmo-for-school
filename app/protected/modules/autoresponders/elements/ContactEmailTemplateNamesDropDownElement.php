<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class ContactEmailTemplateNamesDropDownElement extends StaticDropDownFormElement
    {
        const DISABLE_DROPDOWN_WHEN_AJAX_IN_PROGRESS    = true;

        const DISABLE_TEXTBOX_WHEN_AJAX_IN_PROGRESS    = true;

        const NOTIFICATION_BAR_ID                      = 'FlashMessageBar';

        protected function renderControlNonEditable()
        {
            // TODO: @Shoaibi: Critical: Uncomment following and figure out a way to let details action run independently
            // throw new NotSupportedException();
        }

        protected function renderControlEditable()
        {
            $this->attribute = 'contactEmailTemplateNames';
            $dropDownArray = $this->getDropDownArray();
            $htmlOptions   = $this->getEditableHtmlOptions();
            $name          = $this->getEditableInputName();
            $this->registerScripts();
            return ZurmoHtml::dropDownList($name, null, $dropDownArray, $htmlOptions);
        }

        protected function registerScripts()
        {
            $this->registerUpdateFlashBarScript();
            $this->registerDropDownChangeHandlerScript();
        }

        protected function registerDropDownChangeHandlerScript()
        {
            $dropDownId = $this->getEditableInputId() . '_value';
            $scriptName = $dropDownId . '_changeHandler';
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                // TODO: @Shoaibi/@Amit: Critical: Loading spinner
                Yii::app()->clientScript->registerScript($scriptName, '
                        $("#' . $dropDownId . '").unbind("change.action").bind("change.action", function(event, ui)
                        {
                            selectedOption      = $(this).find(":selected");
                            selectedOptionValue    = selectedOption.val();
                            if (selectedOptionValue)
                            {
                                var dropDown            = $(this);
                                var notificationBarId   = "' . static::NOTIFICATION_BAR_ID . '";
                                var url                 = "' . $this->getEmailTemplateDetailsUrl() . '";
                                var disableDropDown     = "' . static::DISABLE_DROPDOWN_WHEN_AJAX_IN_PROGRESS . '";
                                var disableTextBox      = "' . static::DISABLE_TEXTBOX_WHEN_AJAX_IN_PROGRESS. '";
                                var textContentId       = "' . $this->getTextContentId() . '";
                                var htmlContentId       = "' . $this->getHtmlContentId() . '";
                                var textContentElement  = $("#" + textContentId);
                                var htmlContentElement  = $("#" + htmlContentId);
                                var redActorElement     = $("#" + htmlContentId).parent().find(".redactor_editor");
                                $.ajax(
                                    {
                                        url:        url,
                                        dataType:   "json",
                                        data:       {
                                                        id: selectedOptionValue,
                                                         renderJson: true
                                                    },
                                        beforeSend: function(request, settings)
                                                    {
                                                        makeSmallLoadingSpinner(true);
                                                        if (disableDropDown == true)
                                                        {
                                                            $(dropDown).attr("disabled", "disabled");
                                                        }
                                                        if (disableTextBox == true)
                                                        {
                                                            $(textContentElement).attr("disabled", "disabled");
                                                            $(htmlContentElement).attr("disabled", "disabled");
                                                            $(redActorElement).hide();
                                                        }
                                                    },
                                        success:    function(data, status, request)
                                                    {
                                                        $(textContentElement).html(data.textContent);
                                                        $(htmlContentElement).html(data.htmlContent);
                                                        $(redActorElement).html(data.htmlContent);
                                                    },
                                        error:      function(request, status, error)
                                                    {
                                                        var data = {' . // Not Coding Standard
                                                                    '   "message" : "' . Zurmo::t('AutorespondersModule',
                                                                            'There was an error processing your request') .
                                                                        '",
                                                                        "type"    : "error"
                                                                    };
                                                        updateFlashBar(data, notificationBarId);
                                                    },
                                        complete:   function(request, status)
                                                    {
                                                        $(dropDown).removeAttr("disabled");
                                                        $(dropDown).val("");
                                                        $(textContentElement).removeAttr("disabled");
                                                        $(htmlContentElement).removeAttr("disabled");
                                                        $(redActorElement).show();
                                                        event.preventDefault();
                                                        return false;
                                                    }
                                    }
                                );
                            }
                        }
                    );
                ');
            }
        }

        protected function registerUpdateFlashBarScript()
        {
            if (Yii::app()->clientScript->isScriptRegistered('handleUpdateFlashBar'))
            {
                return;
            }
            else
            {
                Yii::app()->clientScript->registerScript('handleUpdateFlashBar', '
                    function updateFlashBar(data, flashBarId)
                    {
                        $("#" + flashBarId).jnotifyAddMessage(
                        {
                            text: data.message,
                            permanent: false,
                            showIcon: true,
                            type: data.type
                        });
                    }
                ');
            }
        }

        protected function renderLabel()
        {
            return null;
        }

        protected function renderError()
        {
            return null;
        }

        protected function getDropDownArray()
        {
            return $this->getAvailableContactEmailTemplateNamesArray();
        }

        protected function getAvailableContactEmailTemplateNamesArray()
        {
            $emailTemplates         = EmailTemplate::getByType(EmailTemplate::TYPE_CONTACT);
            $emailTemplatesArray    = array();
            foreach ($emailTemplates as $emailTemplate)
            {
                $emailTemplatesArray[$emailTemplate->id] = $emailTemplate->name;
            }
            asort($emailTemplatesArray);
            return $emailTemplatesArray;
        }

        protected function getEditableHtmlOptions()
        {
            $prompt             = array('prompt' => Zurmo::t('AutorespondersModule', 'Select a template'));
            $parentHtmlOptions  = parent::getEditableHtmlOptions();
            $htmlOptions        = CMap::mergeArray($parentHtmlOptions, $prompt);
            return $htmlOptions;
        }

        protected function getEmailTemplateDetailsUrl()
        {
            return Yii::app()->createUrl('/emailTemplates/default/details');
        }

        protected function getTextContentId()
        {
            $textContentId = $this->getModuleId();
            $textContentId .= '_';
            $textContentId .= EmailTemplateHtmlAndTextContentElement::TEXT_CONTENT_INPUT_NAME;
            return $textContentId;
        }

        protected function getHtmlContentId()
        {
            $htmlContentId = $this->getModuleId();
            $htmlContentId .= '_';
            $htmlContentId .= EmailTemplateHtmlAndTextContentElement::HTML_CONTENT_INPUT_NAME;
            return $htmlContentId;
        }

        protected function getModuleId()
        {
            return 'Autoresponder';
        }
    }
?>