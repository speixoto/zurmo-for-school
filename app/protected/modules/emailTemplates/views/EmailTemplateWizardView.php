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

    abstract class EmailTemplateWizardView extends WizardView
    {
        abstract protected function resolveContainingViews(WizardActiveForm $form);

        abstract protected function renderGeneralDataNextPageLinkScript($formName);

        /**
         * @return string|void
         */
        public static function getModuleId()
        {
            return 'emailTemplates';
        }

        /**
         * @return string
         */
        public function getTitle()
        {
            return Zurmo::t('EmailTemplatesModule', 'Email Template Wizard');
        }

        /**
         * @return string
         */
        protected static function getStartingValidationScenario()
        {
            return EmailTemplateWizardForm::GENERAL_DATA_VALIDATION_SCENARIO;
        }

        protected function renderContainingViews(WizardActiveForm $form)
        {
            $views              = $this->resolveContainingViews($form);
            $rows               = count($views);
            $gridView = new GridView($rows, 1);
            foreach ($views as $row => $view)
            {
                $gridView->setView($view, $row, 0);
            }
            return $gridView->render();
        }

        protected function renderConfigSaveAjax($formName)
        {
            assert('is_string($formName)');
            $script     = "linkId = $('#" . $formName . "').find('.attachLoadingTarget').attr('id');";
            $script     .= $this->renderPreGeneralDataNextPageLinkScript($formName);
            $script     .= $this->renderGeneralDataNextPageLinkScript($formName);
            $script     .= $this->renderPostGeneralDataNextPageLinkScript($formName);
            return $script;
        }

        protected function renderPreGeneralDataNextPageLinkScript($formName)
        {
            return;
        }

        protected function renderPostGeneralDataNextPageLinkScript($formName)
        {
            return;
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $this->registerClickFlowScript();
        }

        protected function registerClickFlowScript()
        {
            $this->registerPreGeneralDataPreviousPageLinkScript();
            $this->registerGeneralDataPreviousPageLinkScript();
            $this->registerPostGeneralDataPreviousLinkScript();
        }

        protected function registerPreGeneralDataPreviousPageLinkScript()
        {
        }

        protected function registerGeneralDataPreviousPageLinkScript()
        {
            Yii::app()->clientScript->registerScript('clickflow.generalDataPreviousPageLink', '
                $("#' . GeneralDataForEmailTemplateWizardView::getPreviousPageLinkId() . '").unbind("click");
                $("#' . GeneralDataForEmailTemplateWizardView::getPreviousPageLinkId() . '").bind("click", function()
                    {
                        url = "' . $this->resolveSaveRedirectToListUrl() . '";
                        window.location.href = url;
                        return false;
                    }
                );
          ');
        }

        protected function registerPostGeneralDataPreviousLinkScript()
        {
        }

        protected function resolveSaveRedirectToListUrl()
        {
            $action = $this->resolveListActionByEmailTemplateType();
            return Yii::app()->createUrl(static::getModuleId() . '/' . static::getControllerId() . '/' . $action);
        }

        protected function getFormActionUrl()
        {
            return Yii::app()->createUrl(static::getModuleId() . '/' . static::getControllerId() . '/save',
                                                    array('builtType' => $this->model->builtType));
        }

        protected function resolveListActionByEmailTemplateType()
        {
            $action = 'ListForMarketing';
            if (Yii::app()->request->getQuery('type') == EmailTemplate::TYPE_WORKFLOW)
            {
                $action = 'ListForWorkflow';
            }
            return $action;
        }
    }
?>