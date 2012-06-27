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

    class ArchivedEmailMatching extends GridView
    {
        protected $cssClasses =  array('DetailsView');

        protected $controllerId;

        protected $moduleId;

        protected $emailMessage;

        protected $userCanAccessLeads;

        protected $userCanAccessContacts;

        protected $userCanCreateContact;

        protected $userCanCreateLead;

        public function __construct(
                $controllerId,
                $moduleId,
                EmailMessage $emailMessage,
                SelectVariableContactForm $selectVariableContactForm,
                CreateContactForm $createContactForm,
                CreateLeadForm $createLeadForm,
                $userCanAccessLeads,
                $userCanAccessContacts,
                $userCanCreateContact,
                $userCanCreateLead,
                $gridSize)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('$emailMessage->id > 0');
            assert('is_bool($userCanAccessLeads)');
            assert('is_bool($userCanAccessContacts)');
            assert('is_bool($userCanCreateContact)');
            assert('is_bool($userCanCreateLead)');
            assert('is_int($gridSize)');
            parent::__construct($gridSize, 1);
            $this->controllerId              = $controllerId;
            $this->moduleId                  = $moduleId;
            $this->emailMessage              = $emailMessage;
            $this->selectVariableContactForm = $selectVariableContactForm;
            $this->contact = $contact; //we need this for use with creating lead or contact.
            $this->lead    = $lead;
            $this->userCanAccessLeads        = $userCanAccessLeads; //todo: remove this, we don't need it since the selectVariableContactForm should have this info
            $this->userCanAccessContacts     = $userCanAccessContacts; //todo: remove this, we don't need it since the selectVariableContactForm should have this info
            $this->userCanCreateContact      = $userCanCreateContact;
            $this->userCanCreateLead         = $userCanCreateLead;
            $this->gridSize                  = $gridSize;
        }

        /**
         * Renders content for the view.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $this->setView(new SelectVariableContactForArchivedEmailMatchingView($this->controllerId,
                                                                                 $this->moduleId,
                                                                                 $emailMessage,
                                                                                 $this->selectVariableContactForm), 0, 0);

            ///effectively inlineContact,Lead quick creations.
            $row = 1;
            if($userCanCreateContact)
            {
                $this->setView(new xxxView($controllerId, $moduleId, $emailMessage, $this->contact), $row, 0);
                $row ++;
            }
            if($userCanCreateLead)
            {
                $this->setView(new yyyView($controllerId, $moduleId, $emailMessage, $this->lead), $row, 0);
            }
            $this->renderScriptsContent();

            $selectLink = CHtml::link(Yii::t('Default', 'Select AccountsModuleSingularLabel',
                            LabelUtil::getTranslationParamsForAllModules()), '#', array('class' => 'account-select-link'));
            $createLink = CHtml::link(Yii::t('Default', 'Create AccountsModuleSingularLabel',
                            LabelUtil::getTranslationParamsForAllModules()), '#', array('class' => 'account-create-link'));
            $createLink = CHtml::link(Yii::t('Default', 'Create AccountsModuleSingularLabel',
                            LabelUtil::getTranslationParamsForAllModules()), '#', array('class' => 'account-create-link'));


            $content  = null;
            $content .= '<div class="lead-conversion-actions">';
            $content .= '<div id="account-select-title">';
            if ($this->userCanCreateAccount)
            {
                $content .= $createLink .  '&#160;' . Yii::t('Default', 'or') . '&#160;';
            }
            $content .= Yii::t('Default', 'Select AccountsModuleSingularLabel',
                                    LabelUtil::getTranslationParamsForAllModules()) . '&#160;';

            if ($this->convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED)
            {
                $content .= Yii::t('Default', 'or') . '&#160;' . $skipLink;
            }
            $content .= '</div>';
            $content .= '<div id="account-create-title">';
            $content .= Yii::t('Default', 'Create AccountsModuleSingularLabel',
                                    LabelUtil::getTranslationParamsForAllModules()) . '&#160;';
            $content .= Yii::t('Default', 'or') . '&#160;' . $selectLink . '&#160;';
            if ($this->convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED)
            {
                $content .= Yii::t('Default', 'or') . '&#160;' . $skipLink;
            }
            $content .= '</div>';
            if ($this->convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED)
            {
                $content .= '<div id="account-skip-title">';
                if ($this->userCanCreateAccount)
                {
                    $content .= $createLink . '&#160;' . Yii::t('Default', 'or') . '&#160;';
                }
                $content .= $selectLink . '&#160;' . Yii::t('Default', 'or') . '&#160;';
                $content .= Yii::t('Default', 'Skip AccountsModuleSingularLabel',
                                        LabelUtil::getTranslationParamsForAllModules()) . '&#160;';
                $content .= '</div>';
            }
            $content .= '</div>'; //this was missing..
            return '<div class="wrapper">' . $content . parent::renderContent() . '</div>';
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        protected function renderScriptsContent()
        {
            //always start with everything hidden except top area.
            //todo:
            Yii::app()->clientScript->registerScript('leadConvert', "
                $(document).ready(function()
                    {
                        $('#account-create-title').hide();
                        $('#AccountConvertToView').hide();
                        $('#LeadConvertAccountSkipView').hide();
                        $('#account-skip-title').hide();
                    }
                );
            ");
            Yii::app()->clientScript->registerScript('leadConvertActions', "
                $('.account-select-link').click( function()
                    {
                        $('#AccountConvertToView').hide();
                        $('#LeadConvertAccountSkipView').hide();
                        $('#AccountSelectView').show();
                        $('#account-create-title').hide();
                        $('#account-skip-title').hide();
                        $('#account-select-title').show();
                        return false;
                    }
                );
                $('.account-create-link').click( function()
                    {
                        $('#AccountConvertToView').show();
                        $('#LeadConvertAccountSkipView').hide();
                        $('#AccountSelectView').hide();
                        $('#account-create-title').show();
                        $('#account-skip-title').hide();
                        $('#account-select-title').hide();
                        return false;
                    }
                );
                $('.account-skip-link').click( function()
                    {
                        $('#AccountConvertToView').hide();
                        $('#LeadConvertAccountSkipView').show();
                        $('#AccountSelectView').hide();
                        $('#account-create-title').hide();
                        $('#account-skip-title').show();
                        $('#account-select-title').hide();
                        return false;
                    }
                );
            ");

        }
    }
?>