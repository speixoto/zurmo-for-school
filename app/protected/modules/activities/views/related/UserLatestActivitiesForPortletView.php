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
     * Wrapper view for displaying a user's latest activities feed.
     */
    class UserLatestActivitiesForPortletView extends LatestActivitiesForPortletView
    {
       /**
         * Some extra assertions are made to ensure this view is used in a way that it supports.
         */
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('is_array($viewData) || $viewData == null');
            assert('isset($params["portletId"])');
            assert('isset($params["relationModuleId"])');
            assert('isset($params["relationModel"])');
            assert('is_string($uniqueLayoutId)');
            $this->moduleId       = 'users';
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        /**
         * Override to default to 'mine' instead of 'all' activities.
         * (non-PHPdoc)
         * @see LatestActivitiesForPortletView::makeLatestActivitiesConfigurationForm()
         */
        protected function makeLatestActivitiesConfigurationForm()
        {
            $form                = new LatestActivitiesConfigurationForm();
            $form->ownedByFilter = intval($this->params['relationModel']->id);
            return $form;
        }

        /**
         * Override to ensure the user id is properly set in the Id parameter.
         * (non-PHPdoc)
         * @see LatestActivitiesForPortletView::getPortletDetailsUrl()
         */
        protected function getPortletDetailsUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/defaultPortlet/details',
                                                        array_merge(GetUtil::getData(), array( 'portletId' =>
                                                                                    $this->params['portletId'],
                                                            'uniqueLayoutId' => $this->uniqueLayoutId,
                                                            'id' => $this->params['relationModel']->id)));
        }

        protected function getDataProvider($uniquePageId, $form)
        {
            assert('is_string($uniquePageId)');
            assert('$form instanceOf LatestActivitiesConfigurationForm');
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            $filteredMashableModelClassNames = LatestActivitiesUtil::resolveMashableModelClassNamesByFilteredBy(
                                                    array_keys($form->mashableModelClassNamesAndDisplayLabels),
                                                    $form->filteredByModelName);
            $modelClassNamesAndSearchAttributeData = // Not Coding Standard
                LatestActivitiesUtil::
                    getSearchAttributesDataByModelClassNamesAndRelatedItemIds($filteredMashableModelClassNames,
                                                                              array(), $form->ownedByFilter);
            $modelClassNamesAndSortAttributes =      // Not Coding Standard
                LatestActivitiesUtil::getSortAttributesByMashableModelClassNames($filteredMashableModelClassNames);
            return new RedBeanModelsDataProvider($uniquePageId, $modelClassNamesAndSortAttributes,
                                                          true, $modelClassNamesAndSearchAttributeData,
                                                          array('pagination' => array('pageSize' => $pageSize)));
        }

        public function getLatestActivitiesViewClassName()
        {
            return 'LatestActivitiesForUserListView';
        }

        protected static function includeHavingRelatedItemsWhenRenderingMashableModels()
        {
            return true;
        }

        public static function hasRollupSwitch()
        {
            return true;
        }
    }
?>