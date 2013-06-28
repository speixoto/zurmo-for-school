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
     * Model for storing recipient information about an email message.  Stores specific toAddress and toName
     * in case there is no specific 'person' the email is sending to.  Also in case that 'person' changes their
     * information, the integrity of what actual email address/name was used stays intact.
     */
    class EmailMessageRecipient extends OwnedModel
    {
        const TYPE_TO  = 1;

        const TYPE_CC  = 2;

        const TYPE_BCC = 3;

        public function __toString()
        {
            if (trim($this->toAddress) == '')
            {
                return Zurmo::t('EmailMessagesModule', '(Unnamed)');
            }
            return $this->toAddress;
        }

        public static function getModuleClassName()
        {
            return 'EmailMessagesModule';
        }

        public static function canSaveMetadata()
        {
            return false;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'toAddress',
                    'toName',
                    'type',
                ),
                'relations' => array(
                    'personOrAccount'      => array(RedBeanModel::HAS_ONE, 'Item',    RedBeanModel::NOT_OWNED,
                                                    RedBeanModel::LINK_TYPE_SPECIFIC, 'personOrAccount')
                ),
                'rules' => array(
                    array('toAddress', 'required'),
                    array('toAddress', 'email'),
                    array('toName',    'type',    'type' => 'string'),
                    array('toName',    'length',  'max' => 64),
                    array('type',    'required'),
                    array('type',    'type',    'type' => 'integer'),
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email Recipient', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email Recipients', array(), null, $language);
        }

        protected static function translatedAttributeLabels($language)
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'personOrAccount' => Zurmo::t('ZurmoModule',         'Person Or AccountsModuleSingularLabel',  $params, null, $language),
                    'toAddress'       => Zurmo::t('EmailMessagesModule', 'To Address',  array(), null, $language),
                    'toName'          => Zurmo::t('EmailMessagesModule', 'To Name',  array(), null, $language),
                    'type'            => Zurmo::t('Core', 'Type',  array(), null, $language),
                )
            );
        }
    }
?>