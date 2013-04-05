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

    class MarketingListMember extends OwnedModel
    {
        public static function getModuleClassName()
        {
            return 'MarketingListsModule';
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel()
        {
            // TODO: @Shoaibi/@Jason: High: Why "Marketing Lists"?
            return 'Marketing List Members';
        }

        protected static function getLabel()
        {
            // TODO: @Shoaibi/@Jason: High: Same as above.
            return 'Marketing List Member';
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'createdDateTime',
                    'modifiedDateTime',
                    'unsubscribed',
                ),
                'relations' => array(
                    'contact'       => array(RedBeanModel::HAS_ONE,                 'Contact'),
                    'marketingList' => array(RedBeanModel::HAS_ONE,                 'MarketingList'),
                ),
                'rules' => array(
                    array('createdDateTime',       'required'),
                    array('createdDateTime',       'type', 'type' => 'datetime'),
                    array('modifiedDateTime',      'type', 'type' => 'datetime'),
                    array('unsubscribed',          'boolean'),
                    array('unsubscribed',          'default', 'value' => false),
                ),
                'elements' => array(
                    'createdDateTime'  => 'DateTime',
                    'modifiedDateTime' => 'DateTime',
                    'unsubscribed'     => 'CheckBox',
            ));
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function addNewMember($marketingListId, $contactId, $unsubscribed = false)
        {
            $member                     = new self;
            $member->contact            = Contact::getById($contactId);
            $member->marketingList      = MarketingList::getById($marketingListId);
            $member->unsubscribed       = $unsubscribed;
            if (!$member->unrestrictedSave())
            {
                throw new FailedToSaveModelException();
            }
            else
            {
                return true;
            }
        }

        public static function memberAlreadyExists($marketingListId, $contactId)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'marketingList',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => $marketingListId,
                ),
                2 => array(
                    'attributeName'             => 'contact',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => $contactId
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where             = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getCount($joinTablesAdapter, $where, get_called_class(), true);
        }

        public static function getCountByMarketingListIdAndUnsubscribed($marketingListId, $unsubscribed)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'unsubscribed',
                    'operatorType'              => 'equals',
                    'value'                     => intval($unsubscribed)
                ),
                2 => array(
                    'attributeName'             => 'marketingList',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => $marketingListId,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where             = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getCount($joinTablesAdapter, $where, get_called_class(), true);
        }

        public function onCreated()
        {
            $this->unrestrictedSet('createdDateTime',  DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
            $this->unrestrictedSet('modifiedDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
        }
    }
?>
