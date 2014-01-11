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
     * Base class for the different services and settings that need to be checked for an installation.
     */
    abstract class ServiceHelper
    {
        /**
         * Is the service a required service.
         * @var integer
         */
        const REQUIRED_SERVICE = 1;

        /**
         * Is the service an optional service.
         * @var integer
         */
        const OPTIONAL_SERVICE = 2;

        /**
         *
         * Initial set to false, but after runCheckAndGetIfSuccessful() has been run, it is set to true.
         * @var boolean
         */
        protected $serviceHasBeenChecked = false;

        /**
         * Messages generated during the service check.
         * @var string
         */
        protected $message;

        /**
         * Is the service required
         * @var boolean
         */
        protected $required = true;

        /**
         * Overriden in child to denote the minimum required version of the service.
         * @var string
         */
        protected $minimumVersion;

        /**
         * In some cases, the check for a service version or presence of a service might only result in partial
         * information. An example is the Apache server check. The check might be able to ascertain apache is in
         * fact installed, but the version of apache remains unknown.
         * @var boolean
         */
        protected $checkResultedInWarning = false;

        abstract protected function checkService();

        public function didCheckProduceWarningStatus()
        {
            return $this->checkResultedInWarning;
        }

        /**
         * Called to check service.  Will check service and populate message.
         * @return boolean true/false
         */
        public function runCheckAndGetIfSuccessful()
        {
            $servicePassed = $this->checkService();
            $this->serviceHasBeenChecked = true;
            return $servicePassed;
        }

        public function getMessage()
        {
            if (!$this->serviceHasBeenChecked)
            {
                throw new NotSupportedException();
            }
            return $this->message;
        }

        public function isRequired()
        {
            return $this->required;
        }

        public function getServiceType()
        {
            if ($this->isRequired())
            {
                return self::REQUIRED_SERVICE;
            }
            else
            {
                return self::OPTIONAL_SERVICE;
            }
        }

        /**
         * @param string $methodName
         * @param string $displayLabel
         * @return bool
         */
        protected function checkServiceAndSetMessagesByMethodNameAndDisplayLabel($methodName, $displayLabel)
        {
            assert('$this->minimumVersion != null &&
                    (is_array($this->minimumVersion) || is_string($this->minimumVersion))');
            assert('is_string($methodName)');
            assert('is_string($displayLabel)');
            $actualVersion           = null;
            $minimumVersionLabel     = $this->getMinimumVersionLabel();
            $passed                  = $this->callCheckServiceMethod($methodName, $actualVersion);
            if ($passed)
            {
                $this->message  = $displayLabel . ' ' . Zurmo::t('InstallModule', 'version installed:') . ' ' . $actualVersion;
                $this->message .= ' ' .Zurmo::t('InstallModule', 'Minimum version required:') . ' ' . $minimumVersionLabel;
                return true;
            }
            else
            {
                if ($actualVersion == null)
                {
                    $this->message  = $displayLabel . ' ' . Zurmo::t('InstallModule', 'is not installed.');
                }
                else
                {
                    $this->message  = $displayLabel . ' ' . Zurmo::t('InstallModule', 'version installed:') . ' ' . $actualVersion;
                }
                $this->message .= "\n";
                $this->message .= Zurmo::t('InstallModule', 'Minimum version required:') . ' ' . $minimumVersionLabel;
                return false;
            }
        }

        /**
         * @param string $methodName
         * @param $actualVersion
         * @return mixed
         */
        protected function callCheckServiceMethod($methodName, & $actualVersion)
        {
            assert('is_string($methodName)');
            assert('$actualVersion == null');
            return InstallUtil::$methodName($this->minimumVersion, $actualVersion);
        }

        protected function getMinimumVersionLabel()
        {
            assert('is_string($this->minimumVersion)');
            return $this->minimumVersion;
        }
    }
?>