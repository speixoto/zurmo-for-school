<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * The configurable View for a model detail view with relation views.
     */
    abstract class ConfigurableDetailsAndRelationsView extends DetailsAndRelationsView
    {
        public function __construct($controllerId, $moduleId, $params)
        {
            assert('isset($params["controllerId"])');
            assert('isset($params["relationModuleId"])');
            assert('isset($params["relationModel"])');
            $this->controllerId        = $controllerId;
            $this->moduleId            = $moduleId;
            $this->uniqueLayoutId      = get_class($this);
            $this->params              = $params;
            $model                     = $params["relationModel"];
            $this->modelId             = $model->id;
        }

        protected function renderContent()
        {
            $getData = GetUtil::getData();
            $metadata         = self::getMetadata();
            if(isset($getData['lockPortlets']))
            {
                $lockPortlets = (bool)$getData['lockPortlets'];
                if($lockPortlets == '1')
                {
                    ZurmoDefaultViewUtil::setLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView', true);
                }
                else
                {
                    ZurmoDefaultViewUtil::setLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView', false);
                }
            }

            $content                = $this->renderActionElementBar(true);
            $isViewLocked           = ZurmoDefaultViewUtil::getLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView');
            $portletsAreRemovable   = true;
            $portletsAreMovable     = true;
            if($isViewLocked == true)
            {
                $portletsAreRemovable   = false;
                $portletsAreMovable     = false;
            }
            $viewClassName    = static::getModelRelationsSecuredPortletFrameViewClassName();
            $configurableView = new $viewClassName( $this->controllerId,
                                                    $this->moduleId,
                                                    $this->uniqueLayoutId,
                                                    $this->params,
                                                    $metadata,
                                                    false,
                                                    $portletsAreMovable,
                                                    false,
                                                    '50,50',
                                                    $portletsAreRemovable);
            $content          .=  $configurableView->render();
            $content          .= $this->renderScripts();
            return $content;
        }

        protected function renderActionElementBar($renderedInForm)
        {
            $getData = GetUtil::getData();
            if (Yii::app()->userInterface->isMobile() === false)
            {
                $isViewLocked     = ZurmoDefaultViewUtil::getLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView');
                $url              = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/details?id=' . $getData['id']);
                $lockLink = '';
                if($isViewLocked === true)
                {
                    $lockLink = "<a href='" . $url . "&lockPortlets=0' class='icon-lock'>" . Zurmo::t('Core', 'Unlock') . "</a>";
                }
                else
                {
                    $lockLink = "<a href='" . $url . "&lockPortlets=1' class='icon-unlock'>" . Zurmo::t('Core', 'Lock') . "</a>";
                }
            }
            else
            {
                $lockLink = '';
            }
            $content  = '<div class="view-toolbar-container clearfix"><div class="view-toolbar">';
            $content .= $lockLink . parent::renderActionElementBar($renderedInForm);
            $content .= '</div></div>';
            return $content;
        }

        protected static function resolveAjaxOptionsForAddPortlet()
        {
            $title = Zurmo::t('HomeModule', 'Add Portlet');
            return ModalView::getAjaxOptionsForModalLink($title);
        }
    }
?>