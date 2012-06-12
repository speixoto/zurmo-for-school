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

    class Conversation extends OwnedSecurableItem implements MashableActivityInterface
    {
        public static function getMashableActivityRulesType()
        {
            return 'Conversation';
        }

        public static function getByName($name)
        {
            assert('is_string($name) && $name != ""');
            return self::getSubset(null, null, null, "name = '$name'");
        }

        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Yii::t('Default', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public function onCreated()
        {
            parent::onCreated();
            $this->unrestrictedSet('latestDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
        }

        public static function getModuleClassName()
        {
            return 'ConversationsModule';
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'description',
                    'latestDateTime',
                    'name',
                    'ownerHasReadLatest',
                ),
                'relations' => array(
                    'comments'                 => array(RedBeanModel::HAS_MANY,  'Comment', RedBeanModel::OWNED, 'relatedModel'),
                    'conversationItems'	 	   => array(RedBeanModel::HAS_MANY,  'Item'),
                    'conversationParticipants' => array(RedBeanModel::HAS_MANY,  'ConversationParticipant', RedBeanModel::OWNED),
                    'files'                    => array(RedBeanModel::HAS_MANY,  'FileModel', RedBeanModel::OWNED, 'relatedModel'),
                ),
                'rules' => array(
                    array('description',    	'type',    'type' => 'string'),
                    array('latestDateTime', 	'required'),
                    array('latestDateTime', 	'readOnly'),
                    array('latestDateTime', 	'type', 'type' => 'datetime'),
                    array('name',           	'required'),
                    array('name',          		'type',    'type' => 'string'),
                    array('name',           	'length',  'min'  => 3, 'max' => 64),
                    array('ownerHasReadLatest', 'boolean'),
                ),
                'elements' => array(
                    'conversationItems' => 'ConversationItem',
                    'description'       => 'TextArea',
                    'files'             => 'Files',
                    'latestDateTime'    => 'DateTime',
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                    'annualRevenue',
                    'description',
                    'latestDateTime',
                    'name',
                    'ownerHasReadLatest',
                ),
                'conversationItemsModelClassNames' => array(
                    'Account',
                    'Opportunity',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            return 'ConversationGamification';
        }
    }
?>