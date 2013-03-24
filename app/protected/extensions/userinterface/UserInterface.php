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

    // Application component that helps to determine are visitors are using mobile, tablet or desktop computer.
    class UserInterface extends CApplicationComponent
    {
        const MOBILE                              = 'Mobile';
        const TABLET                              = 'Tablet';
        const DESKTOP                             = 'Desktop';
        const DEFAULT_USER_INTERFACE_COOKIE_NAME  = "DefaultUserInterfaceType";
        const SELECTED_USER_INTERFACE_COOKIE_NAME = "UserInterfaceType";

        protected $defaultUserInterfaceType   = null;
        protected $selectedUserInterfaceType  = null;

        public function init()
        {
            $this->setDefaultUserInterfaceType();
            $this->setSelectedUserInterfaceType();
        }

        /**
         * Get selected user interface type
         */
        public function getSelectedUserInterfaceType()
        {
            return $this->selectedUserInterfaceType;
        }

        /**
         * Get default user interface type
         */
        public function getDefaultUserInterfaceType()
        {
            return $this->defaultUserInterfaceType;
        }

        /**
         * Set default interface type, based only on user device
         * User can't change this option.
         * @param $userInterfaceType
         */
        public function setDefaultUserInterfaceType()
        {
            if (!isset(Yii::app()->request->cookies[self::DEFAULT_USER_INTERFACE_COOKIE_NAME]))
            {
                $userInterfaceType = $this->detectUserInterfaceType();
                Yii::app()->request->cookies[self::DEFAULT_USER_INTERFACE_COOKIE_NAME] =
                    new CHttpCookie(self::DEFAULT_USER_INTERFACE_COOKIE_NAME, $userInterfaceType);
                $this->defaultUserInterfaceType = $userInterfaceType;
            }
            else
            {
                $this->defaultUserInterfaceType = Yii::app()->request->cookies[self::DEFAULT_USER_INTERFACE_COOKIE_NAME]->value;
            }
        }

        /**
         * Set interface type, selected by user
         * For example if user is using mobile device, he should be able to switch to desktop interface.
         * If user is using desktop interface, there are no sense to switch to mobile interface, except is user is using
         * mobile device and selected desktop interface.
         * Same ideas are implemented tor tablet devices.
         * @param $userInterfaceType
         */
        public function setSelectedUserInterfaceType($userInterfaceType = null)
        {
            if (!isset(Yii::app()->request->cookies[self::SELECTED_USER_INTERFACE_COOKIE_NAME]) || isset($userInterfaceType))
            {
                if (!isset($userInterfaceType))
                {
                    $userInterfaceType = $this->detectUserInterfaceType();
                }

                Yii::app()->request->cookies[self::SELECTED_USER_INTERFACE_COOKIE_NAME] =
                    new CHttpCookie(self::SELECTED_USER_INTERFACE_COOKIE_NAME, $userInterfaceType);
                $this->selectedUserInterfaceType = $userInterfaceType;
            }
            else
            {
                $this->selectedUserInterfaceType = Yii::app()->request->cookies[self::SELECTED_USER_INTERFACE_COOKIE_NAME]->value;
            }
        }

        /**
         * Does user use mobile
         * @return bool
         */
        public function isMobile()
        {
            return true;//$this->selectedUserInterfaceType == self::MOBILE;
        }

        /**
         * Does user using tablet
         * @return bool
         */
        public function isTablet()
        {
            return true;//$this->selectedUserInterfaceType == self::TABLET;
        }

        /**
         * Does user use desktop computer
         * @return bool
         */
        public function isDesktop()
        {
            return false;// ($this->selectedUserInterfaceType != self::MOBILE && $this->selectedUserInterfaceType != self::TABLET);
        }

        /**
         * Does user use mobile
         * @return bool
         */
        public function isResolvedToMobile()
        {
            return $this->defaultUserInterfaceType == self::MOBILE;
        }

        /**
         * Does user using tablet
         * @return bool
         */
        public function isResolvedToTablet()
        {
            return $this->defaultUserInterfaceType == self::TABLET;
        }

        /**
         * Does user use desktop computer
         * @return bool
         */
        public function isResolvedToDesktop()
        {
            return ($this->defaultUserInterfaceType != self::MOBILE && $this->defaultUserInterfaceType != self::TABLET);
        }

        /**
         * Determine user interface type, based on device signature.
         * @return string
         */
        protected function detectUserInterfaceType()
        {
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/Mobile_Detect.php';
            $detector = new Mobile_Detect;
            if ($detector->isMobile())
            {
                $userInterfaceType = self::MOBILE;
            }
            else if ($detector->isTablet())
            {
                $userInterfaceType = self::TABLET;
            }
            else
            {
                $userInterfaceType = self::DESKTOP;
            }
            return $userInterfaceType;
        }
    }
