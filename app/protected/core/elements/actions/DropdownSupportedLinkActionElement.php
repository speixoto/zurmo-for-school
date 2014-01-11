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
     * Parent class for all ActionElements that support rendering as dropdown
     */
    abstract class DropdownSupportedLinkActionElement extends LinkActionElement implements SupportsRenderingDropDownInterface
    {
        const MENU_ID                           = 'ListViewActionMenu';

        abstract public function getElementValue();

        public static function useItemUrlAsElementValue()
        {
            return false;
        }

        public static function getDropDownId()
        {
            return static::MENU_ID;
        }

        public static function  shouldRenderAsDropDownWhenRequired()
        {
            return true;
        }

        public function registerDropDownScripts($dropDownId = null, $scriptName = null)
        {
            if (!$dropDownId)
            {
                $dropDownId = static::getDropDownId();
            }
            if (!$scriptName)
            {
                $scriptName = $dropDownId;
            }
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                Yii::app()->clientScript->registerScript($scriptName, "
                        $('#" . $dropDownId . "').unbind('change.action').bind('change.action', function()
                        {
                            // Heavy dependence on DOM...
                            selectedOption      = $(this).find(':selected');
                            selectedOptionId    = selectedOption.attr('id');
                            if (selectedOptionId)
                            {
                                selectedOptionValue = selectedOption.val();
                                hostInfo            = '" . Yii::app()->request->hostInfo . "';
                                url                 = hostInfo + selectedOptionValue;
                                if (selectedOptionValue.indexOf('/') == 0)
                                {
                                    if ($(this).isValidUrl(url))
                                    {
                                        window.location.href = url;
                                    }
                                }
                                else
                                {
                                    if (typeof window[selectedOptionValue] == 'function')
                                    {
                                        window[selectedOptionValue]();
                                    }
                                }
                                $('#" . $dropDownId . "').val('');
                            }
                        }
                    );
                ", CClientScript::POS_END);
            }
        }

        public function renderMenuItem()
        {
            $parentMenuItem = parent::renderMenuItem();
            $itemOptions    = array( 'itemOptions' => array('id' => get_class($this)));
            $content        = CMap::mergeArray($parentMenuItem, $itemOptions);
            return $content;
        }

        public function getOptGroup()
        {
        }

        public function getOptions()
        {
            return $this->renderMenuItem();
        }

        protected function getListViewGridId()
        {
            if (!isset($this->params['listViewGridId']))
            {
                throw new NotSupportedException();
            }
            return $this->params['listViewGridId'];
        }
    }
?>