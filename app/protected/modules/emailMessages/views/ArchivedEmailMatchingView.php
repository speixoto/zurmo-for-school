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

    class ArchivedEmailMatchingView extends GridView
    {
        protected $cssClasses =  array('DetailsView');

        protected $controllerId;

        protected $moduleId;

        protected $emailMessage;

        protected $selectForm;

        protected $contact;

        protected $userCanAccessLeads;

        protected $userCanAccessContacts;

        protected $userCanCreateContact;

        protected $userCanCreateLead;

        protected $uniqueId;

        protected $saveActionId;

        protected $urlParameters;

        public function __construct(
                $controllerId,
                $moduleId,
                EmailMessage $emailMessage,
                Contact      $contact,
                $selectForm,
                $userCanAccessLeads,
                $userCanAccessContacts,
                $userCanCreateContact,
                $userCanCreateLead,
                $gridSize)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('$emailMessage->id > 0');
            assert('$selectForm instanceof AnyContactSelectForm || $selectForm instanceof ContactSelectForm || $selectForm instanceof LeadSelectForm');
            assert('is_bool($userCanAccessLeads)');
            assert('is_bool($userCanAccessContacts)');
            assert('is_bool($userCanCreateContact)');
            assert('is_bool($userCanCreateLead)');
            assert('is_int($gridSize)');
            parent::__construct($gridSize, 1);
            $this->controllerId              = $controllerId;
            $this->moduleId                  = $moduleId;
            $this->emailMessage              = $emailMessage;
            $this->selectForm                = $selectForm;
            $this->contact                   = $contact;
            $this->userCanAccessLeads        = $userCanAccessLeads;
            $this->userCanAccessContacts     = $userCanAccessContacts;
            $this->userCanCreateContact      = $userCanCreateContact;
            $this->userCanCreateLead         = $userCanCreateLead;
            $this->gridSize                  = $gridSize;
            $this->uniqueId                  = $emailMessage->id;
            $this->saveActionId              = 'completeMatch';
            $this->urlParameters             = array('id' => $this->emailMessage->id);
        }

        /**
         * Renders content for the view.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $this->setView(new AnyContactSelectForEmailMatchingView($this->controllerId,
                                                                    $this->moduleId,
                                                                    $this->selectForm,
                                                                    $this->uniqueId,
                                                                    $this->saveActionId,
                                                                    $this->urlParameters), 0, 0);
            $row = 1;
            if($this->userCanCreateContact)
            {
                $this->setView(new ContactInlineCreateForArchivedEmailCreateView(
                                        $this->controllerId,
                                        $this->moduleId,
                                        $this->emailMessage->id,
                                        $this->contact,
                                        $this->uniqueId,
                                        $this->saveActionId,
                                        $this->urlParameters), $row, 0);
                $row ++;
            }
            if($this->userCanCreateLead)
            {
                $this->setView(new LeadInlineCreateForArchivedEmailCreateView(
                                        $this->controllerId,
                                        $this->moduleId,
                                        $this->emailMessage->id,
                                        $this->contact,
                                        $this->uniqueId,
                                        $this->saveActionId,
                                        $this->urlParameters), $row, 0);
            }
            $selectLink            = $this->renderSelectLinkContent();
            $selectContent         = $this->renderSelectContent();
            $createContactLink     = CHtml::link(Yii::t('Default', 'Create ContactsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules()), '#',
                                     array('class' => 'contact-create-link'));
            $createContactContent  = Yii::t('Default', 'Create ContactsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules());
            $createLeadLink        = CHtml::link(Yii::t('Default', 'Create LeadsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules()), '#',
                                     array('class' => 'lead-create-link'));
            $createLeadContent     = Yii::t('Default', 'Create LeadsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules());

            $content  = '<div class="lead-conversion-actions">';
            $content .= $this->renderContactSelectTitleDivContent($selectContent, $createLeadLink,    $createContactLink);
            $content .= $this->renderLeadCreateTitleDivContent($selectLink,       $createLeadContent, $createContactLink);
            $content .= $this->renderContactCreateTitleDivContent($selectLink,    $createLeadLink,    $createContactContent);
            $content .= '</div>';
            return '<div class="wrapper">' . $content . parent::renderContent() . '</div>';
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        protected function renderScriptsContent()
        {
            //todo: do stying inline to hide stuff by defualt since this will always be the same way
            //todo: make this script to handle more than one row at a time. by using parent/child. then we don't need
            //ids maybe on the title links... we can remove them.
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

        protected function renderSelectLinkContent()
        {
            if($this->userCanAccessContacts && $this->userCanAccessLeads)
            {
                return CHtml::link(Yii::t('Default', 'Select ContactsModuleSingularLabel / LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules()), '#',
                                    array('class' => 'contact-select-link'));
            }
            if($this->userCanAccessContacts)
            {
                return CHtml::link(Yii::t('Default', 'Select ContactsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules()), '#',
                                    array('class' => 'contact-select-link'));
            }
            else
            {
                return CHtml::link(Yii::t('Default', 'Select LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules()), '#',
                                    array('class' => 'contact-select-link'));
            }
        }

        protected function renderSelectContent()
        {
            if($this->userCanAccessContacts && $this->userCanAccessLeads)
            {
                return Yii::t('Default', 'Select ContactsModuleSingularLabel / LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules());
            }
            if($this->userCanAccessContacts)
            {
                return Yii::t('Default', 'Select ContactsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules());
            }
            else
            {
                return Yii::t('Default', 'Select LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules());
            }
        }

        protected function renderContactSelectTitleDivContent($selectContent, $createLeadLink, $createContactLink)
        {
            assert('is_string($selectContent)');
            assert('is_string($createLeadLink)');
            assert('is_string($createContactLink)');
            $content  = '<div id="contact-select-title-' . $this->uniqueId . '">';
            $content .= $selectContent .  ' ' . Yii::t('Default', 'or') . ' ';
            if($this->userCanCreateContact && $this->userCanCreateLead)
            {
                $content .= $createLeadLink . ' ' . Yii::t('Default', 'or') . ' ' . $createContactLink;
            }
            elseif($this->userCanCreateContact)
            {
                $content .= $createContactLink;
            }
            else
            {
                $content .= $createLeadLink;
            }
            $content .= '</div>';
            return $content;
        }

        protected function renderLeadCreateTitleDivContent($selectContent, $createLeadContent, $createContactLink)
        {
            assert('is_string($selectContent)');
            assert('is_string($createLeadContent)');
            assert('is_string($createContactLink)');
            $content  = '<div id="lead-create-title-' . $this->uniqueId . '">';
            $content .= $selectContent . Yii::t('Default', 'or') . ' ';
            $content .= $createLeadContent;
            if($this->userCanCreateContact)
            {
                $content .= ' ' . Yii::t('Default', 'or') . ' ' . $createContactLink;
            }
            $content .= '</div>';
            return $content;
        }

        protected function renderContactCreateTitleDivContent($selectContent, $createLeadLink, $createContactContent)
        {
            assert('is_string($selectContent)');
            assert('is_string($createLeadLink)');
            assert('is_string($createContactContent)');
            $content  = '<div id="contact-create-title-' . $this->uniqueId . '">';
            $content .= $selectContent . Yii::t('Default', 'or') . ' ';
            if($this->userCanCreateLead)
            {
                $content .= ' ' . Yii::t('Default', 'or') . ' ' . $createLeadLink;
            }
            $content .= ' ' . Yii::t('Default', 'or') . ' ' . $createContactContent;
            $content .= '</div>';
            return $content;
        }
    }
?>