<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * An inline edit view for a creating a contact to match an archived email to.
     *
     */
    class ContactInlineCreateForArchivedEmailCreateView extends InlineEditView
    {
        protected $uniqueId;

        public function __construct($controllerId, $moduleId, $emailMessageId, $model, $uniqueId, $saveActionId, $urlParameters)
        {
            assert('is_string($uniqueId) || is_int($uniqueId)');
            assert('$model instanceof Contact');
            parent::__construct($model, $controllerId, $moduleId, $saveActionId, $urlParameters, null);
            $this->uniqueId = $uniqueId;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'CancelLinkForEmailsMatchingList', 'htmlOptions' => array (
                                    'id'   => 'eval:"createContactCancel" . $this->uniqueId',
                                    'name' => 'eval:"createContactCancel" . $this->uniqueId',
                                    'class' => 'eval:"createContactCancel"')),
                            array('type' => 'SaveButton', 'htmlOptions' => array (
                                    'id'   => 'eval:"save-contact-" . $this->uniqueId',
                                    'name' => 'eval:"save-contact-" . $this->uniqueId')),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'TitleFullName',
                        'ContactStateDropDown',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'title',
                        'firstName',
                        'lastName',
                        'owner',
                        'state',
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'TitleFullName'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'ContactStateDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'account', 'type' => 'Account'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'officePhone', 'type' => 'Phone'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'primaryEmail', 'type' => 'EmailAddressInformation'),
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
         * Override to allow the latest activities portlet, if it exists to be refreshed.
         * (non-PHPdoc)
         * @see InlineEditView::renderConfigSaveAjax()
         */
        protected function renderConfigSaveAjax($formName)
        {
            // Begin Not Coding Standard
            return ZurmoHtml::ajax(array(
                    'type' => 'POST',
                    'data' => 'js:$("#' . $formName . '").serialize()',
                    'url'  =>  $this->getValidateAndSaveUrl(),
                    'update' => '#' . $this->uniquePageId,
                    'complete' => "function(XMLHttpRequest, textStatus){
                    $('#wrapper-" . $this->uniqueId . "').parent().parent().parent().remove();
                    $('#" . self::getNotificationBarId() . "').jnotifyAddMessage(
                                       {
                                          text: '" . Zurmo::t('ContactsModule', 'Created ContactsModuleSingularLabel successfully', LabelUtil::getTranslationParamsForAllModules()) . "',
                                          permanent: false,
                                          showIcon: true,
                                       });
                    if ($('.email-archive-item').length==0)
                    {
                        window.location.reload();
                    }
                    }",

                ));
            // End Not Coding Standard
        }

        public function renderAfterFormLayout($form)
       {
           $this->renderScriptsContent();
        }

        protected function renderScriptsContent()
        {
            return Yii::app()->clientScript->registerScript('contactInlineCreateCollapseActions', "
                        $('.createContactCancel').each(function()
                        {
                            $('.createContactCancel').live('click', function()
                            {
                                $(this).parentsUntil('.email-archive-item').find('.ContactInlineCreateForArchivedEmailCreateView').hide();
                                $(this).closest('.email-archive-item').closest('td').removeClass('active-panel')
                                .find('.z-action-link-active').removeClass('z-action-link-active');
                            });
                        });");
        }

        protected function doesLabelHaveOwnCell()
        {
            return false;
        }

        public function getFormName()
        {
            return "contact-inline-create-form-" . $this->uniqueId;
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        /**
         * By default, this is hidden.
         * (non-PHPdoc)
         * @see View::getViewStyle()
         */
        protected function getViewStyle()
        {
            return " style='display:none;'";
        }

        /**
         * Override to support prefixing with the uniqueId since this view is typicall used in a listview where there
         * are more than one form with the same inputs.
         * (non-PHPdoc)
         * @see DetailsView::resolveElementInformationDuringFormLayoutRender()
         */
        protected function resolveElementInformationDuringFormLayoutRender(& $elementInformation)
        {
            $elementInformation['inputPrefix']  = array(get_class($this->model), $this->uniqueId);
        }

        public static function getDisplayDescription()
        {
            return Zurmo::t('ContactsModule', 'Matching Archived Emails');
        }

        protected static function getNotificationBarId()
        {
            return 'FlashMessageBar';
        }
    }
?>
