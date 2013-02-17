<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */

class MashableInboxDefaultController extends ZurmoModuleController {

    public function actionList($modelClassName = '') {
        $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                'listPageSize', get_class($this->getModule()));
        $actionViewOptions      = array();
        $mashableInboxForm = new MashableInboxForm();
        if(Yii::app()->request->isAjaxRequest)
        {
            $getData = GetUtil::getData();
            $mashableInboxForm->attributes = $getData["MashableInboxForm"];
            if ($modelClassName != '') {
                $mashableUtilRules  = MashableUtil::createMashableInboxRulesByModel($modelClassName);
                $listView           = $mashableUtilRules->getListView($mashableInboxForm->optionForModel, $mashableInboxForm->filteredBy);
            } else {
                $modelClassNames = array_keys(MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface'));

                $modelClassNamesAndSearchAttributeData = // Not Coding Standard
                        MashableUtil::getSearchAttributesDataByModelClassNames($modelClassNames, $mashableInboxForm->filteredBy);
                $modelClassNamesAndSortAttributes = // Not Coding Standard
                        MashableUtil::getSortAttributesByMashableInboxModelClassNames($modelClassNames);
                $dataProvider = new RedBeanModelsDataProvider('MashableInbox', $modelClassNamesAndSortAttributes,
                                true, $modelClassNamesAndSearchAttributeData,
                                array('pagination' => array('pageSize' => $pageSize)));
                $listView = new MashableInboxListView($this->getId(), $this->getModule()->getId(), 'MashableInbox', $dataProvider, array());
            }
            echo $listView->render();
        }
        else
        {
            $mashableInboxForm->filteredBy = MashableInboxForm::FILTERED_BY_ALL;
            if ($modelClassName != '') {
                if ($mashableInboxForm->optionForModel == '')
                {
                    $mashableInboxForm->optionForModel = 2;
                }
                $mashableUtilRules  = MashableUtil::createMashableInboxRulesByModel($modelClassName);
                $listView           = $mashableUtilRules->getListView($mashableInboxForm->optionForModel, $mashableInboxForm->filteredBy);
                $actionViewOptions  = $mashableUtilRules->getActionViewOptions();
            } else {
                $modelClassNames = array_keys(MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface'));

                $modelClassNamesAndSearchAttributeData = // Not Coding Standard
                        MashableUtil::getSearchAttributesDataByModelClassNames($modelClassNames, $mashableInboxForm->filteredBy);
                $modelClassNamesAndSortAttributes = // Not Coding Standard
                        MashableUtil::getSortAttributesByMashableInboxModelClassNames($modelClassNames);
                $dataProvider = new RedBeanModelsDataProvider('MashableInbox', $modelClassNamesAndSortAttributes,
                                true, $modelClassNamesAndSearchAttributeData,
                                array('pagination' => array('pageSize' => $pageSize)));
                $listView = new MashableInboxListView($this->getId(), $this->getModule()->getId(), 'MashableInbox', $dataProvider, array());
            }
            $actionBarView = new MashableInboxActionBarForViews($this->getId(), $this->getModule()->getId(), 'MashableInboxListView', $actionViewOptions, $mashableInboxForm);
            $gridView = new GridView(1, 2);
            $gridView->setView($actionBarView, 0, 0);
            $gridView->setView($listView, 0, 1);
            $view = new MashableInboxPageView(ZurmoDefaultViewUtil::
                            makeStandardViewForCurrentUser($this, $gridView));
            echo $view->render();
        }
    }
}
?>