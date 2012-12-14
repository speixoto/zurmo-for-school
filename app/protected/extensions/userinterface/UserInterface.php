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
        const MOBILE      = 'Mobile';
        const TABLET      = 'Tablet';
        const DESKTOP     = 'Desktop';
        const COOKIE_NAME = "UserInterfaceType";

        protected $userInterfaceType  = null;

        public function init()
        {
            if (!isset(Yii::app()->request->cookies[self::COOKIE_NAME]))
            {
                $userInterfaceType = $this->detectUserInterfaceType();
                $this->setType($userInterfaceType);
            }
            else
            {
                $this->userInterfaceType = Yii::app()->request->cookies[self::COOKIE_NAME]->value;
            }
        }

        /**
         * Get user interface type
         * @return string
         */
        public function getType()
        {
            return $this->userInterfaceType;
        }

        /**
         * Set interface type.
         * User can set different interface type, no matter of device he is using.
         * @param $userInterfaceType
         */
        public function setType($userInterfaceType)
        {
            $this->userInterfaceType = $userInterfaceType;
            Yii::app()->request->cookies[self::COOKIE_NAME] =  new CHttpCookie(self::COOKIE_NAME, $userInterfaceType);
        }

        /**
         * Does user use mobile
         * @return bool
         */
        public function isMobile()
        {
            return $this->userInterfaceType == self::MOBILE;
        }

        /**
         * Does user using tablet
         * @return bool
         */
        public function isTablet()
        {
            return $this->userInterfaceType == self::TABLET;
        }

        /**
         * Does user use desktop computer
         * @return bool
         */
        public function isDesktop()
        {
            return ($this->userInterfaceType != self::MOBILE && $this->userInterfaceType != self::TABLET);
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
