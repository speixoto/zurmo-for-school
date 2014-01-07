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

    class InstallDefaultController extends ZurmoModuleController
    {
        const FILTER_PATH = 'application.modules.install.controllers.filters.InstallControllerFilter';

        public function filters()
        {
            $filters   = array();
            $filters[] = array(
                InstallDefaultController::FILTER_PATH,
            );
            return $filters;
        }

        public function actionIndex()
        {
            $this->actionWelcome();
        }

        public function actionWelcome()
        {
            $welcomeView = new InstallWelcomeView($this->getId(), $this->getModule()->getId());
            $view = new InstallPageView($welcomeView);
            echo $view->render();
        }

        public function actionCheckSystem()
        {
            $serviceCheckResultsDataForDisplay = CheckServicesUtil::checkServicesAndGetResultsDataForDisplay();
            $checkServicesView = new InstallCheckServicesView($this->getId(), $this->getModule()->getId(),
                                                              $serviceCheckResultsDataForDisplay);
            $view = new InstallPageView($checkServicesView);
            echo $view->render();
        }

        public function actionSettings()
        {
            $form = new InstallSettingsForm();
            $memcacheServiceHelper = new MemcacheServiceHelper();
            if (!$memcacheServiceHelper->runCheckAndGetIfSuccessful())
            {
                $form->setMemcacheIsNotAvailable();
            }
            $form->hostInfo = InstallUtil::getDefaultHostInfo();
            $form->scriptUrl = InstallUtil::getDefaultScriptUrl($this->getRoute());

            if (isset($_POST['ajax']) && $_POST['ajax'] === 'install-form')
            {
                $this->actionValidateSettings($form);
            }
            else
            {
                if (isset($_POST['InstallSettingsForm']))
                {
                    $form->setAttributes($_POST['InstallSettingsForm']);
                    //in case if additionalSystemCheck it will render its own screen
                    $this->additionalSystemCheck($form);
                    Yii::app()->end(0, false);
                }
            }
            $settingsView = new InstallSettingsView($this->getId(), $this->getModule()->getId(), $form);
            $view = new InstallPageView($settingsView);
            echo $view->render();
        }

        protected function additionalSystemCheck($form)
        {
            $serviceCheckResultsDataForDisplay = CheckServicesUtil::checkServicesAndGetResultsDataForDisplay(true, $form);

            if (count($serviceCheckResultsDataForDisplay[CheckServicesUtil::CHECK_FAILED][ServiceHelper::REQUIRED_SERVICE]) &&
                !defined('IS_TEST'))
            {
                $checkServicesView = new InstallAdditionalCheckServicesView($this->getId(), $this->getModule()->getId(),
                                                                           $serviceCheckResultsDataForDisplay);
                $view = new InstallPageView($checkServicesView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            $this->actionRunInstallation($form);
            Yii::app()->end(0, false);
        }

        protected function actionValidateSettings($model)
        {
            $model->setAttributes($_POST[get_class($model)]);
            $model->validate();
            $errorData = array();
            foreach ($model->getErrors() as $attribute => $errors)
            {
                    $errorData[ZurmoHtml::activeId($model, $attribute)] = $errors;
            }
            echo CJSON::encode($errorData);
            Yii::app()->end(0, false);
        }

        protected function actionRunInstallation($form)
        {
            assert('$form instanceof InstallSettingsForm');
            $nextView = new InstallCompleteView($this->getId(), $this->getModule()->getId());
                $view = new InstallPageView($nextView);
            echo $view->render();

            $template = ZurmoHtml::script("$('#logging-table').prepend('{message}<br/>');");
            $messageStreamer = new MessageStreamer($template);
            InstallUtil::runInstallation($form, $messageStreamer);
            if ($form->installDemoData)
            {
                echo ZurmoHtml::script('$("#progress-table").hide(); $("#demo-data-table").show();');
            }
            else
            {
                $messageStreamer->add(Zurmo::t('InstallModule', 'Locking Installation.'));
                InstallUtil::writeInstallComplete(INSTANCE_ROOT);
                ForgetAllCacheUtil::forgetAllCaches();
                echo ZurmoHtml::script('$("#progress-table").hide(); $("#complete-table").show();');
            }
        }

        /**
         * Added forgetAllCaches in case you are debugging and want to run this action again with a saved db.
         */
        public function actionInstallDemoData()
        {
            RedBeanDatabase::setup(Yii::app()->db->connectionString,
                                   Yii::app()->db->username,
                                   Yii::app()->db->password);
            ForgetAllCacheUtil::forgetAllCaches();
            Yii::app()->user->userModel = User::getByUsername('super');
            $nextView = new InstallCompleteView($this->getId(), $this->getModule()->getId());
            $view = new InstallPageView($nextView);
            echo $view->render();
            $template = ZurmoHtml::script("$('#logging-table').prepend('{message}<br/>');");
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->add(Zurmo::t('InstallModule', 'Starting to load demo data.'));
            $messageLogger = new MessageLogger($messageStreamer);
            DemoDataUtil::load($messageLogger, 6);
            $messageStreamer->add(Zurmo::t('InstallModule', 'Finished loading demo data.'));
            $messageStreamer->add(Zurmo::t('InstallModule', 'Locking Installation.'));
            InstallUtil::writeInstallComplete(INSTANCE_ROOT);
            ForgetAllCacheUtil::forgetAllCaches();
            echo ZurmoHtml::script('$("#progress-table").hide(); $("#complete-table").show();');
        }
    }
?>