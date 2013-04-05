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

    abstract class MarketingListMemberLinkActionElement extends ListRowActionElement implements RowModelShouldRenderInterface
    {
        const CONTROLLER_ID             = 'defaultPortlet';

        const LINK_ACTION_ELEMENT_CLASS = 'marketing-list-member-actions-link';

        abstract protected function getActionId();

        public function getDefaultRoute()
        {
            $params = array('id' => $this->modelId);
            return Yii::app()->createUrl($this->moduleId . '/' . static::CONTROLLER_ID . '/' . $this->getActionId() , $params);
        }

        public function render()
        {
            throw new NotSupportedException;
        }

        public function renderMenuItem()
        {
            $this->registerScripts();
            return array(
                'label'           => $this->getLabel(),
                'url'             => $this->getDefaultRoute(),
                'linkOptions'     => $this->getHtmlOptions(),
            );
        }

        protected function getHtmlOptions()
        {
            $htmlOptions            = parent::getHtmlOptions();
            $htmlOptions['id']      = $this->getLinkId();
            $htmlOptions['class']   = static::LINK_ACTION_ELEMENT_CLASS;
            return $htmlOptions;
        }

        protected function getLinkId()
        {
            return $this->getGridId(). '-' . strtolower($this->getActionType()) . '-' . $this->modelId;
        }

        protected function registerScripts()
        {
            $this->registerUnifiedEventHander();
        }

        protected function registerUnifiedEventHander()
        {
            if (Yii::app()->clientScript->isScriptRegistered('marketingListMemberLinkActionElementEventHandler'))
            {
                return;
            }
            else
            {
                $unlinkConfirmMessage   = CJavaScript::quote(Zurmo::t('MarketingListsModule', 'Are you sure you want to unlink this record?'));
                $errorMessage           = CJavaScript::quote(Zurmo::t('Core', 'There was an error processing your request'));
                Yii::app()->clientScript->registerScript('marketingListMemberLinkActionElementEventHandler', '
                    $("a.' . static::LINK_ACTION_ELEMENT_CLASS . '").unbind("click.action").bind("click.action", function(event)
                        {
                            linkUrl = $(this).attr("href");
                            linkId  = $(this).attr("id");
                            if (linkId.indexOf("delete") !== -1 && !onAjaxSubmitRelatedListAction("' . $unlinkConfirmMessage . '", "' . $this->getGridId() . '"))
                            {
                                event.preventDefault();
                            }
                            $.ajax({
                                "error"     : function(xhr, textStatus, errorThrown)
                                                {
                                                    alert("' . $errorMessage . '");
                                                },
                                "success"   : function()
                                                {
                                                    $("#" + linkId).closest(".items").parent().find(".pager").find(".refresh").find("a").click();
                                                },
                                "url"       : linkUrl,
                                "cache"	    : false
                            });
                            event.preventDefault();
                        }
                    );
                ');
            }
        }
    }
?>