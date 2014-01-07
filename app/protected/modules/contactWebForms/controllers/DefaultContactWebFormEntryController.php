<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Default controller for ContactWebFormEntry actions
      */
    Yii::import('application.modules.contactWebForms.controllers.DefaultController', true);
    class ContactWebFormsDefaultContactWebFormEntryController extends ZurmoBaseController
    {
        public static function getListBreadcrumbLinks()
        {
            $title = Zurmo::t('ContactWebFormsModule', 'Contact Web Form Entries');
            return array($title);
        }

        public function filters()
        {
            $modelClassName   = $this->getModule()->getPrimaryModelName();
            $viewClassName    = $modelClassName . 'EditAndDetailsView';
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + create, createFromRelation, edit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => $viewClassName,
                    ),
                    array(
                        ContactWebFormsDefaultController::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                        'activeActionElementType' => 'ContactWebFormEntriesListLink',
                        'breadCrumbLinks'         => static::getListBreadcrumbLinks(),
                    ),
                )
            );
        }

        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $activeActionElementType        = 'ContactWebFormEntriesListMenu';
            $model                          = new ContactWebFormEntry(false);
            $searchForm                     = new ContactWebFormEntrySearchForm($model);
            $dataProvider                   = $this->resolveSearchDataProvider($searchForm, $pageSize, null,
                                              'ContactWebFormEntrySearchView');
            $breadCrumbLinks                = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider,
                    'ContactWebFormEntryListView'
                );
                $view = new ContactWebFormsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForContactWebFormEntrySearchAndListView', 'ContactWebFormEntry',
                             $activeActionElementType);
                $view = new ContactWebFormsPageView(ZurmoDefaultAdminViewUtil::
                                              makeViewWithBreadcrumbsForCurrentUser(
                                              $this, $mixedView, $breadCrumbLinks, 'ContactWebFormsBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionMassDelete()
        {
            $pageSize            = Yii::app()->pagination->resolveActiveForCurrentUserByType('massDeleteProgressPageSize');
            $contactWebFormEntry = new ContactWebFormEntry(false);
            $activeAttributes    = $this->resolveActiveAttributesFromMassDeletePost();
            $dataProvider        = $this->getDataProviderByResolvingSelectAllFromGet(
                                   new ContactWebFormEntrySearchForm($contactWebFormEntry), $pageSize, null, null);
            $selectedRecordCount = static::getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $contactWebFormEntry = $this->processMassDelete($pageSize, $activeAttributes, $selectedRecordCount,
                                   'ContactWebFormsPageView', $contactWebFormEntry,
                                   ContactWebFormEntry::getModelLabelByTypeAndLanguage('Plural'), $dataProvider,
                                   array($this->getId() . '/list'));
            $massDeleteView      = $this->makeMassDeleteView($contactWebFormEntry, $activeAttributes, $selectedRecordCount,
                                   ContactWebFormEntry::getModelLabelByTypeAndLanguage('Plural'));
            $view                = new ContactWebFormsPageView(ZurmoDefaultAdminViewUtil::
                                   makeStandardViewForCurrentUser($this, $massDeleteView));
            echo $view->render();
        }

        public function actionMassDeleteProgress()
        {
            $pageSize            = Yii::app()->pagination->resolveActiveForCurrentUserByType('massDeleteProgressPageSize');
            $contactWebFormEntry = new ContactWebFormEntry(false);
            $dataProvider        = $this->getDataProviderByResolvingSelectAllFromGet(
                                   new ContactWebFormsSearchForm($contactWebFormEntry), $pageSize, null, null);
            $this->processMassDeleteProgress('ContactWebFormEntry', $pageSize,
                                              ContactWebFormEntry::getModelLabelByTypeAndLanguage('Plural'),
                                              $dataProvider);
        }

        protected function makeMassDeleteView($model, $activeAttributes, $selectedRecordCount, $title,
                                              $massDeleteViewClassName = 'MassDeleteView',
                                              $useModuleClassNameForItemLabel = true)
        {
            return parent::makeMassDeleteView($model, $activeAttributes, $selectedRecordCount, $title,
                                              $massDeleteViewClassName = 'MassDeleteView', false);
        }
    }
?>
