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
     * Framework portlet controller.
     */
    abstract class PortletController extends Controller
    {
        /**
         * Save layout changes including:
         *  collapse/show
         *  position change
         *  removed portlets
         *
         */
        public function actionSaveLayout()
        {
            $portlets = Portlet::getByLayoutIdAndUserSortedById($_POST['portletLayoutConfiguration']['uniqueLayoutId'], Yii::app()->user->userModel->id);
            $portletsStillOnLayout = array();
            if (!empty($_POST['portletLayoutConfiguration']['portlets']))
            {
                foreach ($_POST['portletLayoutConfiguration']['portlets'] as $key => $portletPostData)
                {
                    $idParts = explode("_", $portletPostData['id']);
                    $portlets[$idParts[1]]->column    = $portletPostData['column']   + 1;
                    $portlets[$idParts[1]]->position  = $portletPostData['position'] + 1;
                    $portlets[$idParts[1]]->collapsed = BooleanUtil::boolVal($portletPostData['collapsed']);
                    $portlets[$idParts[1]]->save();
                    $portletsStillOnLayout[$idParts[1]] = $idParts[1];
                }
            }
            foreach ($portlets as $portletId => $portlet)
            {
                if (!isset($portletsStillOnLayout[$portletId]))
                {
                    $portlet->delete();
                }
            }
        }

        /**
         * Called using Ajax. Renders a modal popup
         * of the portlet's configuration view.
         * Also called on 'save' of the modal popup form
         * in order to validate form.
         */
        public function actionModalConfigEdit()
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'modal-edit-form')
            {
                $this->actionModalConfigValidate();
            }
            Yii::app()->getClientScript()->setToAjaxMode();
            $portlet = Portlet::getById(intval($_GET['portletId']));
            $portlet->params = array(
                'modalConfigSaveAction' => 'modalConfigSave',
                'controllerId'          => $this->getId(),
                'moduleId'              => $this->getModule()->getId(),
                'uniquePortletPageId'   => $portlet->getUniquePortletPageId(),
            );
            $configurableView = $portlet->getView()->getConfigurationView();
            $view = new ModalView($this, $configurableView);
            echo $view->render();
        }

        protected function actionModalConfigValidate()
        {
            $portlet = Portlet::getById(intval($_GET['portletId']));
            $configurableView = $portlet->getView()->getConfigurationView();
            $configurableView->validate();
            Yii::app()->end(0, false);
        }

        /**
         * Called using Ajax.
         */
        public function actionModalConfigSave($portletId, $uniqueLayoutId, array $portletParams = array())
        {
            $portlet           = Portlet::getById(intval($portletId));
            $configurableView  = $portlet->getView()->getConfigurationView();
            $configurableView->setMetadataFromPost($_POST[$configurableView->getPostArrayName()]);
            $portlet->serializedViewData = serialize($configurableView->getViewMetadata());
            $portlet->save();
            $portlet->forget();
            $this->actionModalRefresh($portletId, $uniqueLayoutId, null, $portletParams);
        }

        /**
         * Refresh portlet contents within a dashboard or details relation view. In the case of details relation view
         * detect if the relationModelId is populated, in which case resolve and populate the relationModel.
         * Resets controller back to default.
         * @param string $portletId
         * @param string $uniqueLayoutId
         * @param string $redirectUrl
         * @param array $portletParams - optional argument which allows you to override the standard parameters.
         * @param bool $portletsAreRemovable
         */
        public function actionModalRefresh($portletId, $uniqueLayoutId, $redirectUrl, array $portletParams = array(),
                                           $portletsAreRemovable = true)
        {
            $portlet = Portlet::getById(intval($portletId));
            $portlet->params = array_merge(array(
                    'controllerId' => 'default',
                    'moduleId'     => $this->getModule()->getId(),
                    'redirectUrl'  => $redirectUrl), $portletParams);
            if (isset($portlet->params['relationModelId']) && $portlet->params['relationModelId'] != '')
            {
                assert('$portlet->params["relationModuleId"] != ""');
                $modelClassName = Yii::app()->findModule($portlet->params["relationModuleId"])->getPrimaryModelName();
                $portlet->params['relationModel'] = $modelClassName::getById((int)$portlet->params['relationModelId']);
            }
            $view = new AjaxPageView(new PortletRefreshView($portlet, $uniqueLayoutId, $this->getModule()->getId(),
                                                            (bool)$portletsAreRemovable));
            echo $view->render();
        }
    }
?>
