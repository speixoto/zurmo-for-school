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

    class EmailTemplatesDefaultController extends ZurmoModuleController
    {
        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $emailTemplate                  = new EmailTemplate(false);
            $searchForm                     = new EmailTemplatesSearchForm($emailTemplate);
            $listAttributesSelector         = new ListAttributesSelector('EmailTemplatesListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'EmailTemplatesSearchView'
            );
            $actionBarAndListView = new ActionBarAndListView(
                $this->getId(),
                $this->getModule()->getId(),
                $emailTemplate,
                'EmailTemplates',
                $dataProvider,
                array(),
                'EmailTemplatesActionBarForListView'
            );
            $view = new EmailTemplatesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $actionBarAndListView));
            echo $view->render();
        }


        public function actionCreate()
        {
            $editAndDetailsView = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost(new EmailTemplate()), 'Edit');
            $view = new EmailTemplatesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $editAndDetailsView));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $template = EmailTemplate::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($template);
            $view = new EmailTemplatesPageView(ZurmoDefaultViewUtil::
                                            makeStandardViewForCurrentUser($this,
                                                $this->makeEditAndDetailsView(
                                                $this->attemptToSaveModelFromPost($template, $redirectUrl), 'Edit')));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $template = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($template);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($template),
                                        'EmailTemplatesModule'), $template);
            $detailsView              = new EmailTemplateEditAndDetailsView('Details', $this->getId(),
                                                                            $this->getModule()->getId(), $template);
            $breadcrumbLinks          = array(StringUtil::getChoppedStringContent(strval($template), 25));
            $view                     = new EmailTemplatesPageView((ZurmoDefaultViewUtil::
                                                makeViewWithBreadcrumbsForCurrentUser($this, $detailsView,
                                                    $breadcrumbLinks, 'EmailTemplateBreadCrumbView')));

            echo $view->render();
        }

        protected static function getSearchFormClassName()
        {
            return 'EmailTemplatesSearchForm';
        }

        public function actionDelete($id)
        {
            $emailTemplate = EmailTemplate::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($emailTemplate);
            $emailTemplate->delete();
            $this->redirect(array($this->getId() . '/index'));
        }
    }
?>
