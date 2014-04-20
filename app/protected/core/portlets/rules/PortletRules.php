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

    abstract class PortletRules
    {
        protected $viewClassName;

        public function __construct($viewClassName)
        {
            assert('is_string($viewClassName)');
            $this->viewClassName = $viewClassName;
        }

        /**
         * @returns the first part of the view name for example 'WorldClock'
         * would be returned if the view was 'WorldClockView'
         */
        public function getType()
        {
            return substr($this->viewClassName, 0, strlen($this->viewClassName) - strlen('View'));
        }

        /**
         * Views following this rule, are they
         * able to be shown on a Dashboard
         * @return boolean true/false
         */
        public function allowOnDashboard()
        {
            return false;
        }

        /**
         * Views following this rule, are they
         * able to be shown more than once on a dashboard
         * @return boolean true/false
         */
        public function allowMultiplePlacementOnDashboard()
        {
            return true;
        }

        /**
         * Views following this rule, are they
         * able to be shown on a relation view
         * @return boolean true/false
         */
        public function allowOnRelationView()
        {
            return false;
        }

        public function resolveIconTypeName()
        {
            return 'type-'. $this->getIconType();
        }

        public function resolveIconName()
        {
            return 'icon-'. $this->getIconType();
        }

        public function getDescription()
        {
            $viewClassName   = $this->viewClassName;
            return $viewClassName::getPortletDescription();
        }

        /**
         * Override if additional rules are needed to determine access rights to a portlet
         * @param User $user
         * @return bool
         */
        public function canUserAccessPortlet(User $user)
        {
            return true;
        }

        protected function getIconType()
        {
            $viewClassName   = $this->viewClassName;
            $moduleClassName = $viewClassName::getModuleClassName();
            return $moduleClassName::getDirectoryName();
        }
    }
?>