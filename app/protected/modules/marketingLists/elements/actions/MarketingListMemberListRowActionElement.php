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

    abstract class MarketingListMemberListRowActionElement extends ListRowActionElement implements RowModelShouldRenderInterface
    {
        const CONTROLLER_ID             = 'defaultPortlet';

        const LINK_ACTION_ELEMENT_CLASS = 'marketing-list-member-actions-link';

        const JS_HANDLER_ID             = 'marketingListMemberLinkActionElementEventHandler';

        abstract protected function getActionId();

        public function getDefaultRoute()
        {
            $params = array('id' => $this->modelId);
            return Yii::app()->createUrl($this->getModuleId() . '/' . static::CONTROLLER_ID
                                                                                . '/' . $this->getActionId() , $params);
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
            if (Yii::app()->clientScript->isScriptRegistered(static::JS_HANDLER_ID))
            {
                return;
            }
            else
            {
                $unlinkConfirmMessage   = CJavaScript::quote($this->getUnlinkTranslatedMessage());
                $errorMessage           = CJavaScript::quote(Zurmo::t('Core', 'There was an error processing your request'));
                // Begin Not Coding Standard
                Yii::app()->clientScript->registerScript(static::JS_HANDLER_ID, '
                    $("a.' . static::LINK_ACTION_ELEMENT_CLASS . '").unbind("click.action").bind("click.action", function(event)
                        {
                            linkUrl     = $(this).attr("href");
                            linkId      = $(this).attr("id");
                            refreshGrid = false;
                            if (linkId.indexOf("delete") !== -1 && !$(this).onAjaxSubmitRelatedListAction("' . $unlinkConfirmMessage . '", "' . $this->getGridId() . '"))
                            {
                                refreshMembersListGridView("' . $this->getGridId() . '");
                            }
                            else
                            {
                                $.ajax({
                                    "error"     : function(xhr, textStatus, errorThrown)
                                                    {
                                                        alert("' . $errorMessage . '");
                                                    },
                                    "success"   : function()
                                                    {
                                                        refreshMembersListGridView("' . $this->getGridId() . '");
                                                    },
                                    "url"       : linkUrl,
                                    "cache"	    : false
                                });
                            }
                            event.preventDefault();
                            return false;
                        }
                    );
                ');
                // End Not Coding Standard
            }
        }

        protected function getUnlinkTranslatedMessage()
        {
            return Zurmo::t('MarketingListsModule', 'Are you sure you want to unlink this record?');
        }

        protected function getModuleId()
        {
            return $this->moduleId;
        }
    }
?>