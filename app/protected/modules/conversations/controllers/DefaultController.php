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

    class ConversationsDefaultController extends ZurmoBaseController
    {
        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionList()
        {
            $pageSize         = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                'listPageSize', get_class($this->getModule()));
            $conversation     = new Conversation(false);
            $getData          = GetUtil::getData();
            $type             = ArrayUtil::getArrayValue($getData, 'type');
            $searchAttributes = array();
            $searchAttributes = ConversationUtil::resolveSearchAttributesByType($type);
            $metadataAdapter  = new SearchDataProviderMetadataAdapter(
                $conversation,
                Yii::app()->user->userModel->id,
                $searchAttributes
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter,
                'Conversation',
                'RedBeanModelDataProvider',
                'latestDateTime',
                true,
                $pageSize
            );

            if ($type == null)
            {
                $type = ConversationUtil::LIST_TYPE_CREATED;
            }
            if ($type == ConversationUtil::LIST_TYPE_CREATED)
            {
                $activeActionElementType = 'ConversationsCreatedLink';
            }
            elseif ($type == ConversationUtil::LIST_TYPE_PARTICIPANT)
            {
                $activeActionElementType = 'ConversationsParticipantLink';
            }
            else
            {
                throw new NotSupportedException();
            }

            $actionBarAndListView = new ActionBarAndListView(
                $this->getId(),
                $this->getModule()->getId(),
                $conversation,
                'Conversations',
                $dataProvider,
                array(),
                'ConversationsActionBarForListView',
                $activeActionElementType
            );
            $view = new ConversationsPageView(ZurmoDefaultViewUtil::
                                              makeStandardViewForCurrentUser($this, $actionBarAndListView));
            echo $view->render();
        }

        public function actionCreate()
        {
            $editAndDetailsView = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost(new Conversation()), 'Edit');
            $view = new ConversationsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $editAndDetailsView));
            echo $view->render();
        }
    }
?>
