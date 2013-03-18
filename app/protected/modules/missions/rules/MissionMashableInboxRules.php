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

    class MissionMashableInboxRules extends MashableInboxRules
    {
        public $shouldRenderCreateAction = true;

        private function getMetadataForUnreadForCurrentUser()
        {
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'ownerHasReadLatest',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1
                ),
                2 => array(
                    'attributeName'        => 'owner',
                    'operatorType'         => 'equals',
                    'value'                => Yii::app()->user->userModel->id
                ),
                3 => array(
                    'attributeName'        => 'takenByUser',
                    'operatorType'         => 'equals',
                    'value'                => Yii::app()->user->userModel->id,
                ),
                4 => array(
                    'attributeName'        => 'takenByUserHasReadLatest',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1
                ),
            );
            $searchAttributeData['structure'] = '((1 and 2) or (3 and 4))';
            return $searchAttributeData;
        }

        public function getMetadataForMashableInbox()
        {
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'owner',
                    'operatorType'         => 'equals',
                    'value'                => Yii::app()->user->userModel->id
                ),
                2 => array(
                    'attributeName'        => 'takenByUser',
                    'operatorType'         => 'equals',
                    'value'                => Yii::app()->user->userModel->id,
                ),
                3 => array(
                    'attributeName'        => 'takenByUser',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                ),
            );
            $searchAttributeData['structure'] = '1 or 2 or 3';
            return $searchAttributeData;
        }

        public function getUnreadCountForCurrentUser()
        {
            $searchAttributeData = $this->getMetadataForUnreadForCurrentUser();
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Mission');
            $where  = RedBeanModelDataProvider::makeWhere('Mission', $searchAttributeData, $joinTablesAdapter);
            return Mission::getCount($joinTablesAdapter, $where, null, true);
        }

        public function getModelClassName()
        {
            return 'Mission';
        }

        public function getMachableInboxOrderByAttributeName()
        {
            return 'latestDateTime';
        }

        public function getActionViewOptions()
        {
            return array(
                array('label' => Zurmo::t('MissionsModule', 'Created'),
                      'type'  => MissionsListConfigurationForm::LIST_TYPE_CREATED),
                array('label' => Zurmo::t('MissionsModule', 'Available'),
                      'type'  => MissionsListConfigurationForm::LIST_TYPE_AVAILABLE),
                array('label' => Zurmo::t('MissionsModule', 'My Missions'),
                      'type'  => MissionsListConfigurationForm::LIST_TYPE_MINE_TAKEN_BUT_NOT_ACCEPTED),
            );
        }

        public function getMetadataFilteredByOption($option)
        {
            if ($option == null)
            {
                $option = MissionsListConfigurationForm::LIST_TYPE_AVAILABLE;
            }
            $mission          = new Mission(false);
            $metadataAdapter  = new MissionsSearchDataProviderMetadataAdapter(
                $mission,
                Yii::app()->user->userModel->id,
                array(),
                $option
            );
            return $metadataAdapter->getAdaptedMetadata();
        }

        public function getMetadataFilteredByFilteredBy($filteredBy)
        {
            if ($filteredBy == MashableInboxForm::FILTERED_BY_UNREAD)
            {
                $metadata = $this->getMetadataForUnreadForCurrentUser();
            }
            else
            {
                $metadata = null;
            }
            return $metadata;
        }

        public function getSearchAttributeData($searchTerm = null)
        {
            $metadata['clauses'][1] = array(
                            'attributeName'        => 'description',
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
            MissionsUtil::markUserHasReadLatest($model, Yii::app()->user->userModel, true);
        }

        public function resolveMarkUnread($modelId)
        {
            assert('$modelId > 0');
            $modelClassName = $this->getModelClassName();
            $model          = $modelClassName::getById($modelId);
            MissionsUtil::markUserHasReadLatest($model, Yii::app()->user->userModel, false);
        }

        public function hasUserReadLatest($modelId)
        {
            assert('$modelId > 0');
            $modelClassName = $this->getModelClassName();
            $model          = $modelClassName::getById($modelId);
            return MissionsUtil::hasUserReadMissionLatest($model, Yii::app()->user->userModel);
        }
    }
?>