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
        public $shouldRenderCreateAction = true;

        public function getUnreadCountForCurrentUser()
        {
            return ConversationsUtil::getUnreadCountTabMenuContentForCurrentUser();
        }

        public function getModelClassName()
        {
            return 'Conversation';
        }

        public function getMachableInboxOrderByAttributeName()
        {
            return 'latestDateTime';
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

        public function getMetadataFilteredByOption($option)
        {
            if ($option == null)
            {
                $option = ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED;
            }
            $conversation     = new Conversation(false);
            $metadataAdapter  = new ConversationsSearchDataProviderMetadataAdapter(
                $conversation,
                Yii::app()->user->userModel->getClassId('Item'),
                array(),
                $option
            );
            return $metadataAdapter->getAdaptedMetadata();
        }

        public function getMetadataFilteredByFilteredBy($filteredBy)
        {
            if ($filteredBy == MashableInboxForm::FILTERED_BY_UNREAD)
            {
                $metadata['clauses'][1] = array(
                            'attributeName'        => 'ownerHasReadLatest',
                            'operatorType'         => 'doesNotEqual',
                            'value'                => (bool)1
                        );
                $metadata['clauses'][2] = array(
                        'attributeName'        => 'owner',
                        'operatorType'         => 'equals',
                        'value'                => Yii::app()->user->userModel->getClassId('Item')
                    );
                $metadata['clauses'][3] = array(
                        'attributeName'        => 'conversationParticipants',
                        'relatedAttributeName' => 'person',
                        'operatorType'         => 'equals',
                        'value'                => Yii::app()->user->userModel->getClassId('Item'),
                    );
                $metadata['clauses'][4] = array(
                        'attributeName'        => 'conversationParticipants',
                        'relatedAttributeName' => 'hasReadLatest',
                        'operatorType'         => 'doesNotEqual',
                        'value'                => (bool)1
                    );
                $metadata['structure'] = "((1 and 2) or (3 and 4))";
            }
            else
            {
                $metadata = null;
            }
            return $metadata;
        }

        public function getSearchAttributeData($searchTerm)
        {
            $metadata['clauses'][1] = array(
                            'attributeName'        => 'subject',
                            'operatorType'         => 'contains',
                            'value'                => $searchTerm
                        );
            $metadata['structure'] = "1";
            return $metadata;
        }

        public function resolveMarkRead($modelId)
        {
            assert('$modelId > 0');
            $modelClassName = $this->getModelClassName();
            $model          = $modelClassName::getById($modelId);
            ConversationsUtil::markUserHasReadLatest($model, Yii::app()->user->userModel, true);
        }

        public function resolveMarkUnread($modelId)
        {
            assert('$modelId > 0');
            $modelClassName = $this->getModelClassName();
            $model          = $modelClassName::getById($modelId);
            ConversationsUtil::markUserHasReadLatest($model, Yii::app()->user->userModel, false);
        }

        public function getMassOptions()
        {
            return array(
                          'closeSelected' => array('label' => Zurmo::t('ConversationsModule', 'Mark selected as closed'),
                                                   'isActionForAll' => false),
                    );
        }

        public function resolveCloseSelected($modelId)
        {
            assert('$modelId > 0');
            $modelClassName = $this->getModelClassName();
            $model          = $modelClassName::getById($modelId);
            if (!$model->resolveIsClosedForNull())
            {
                $model->isClosed = true;
                $saved           = $model->save();
                if (!$saved)
                {
                    throw new NotSupportedException();
                }
            }
        }

        public function hasUserReadLatest($modelId)
        {
            assert('$modelId > 0');
            $modelClassName = $this->getModelClassName();
            $model          = $modelClassName::getById($modelId);
            return ConversationsUtil::hasUserReadConversationLatest($model, Yii::app()->user->userModel);
        }
    }
?>