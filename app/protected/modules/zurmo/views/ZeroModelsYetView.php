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
     * Base class for showing in the user interface when there are no models yet visible for a particular user
     * in a given module.
     */
    abstract class ZeroModelsYetView extends SplashView
    {
        protected $controllerId;

        protected $moduleId;

        protected $actionId;

        abstract protected function getCreateLinkDisplayLabel();

        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param string $modelClassName
         */
        public function __construct($controllerId, $moduleId, $modelClassName)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($modelClassName)');
            $this->controllerId   = $controllerId;
            $this->moduleId       = $moduleId;
            $this->modelClassName = $modelClassName;
        }

        /**
         * Render Zero Model View Content
         * @return string
         */
        protected function renderContent()
        {
            $params             = $this->getCreateLinkParams();
            $createLinkElement  = new CreateLinkActionElement($this->controllerId, $this->moduleId, null, $params);
            $content = '<div class="' . $this->getIconName() . '">';
            $content .= $this->getMessageContent();
            $content .= $createLinkElement->render();
            $content .= '</div>';
            return $content;
        }

        /**
         * Get create link params
         * @return array
         */
        protected function getCreateLinkParams()
        {
            $label              = $this->getCreateLinkDisplayLabel();
            return array('htmlOptions' => array('class' => 'z-button green-button'), 'label' => $label);
        }

        /**
         * @return string
         */
        protected function getIconName()
        {
            return $this->modelClassName;
        }
    }
?>
