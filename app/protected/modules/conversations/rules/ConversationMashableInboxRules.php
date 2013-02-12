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

    class ConversationMashableInboxRules extends MashableInboxRules
    {
        public function getUnreadCountForCurrentUser()
        {
            return ConversationsUtil::getUnreadCountTabMenuContentForCurrentUser();
        }

        public function getActionViewOptions()
        {
            return array(
                array('label' => Zurmo::t('ConversationsModule', 'Created'),
                      'type'  => ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED),
                array('label' => Zurmo::t('ConversationsModule', 'Participating In'),
                      'type'  => ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_PARTICIPANT),
                array('label' => Zurmo::t('ConversationsModule', 'Closed'),
                      'type'  => ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CLOSED),
            );
        }

        public function getActionBarAndListView($type)
        {
            $pageSize         = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                'listPageSize', get_class(Yii::app()->controller->module));
            if ($type == null)
            {
                $type = ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED;
            }
            $conversation     = new Conversation(false);
            $searchAttributes = array();
            $metadataAdapter  = new ConversationsSearchDataProviderMetadataAdapter(
                $conversation,
                Yii::app()->user->userModel->id,
                $searchAttributes,
                $type
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter->getAdaptedMetadata(),
                'Conversation',
                'RedBeanModelDataProvider',
                'latestDateTime',
                true,
                $pageSize
            );
            $listView = new ConversationsListView(
                    Yii::app()->controller->id,
                    Yii::app()->controller->module->id,
                    'Conversation',
                    $dataProvider,
                    array());
            return $listView;
        }

    }
?>