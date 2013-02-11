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

    class MashableInboxDefaultController extends ZurmoModuleController
    {
        public function actionList()
        {
            $pageSize     = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                                'listPageSize', get_class($this->getModule()));

            $filteredMashableModelClassNames = LatestActivitiesUtil::resolveMashableModelClassNamesByFilteredBy(
                                                    array('Mission', 'Conversation'),
                                                    LatestActivitiesConfigurationForm::FILTERED_BY_ALL);
            $modelClassNamesAndSearchAttributeData = // Not Coding Standard
                LatestActivitiesUtil::
                    getSearchAttributesDataByModelClassNamesAndRelatedItemIds($filteredMashableModelClassNames,
                                                                              array(), LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL);
            $modelClassNamesAndSortAttributes =      // Not Coding Standard
                LatestActivitiesUtil::getSortAttributesByMashableModelClassNames($filteredMashableModelClassNames);
            $dataProvider = new RedBeanModelsDataProvider('CombinedInboxes', $modelClassNamesAndSortAttributes,
                                                          true, $modelClassNamesAndSearchAttributeData,
                                                          array('pagination' => array('pageSize' => $pageSize)));
            $listView      = new MashableInboxListView($this->getId(), $this->getModule()->getId(), 'CombinedInboxes', $dataProvider, array());
            $actionBarView = new MashableInboxActionBarAndSearchForListView($this->getId(), $this->getModule()->getId());
            $gridView      = new GridView(1,2);
            $gridView->setView($actionBarView, 0, 0);
            $gridView->setView($listView, 0, 1);
            $view          = new MashableInboxPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $gridView));
            echo $view->render();
        }

    }
?>