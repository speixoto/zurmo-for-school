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

    class ContactsDefaultController extends ZurmoModuleController
    {
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
                        ZurmoModuleController::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller'                    => $this,
                        'stateMetadataAdapterClassName' => 'ContactsStateMetadataAdapter'
                   ),
               )
            );
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $contact                        = new Contact(false);
            $searchForm                     = new ContactsSearchForm($contact);
            $listAttributesSelector         = new ListAttributesSelector('ContactsListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                'ContactsStateMetadataAdapter',
                'ContactsSearchView'
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new ContactsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView(
                    $searchForm,
                    $pageSize,
                    ContactsModule::getModuleLabelByTypeAndLanguage('Plural'),
                    $dataProvider
                );
                $view = new ContactsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $mixedView));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $contact = static::getModelAndCatchNotFoundAndDisplayError('Contact', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($contact);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($contact), 'ContactsModule'), $contact);
            $breadCrumbView          = StickySearchUtil::resolveBreadCrumbViewForDetailsControllerAction($this, 'ContactsSearchView', $contact);
            $detailsAndRelationsView = $this->makeDetailsAndRelationsView($contact, 'ContactsModule',
                                                                          'ContactDetailsAndRelationsView',
                                                                          Yii::app()->request->getRequestUri(),
                                                                          $breadCrumbView);
            $view = new ContactsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsAndRelationsView));
            echo $view->render();
        }

        public function actionCreate()
        {
            $this->actionCreateByModel(new Contact());
        }

        public function actionCreateFromRelation($relationAttributeName, $relationModelId, $relationModuleId, $redirectUrl)
        {
            $contact             = $this->resolveNewModelByRelationInformation( new Contact(),
                                                                                $relationAttributeName,
                                                                                (int)$relationModelId,
                                                                                $relationModuleId);
            $this->actionCreateByModel($contact, $redirectUrl);
        }

        protected function actionCreateByModel(Contact $contact, $redirectUrl = null)
        {
            $titleBarAndEditView = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost($contact, $redirectUrl), 'Edit');
            $view = new ContactsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $titleBarAndEditView));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $contact = Contact::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($contact);
            $view    = new ContactsPageView(ZurmoDefaultViewUtil::
                                            makeStandardViewForCurrentUser($this,
                                                $this->makeEditAndDetailsView(
                                                    $this->attemptToSaveModelFromPost($contact, $redirectUrl), 'Edit')));
            echo $view->render();
        }

        /**
         * Action for displaying a mass edit form and also action when that form is first submitted.
         * When the form is submitted, in the event that the quantity of models to update is greater
         * than the pageSize, then once the pageSize quantity has been reached, the user will be
         * redirected to the makeMassEditProgressView.
         * In the mass edit progress view, a javascript refresh will take place that will call a refresh
         * action, usually massEditProgressSave.
         * If there is no need for a progress view, then a flash message will be added and the user will
         * be redirected to the list view for the model.  A flash message will appear providing information
         * on the updated records.
         * @see Controler->makeMassEditProgressView
         * @see Controller->processMassEdit
         * @see
         */
        public function actionMassEdit()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massEditProgressPageSize');
            $contact = new Contact(false);
            $activeAttributes = $this->resolveActiveAttributesFromMassEditPost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                            new ContactsSearchForm($contact),
                            $pageSize,
                            Yii::app()->user->userModel->id,
                            'ContactsStateMetadataAdapter',
                            'ContactsSearchView');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $contact = $this->processMassEdit(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'ContactsPageView',
                $contact,
                ContactsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
            $massEditView = $this->makeMassEditView(
                $contact,
                $activeAttributes,
                $selectedRecordCount,
                ContactsModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new ContactsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $massEditView));
            echo $view->render();
        }

        /**
         * Action called in the event that the mass edit quantity is larger than the pageSize.
         * This action is called after the pageSize quantity has been updated and continues to be
         * called until the mass edit action is complete.  For example, if there are 20 records to update
         * and the pageSize is 5, then this action will be called 3 times.  The first 5 are updated when
         * the actionMassEdit is called upon the initial form submission.
         */
        public function actionMassEditProgressSave()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massEditProgressPageSize');
            $contact = new Contact(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                            new ContactsSearchForm($contact),
                            $pageSize,
                            Yii::app()->user->userModel->id,
                            'ContactsStateMetadataAdapter',
                            'ContactsSearchView');
            $this->processMassEditProgressSave(
                'Contact',
                $pageSize,
                ContactsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
        }

        /**
         * Action for displaying a mass delete form and also action when that form is first submitted.
         * When the form is submitted, in the event that the quantity of models to delete is greater
         * than the pageSize, then once the pageSize quantity has been reached, the user will be
         * redirected to the makeMassDeleteProgressView.
         * In the mass delete progress view, a javascript refresh will take place that will call a refresh
         * action, usually makeMassDeleteProgressView.
         * If there is no need for a progress view, then a flash message will be added and the user will
         * be redirected to the list view for the model.  A flash message will appear providing information
         * on the delete records.
         * @see Controler->makeMassDeleteProgressView
         * @see Controller->processMassDelete
         * @see
         */
        public function actionMassDelete()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massDeleteProgressPageSize');
            $contact = new Contact(false);

            $activeAttributes = $this->resolveActiveAttributesFromMassDeletePost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                            new ContactsSearchForm($contact),
                            $pageSize,
                            Yii::app()->user->userModel->id,
                            'ContactsStateMetadataAdapter',
                            'ContactsSearchView');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $contact = $this->processMassDelete(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'ContactsPageView',
                $contact,
                ContactsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
            $massDeleteView = $this->makeMassDeleteView(
                $contact,
                $activeAttributes,
                $selectedRecordCount,
                ContactsModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new ContactsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $massDeleteView));
            echo $view->render();
        }

        /**
         * Action called in the event that the mass delete quantity is larger than the pageSize.
         * This action is called after the pageSize quantity has been delted and continues to be
         * called until the mass delete action is complete.  For example, if there are 20 records to delete
         * and the pageSize is 5, then this action will be called 3 times.  The first 5 are updated when
         * the actionMassDelete is called upon the initial form submission.
         */
        public function actionMassDeleteProgress()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massDeleteProgressPageSize');
            $contact = new Contact(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                            new ContactsSearchForm($contact),
                            $pageSize,
                            Yii::app()->user->userModel->id,
                            'ContactsStateMetadataAdapter',
                            'ContactsSearchView');
            $this->processMassDeleteProgress(
                'Contact',
                $pageSize,
                ContactsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
        }

        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId']
            );
            echo ModalSearchListControllerUtil::setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider,
                                                'ContactsStateMetadataAdapter');
        }

        public function actionDelete($id)
        {
            $contact = Contact::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($contact);
            $contact->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        /**
         * Override to provide a contact specific label for the modal page title.
         * @see ZurmoModuleController->actionSelectFromRelatedList()
         */
        public function actionSelectFromRelatedList($portletId,
                                                    $uniqueLayoutId,
                                                    $relationAttributeName,
                                                    $relationModelId,
                                                    $relationModuleId,
                                                    $stateMetadataAdapterClassName = null)
        {
            parent::actionSelectFromRelatedList($portletId,
                                                $uniqueLayoutId,
                                                $relationAttributeName,
                                                $relationModelId,
                                                $relationModuleId,
                                                'ContactsStateMetadataAdapter');
        }

        /**
         * Override to always add contact state filter on search results.
         */
        public function actionAutoComplete($term)
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $autoCompleteResults = ContactAutoCompleteUtil::getByPartialName($term, $pageSize, 'ContactsStateMetadataAdapter');
            echo CJSON::encode($autoCompleteResults);
        }

        protected static function getSearchFormClassName()
        {
            return 'ContactsSearchForm';
        }

        public function actionExport()
        {
            $this->export('ContactsSearchView');
        }
    }
?>
