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

    /**
     * Parent class for all LinkActionElements that may apply to all or selected records.
     */
    abstract class MassActionLinkActionElement extends LinkActionElement implements SupportsRenderingDropDownInterface
    {
        const SELECTED_MENU_TYPE = 1;

        const ALL_MENU_TYPE      = 0;

        const MENU_ID            = 'ListViewExportActionMenu';

        protected $gridId;

        protected $selectedMenuItemName;

        protected $allMenuItemName;

        abstract protected function getActionName();

        abstract protected function getSelectedMenuNameSuffix();

        abstract protected function getAllMenuNameSuffix();

        abstract protected function getScriptNameSuffixForSelectedMenu();

        abstract protected function getScriptNameSuffixForAllMenu();

        public static function getDropDownId()
        {
            return static::MENU_ID;
        }

        public function __construct($controllerId, $moduleId, $modelId, $params = array())
        {
            parent::__construct($controllerId, $moduleId, $moduleId, $params);
            $this->gridId = $this->getListViewGridId();
            $this->selectedMenuItemName = $this->gridId . $this->getSelectedMenuNameSuffix();
            $this->allMenuItemName = $this->gridId . $this->getAllMenuNameSuffix();
            $this->registerUnifiedEventHandler();
        }

        public function render()
        {
            $this->registerMenuScripts();
            $menuItems = array('label' => $this->getMenuHeader(), 'url' => null,
                                'items' => $this->getMenuItems());
            return $this->renderMenuWidget($menuItems);
        }

        public function getActionNameForCurrentElement()
        {
            return $this->getActionName();
        }

        public function getActionType()
        {
            throw new NotSupportedException();
        }

        public function registerUnifiedEventHandler()
        {
            if (Yii::app()->clientScript->isScriptRegistered('massActionLinkActionElementEventHandler'))
            {
                return;
            }
            else
            {
                Yii::app()->clientScript->registerScript('massActionLinkActionElementEventHandler', "
                        function massActionLinkActionElementEventHandler(elementType, gridId, baseUrl, actionId, pageVarName)
                        {
                            selectAll = '';
                            if (elementType == " . static::SELECTED_MENU_TYPE . ")
                            {
                                if ($('#' + gridId + '-selectedIds').val() == '')
                                {
                                    alert('You must select at least one record');
                                    $(this).val('');
                                    return false;
                                }
                            }
                            else
                            {
                                selectAll = 1;
                            }
                            var options =
                            {
                                url     : $.fn.yiiGridView.getUrl(gridId),
                                baseUrl : baseUrl
                            }
                            if (options.url.split( '?' ).length == 2)
                            {
                                options.url = options.baseUrl + '/' + actionId + '?' + options.url.split( '?' )[1];
                            }
                            else
                            {
                                options.url = options.baseUrl + '/' + actionId;
                            }
                            if (elementType == " . static::SELECTED_MENU_TYPE . ")
                            {
                                addListViewSelectedIdsToUrl(gridId, options);
                            }
                            var data = '' + actionId + '=' + '&selectAll=' + selectAll + '&ajax=&' + pageVarName + '=1';
                            url = $.param.querystring(options.url, data);
                            url += '" . $this->resolveAdditionalQueryStringData() ."';
                            window.location.href = url;
                            return false;
                        }
                ");
            }
        }

        public function getOptGroup()
        {
            return $this->getMenuHeader();
        }

        public function getOptions()
        {
            return $this->getMenuItems();
        }

        public function registerDropDownScripts($dropDownId = null, $scriptName = null)
        {
            $dropDownId = ($dropDownId)? $dropDownId : static::getDropDownId();
            $scriptName = ($scriptName)? $scriptName : $dropDownId;
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                Yii::app()->clientScript->registerScript($scriptName, "
                        $('#" . $dropDownId . "').unbind('change.action').bind('change.action', function()
                        {
                            selectedOption      = $(this).find(':selected');
                            selectedOptionId    = selectedOption.attr('id');
                            if (selectedOptionId)
                            {
                                selectedOptionValue = selectedOption.val();
                                optionType          = selectedOptionId.slice(-3);
                                actionName          = selectedOptionValue.slice(0, selectedOptionValue.indexOf('_'));
                                if (optionType == 'All')
                                {
                                    menuType = " . static::ALL_MENU_TYPE . ";
                                }
                                else
                                {
                                    menuType = " . static::SELECTED_MENU_TYPE . ";
                                }
                                massActionLinkActionElementEventHandler(".
                                        "menuType, ".
                                        " '" . $this->gridId. "',".
                                        " '" . Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId) . "',".
                                        " actionName,".
                                        " '" . $this->getPageVarName() ."'".
                                        ");
                            }
                            else
                            {
                            }
                        }
                        );
                    ");
            }
        }

        protected function resolveAdditionalQueryStringData()
        {
            return null;
        }

        protected function registerMenuScripts()
        {
            $this->registerScriptForAllMenu();
            $this->registerScriptForSelectedMenu();
        }

        protected function registerScriptForSelectedMenu()
        {
            $this->registerScriptForMenuType(static::SELECTED_MENU_TYPE);
        }

        protected function registerScriptForAllMenu()
        {
            $this->registerScriptForMenuType(static::ALL_MENU_TYPE);
        }

        protected function registerScriptForMenuType($menuType)
        {
            if ($menuType === static::SELECTED_MENU_TYPE)
            {
                $scriptNameSuffix       = $this->getScriptNameSuffixForSelectedMenu();
                $menuItemName           = $this->selectedMenuItemName;
            }
            else
            {
                $scriptNameSuffix       = $this->getScriptNameSuffixForAllMenu();
                $menuItemName           = $this->allMenuItemName;
            }
            Yii::app()->clientScript->registerScript($this->gridId . $scriptNameSuffix,
                            "$('#" . $menuItemName . "').unbind('click.action').bind('click.action', function()
                                {
                                    " . $this->getEventHandlerScriptContentForMenuType($menuType) ."
                                }
                            );");
        }

        protected function getEventHandlerScriptContentForMenuType($menuType)
        {
            return "massActionLinkActionElementEventHandler(".
                            $menuType . ",".
                            " '" . $this->gridId. "',".
                            " '" . Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId) . "',".
                            " '" . $this->getActionName(). "',".
                            " '" . $this->getPageVarName() ."'".
                            ")";
        }

        protected function getMenuItems()
        {
            return array(
                array('label'   => Zurmo::t('Core', 'Selected'),
                        'url'     => '#',
                        'itemOptions' => array( 'id'   => $this->selectedMenuItemName)),
                array('label'   => Zurmo::t('Core', 'All Results'),
                        'url'     => '#',
                        'itemOptions' => array( 'id'   => $this->allMenuItemName)));
        }

        protected function getMenuHeader()
        {
            return $this->getLabel();
        }

        protected function getMenuId()
        {
            return static::MENU_ID;
        }

        protected function renderMenuWidget($items)
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ActionMenu");
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                                    'htmlOptions'   => array('id' => $this->getMenuId()),
                                    'items'         => array($items),
                                    ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ActionMenu'];
        }

        protected function getListViewGridId()
        {
            if (!isset($this->params['listViewGridId']))
            {
                throw new NotSupportedException();
            }
            return $this->params['listViewGridId'];
        }

        protected function getPageVarName()
        {
            if (!isset($this->params['pageVarName']))
            {
                throw new NotSupportedException();
            }
            return $this->params['pageVarName'];
        }

        protected function getDefaultRoute()
        {
            return $this->moduleId . '/' . $this->controllerId . '/' . $this->getActionName() . '/';
        }

        protected function getDefaultLabel()
        {
            throw new NotSupportedException;
        }
    }
?>