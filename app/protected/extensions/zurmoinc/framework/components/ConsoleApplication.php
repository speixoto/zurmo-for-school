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
     * Override to handle zurmo specific requirements for the console application. Certain methods are expected
     * to be available in the application that are added here. Additional the session component is defined as some
     * code running the console application will require access to the session component even though it is not utilized
     * in the console application.
     */
    class ConsoleApplication extends CConsoleApplication
    {
        /**
         * If the application has been installed or not.
         * @var boolean
         */
        protected $installed;

        /**
         * Is application in maintanance mode or not.
         * @var boolean
         */
        protected $maintananceMode;

        public function isApplicationInstalled()
        {
            return $this->installed;
        }

        public function isApplicationInMaintananceMode()
        {
            return $this->maintananceMode;
        }

        protected function registerCoreComponents()
        {
            parent::registerCoreComponents();
            $components = array(
                'session' => array(
                    'class' => 'CHttpSession',
                ),
            );

            $this->setComponents($components);
        }

        /**
         * Adding expected WebApplication method.
         */
        public function getSession()
        {
            return $this->getComponent('session');
        }

        /**
         * Adding expected WebApplication method.
         */
        public function findModule($moduleID)
        {
            return WebApplication::findModuleInApplication($moduleID);
        }
    }
?>