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

    class ExecutionTimeListener implements PHPUnit_Framework_TestListener
    {
        private $__timeLimit;
        private $__precision;

        public function __construct($timeLimit = 0, $precision = 2)
        {
            $this->__timeLimit = $timeLimit;
            $this->__precision = $precision;
        }

        public function startTest(PHPUnit_Framework_Test $test)
        {
            echo PHP_EOL . "Starting: " . $test->getName();
        }

        public function endTest(PHPUnit_Framework_Test $test, $length)
        {
            if ($length > $this->__timeLimit)
            {
                echo PHP_EOL . "Name: " . $test->getName() . " took " . round($length, $this->__precision) . " second(s)" . PHP_EOL;
            }
        }

        public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
        {
        }

        public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
        {
        }

        public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
        {
        }

        public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
        {
        }

        public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
        {
        }

        public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
        {
        }
    }
?>