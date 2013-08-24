<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    // we don't want to be left in dark if any occurs occur.
    error_reporting(E_ALL);
    ini_set('display_errors', true);

    $cwd = getcwd();

    require_once('../common/PhpUnitServiceUtil.php');
    require_once('../common/testRoots.php');
    require_once('../common/bootstrap.php');

    class TestSuite
    {
        protected static $dependentTestModelClassNames = array();

        public static function suite()
        {
            global $argv;

            PhpUnitServiceUtil::checkVersion();
            $usage = PHP_EOL                                                                                                    .
                "  Usage: phpunit [phpunit options] TestSuite.php <All|Framework|Misc|moduleName|TestClassName> [custom options]" . PHP_EOL .
                PHP_EOL                                                                                                    .
                "    All                     Run all tests." . PHP_EOL                                                    .
                "    Framework               Run the tests in app/protected/extensions/framework/tests/unit." . PHP_EOL          .
                "    Misc                    Run the tests in app/protected/tests/unit." . PHP_EOL                               .
                "    moduleName              Run the tests in app/protected/modules/moduleName/tests/unit." . PHP_EOL            .
                "    TestClassName           Run the tests in TestClassName.php, wherever that happens to be." . PHP_EOL         .
                PHP_EOL                                                                                                    .
                "  Custom Options:" . PHP_EOL                                                                                   .
                PHP_EOL                                                                                                    .
                "    --only-walkthroughs     For the specified test, only includes tests under a walkthroughs directory." . PHP_EOL .
                "    --exclude-walkthroughs  For the specified test, exclude tests under a walkthroughs directory." . PHP_EOL       .
                "    --only-benchmarks       For the specified test, only includes tests under a benchmarks directory." . PHP_EOL .
                "    --exclude-benchmarks    For the specified test, exclude tests under a benchmarks directory." . PHP_EOL      .
                "    --reuse-schema          Reload a previously auto build database. (Will auto build if there is no" . PHP_EOL .
                "                            previous one. The auto built schema is dumped to the system temp dir in" . PHP_EOL  .
                "                            autobuild.sql.)" . PHP_EOL                                                          .
                PHP_EOL                                                                                                    .
                "  Examples:" . PHP_EOL                                                                                         .
                PHP_EOL                                                                                                    .
                "    phpunit --verbose TestSuite.php accounts (Run the tests in the Accounts module.)" . PHP_EOL                . // Not Coding Standard
                "    phpunit TestSuite.php RedBeanModelTest   (Run the tests in RedBeanModelTest.php.)" . PHP_EOL               .
                PHP_EOL                                                                                                    .
                "    To run specific tests use the phpunit --filter <regex> option." . PHP_EOL                                  . // Not Coding Standard
                "    phpunit has its own options. Check phpunit --help." . PHP_EOL . PHP_EOL;                                             // Not Coding Standard

            $onlyWalkthroughs     =  self::customOptionSet('--only-walkthroughs',     $argv);
            $excludeWalkthroughs  =  self::customOptionSet('--exclude-walkthroughs',  $argv);
            $onlyBenchmarks       =  self::customOptionSet('--only-benchmarks',       $argv);
            $excludeBenchmarks    =  self::customOptionSet('--exclude-benchmarks',    $argv);
            $reuse                =  self::customOptionSet('--reuse-schema',          $argv);

            if ($argv[count($argv) - 2] != 'TestSuite.php')
            {
                echo $usage;
                exit;
            }

            if ($onlyWalkthroughs && $onlyBenchmarks)
            {
                echo $usage;
                echo "It doesn't have sense to select both \"--only-walkthroughs\" and \"--only-benchmarks\" options. " . PHP_EOL . PHP_EOL;
                exit;
            }

            $whatToTest           = $argv[count($argv) - 1];
            $includeUnitTests     = !$onlyWalkthroughs && !$onlyBenchmarks;
            $includeWalkthroughs  = !$excludeWalkthroughs && !$onlyBenchmarks;
            $includeBenchmarks    = !$excludeBenchmarks && !$onlyWalkthroughs;

            $suite = new PHPUnit_Framework_TestSuite();
            $suite->setName("$whatToTest Tests");
            self::buildAndAddSuiteFromDirectory($suite, 'Framework', COMMON_ROOT . '/protected/core/tests/unit', $whatToTest, true, false, $includeBenchmarks);
            $moduleDirectoryName = COMMON_ROOT . '/protected/modules';
            if (is_dir($moduleDirectoryName))
            {
                $moduleNames = scandir($moduleDirectoryName);
                foreach ($moduleNames as $moduleName)
                {
                    if ($moduleName != '.' &&
                        $moduleName != '..')
                    {
                        $moduleUnitTestDirectoryName = "$moduleDirectoryName/$moduleName/tests/unit";
                        self::buildAndAddSuiteFromDirectory($suite, $moduleName, $moduleUnitTestDirectoryName, $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks);
                    }
                }
            }
            self::buildAndAddSuiteFromDirectory($suite, 'Misc',            COMMON_ROOT . '/protected/tests/unit',                     $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks);
            self::buildAndAddSuiteFromDirectory($suite, 'Commands',        COMMON_ROOT . '/protected/commands/tests/unit',             $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks);
////////////////////////////////////////////////////////////////////////////////
// Temporary - See Readme.txt in the notSupposedToBeHere directory.
            self::buildAndAddSuiteFromDirectory($suite, 'BadDependencies', COMMON_ROOT . '/protected/tests/unit/notSupposedToBeHere', $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks);
////////////////////////////////////////////////////////////////////////////////

            if ($suite->count() == 0)
            {
                echo $usage;
                echo "  No tests found for '$whatToTest'." . PHP_EOL . PHP_EOL;
                exit;
            }
            echo "Testing with database: '"  . Yii::app()->db->connectionString . '\', ' .
                                                'username: \'' . Yii::app()->db->username         . "'." . PHP_EOL;
            
            static::setupDatabaseConnection();
            if (!$reuse)
            {
                if (!is_writable(sys_get_temp_dir()))
                {
                    echo PHP_EOL .PHP_EOL . "Temp directory must be writable to store reusable schema" . PHP_EOL; // Not Coding Standard
                    echo "Temp directory: " . sys_get_temp_dir() .  PHP_EOL . PHP_EOL; // Not Coding Standard
                    exit;
                }
                echo "Auto building database schema..." . PHP_EOL;
                ZurmoRedBean::$writer->wipeAll();
                $messageLogger = new MessageLogger();
                InstallUtil::autoBuildDatabase($messageLogger, true);
                $messageLogger->printMessages();
                ReadPermissionsOptimizationUtil::rebuild();
                assert('RedBeanDatabase::isSetup()');
                Yii::app()->user->userModel = InstallUtil::createSuperUser('super', 'super');

                echo "Saving auto built schema..." . PHP_EOL;
                $schemaFile = sys_get_temp_dir() . '/autobuilt.sql';
                $success = preg_match("/;dbname=([^;]+)/", Yii::app()->db->connectionString, $matches); // Not Coding Standard
                assert('$success == 1');
                $databaseName = $matches[1];

                $systemOutput = system('mysqldump -u' . Yii::app()->db->username .
                                        ' -p' . Yii::app()->db->password .
                                        ' ' . $databaseName            .
                                        " > $schemaFile");
                if ($systemOutput != null)
                {
                    echo 'Dumping schema using system command. Output: ' . $systemOutput . PHP_EOL . PHP_EOL;
                }
            }
            else
            {
                echo PHP_EOL;
                $messageLogger  = new MessageLogger();
                static::buildDependentTestModels($messageLogger);
                $messageLogger->printMessages();
            }
            static::closeDatabaseConnection();
            return $suite;
        }

        public static function customOptionSet($customOption, &$argv)
        {
            $set = in_array($customOption, $argv);
            $argv = array_values(array_diff($argv, array($customOption)));
            return $set;
        }

        public static function buildAndAddSuiteFromDirectory($parentSuite, $name, $directoryName, $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks)
        {
            if ($includeUnitTests)
            {
                self::buildAndAddSuiteFromDirectory2($parentSuite, $name, $directoryName,                  $whatToTest);
            }
            if ($includeWalkthroughs)
            {
                self::buildAndAddSuiteFromDirectory2($parentSuite, $name, $directoryName . '/walkthrough', $whatToTest);
            }
            if ($includeBenchmarks)
            {
                self::buildAndAddSuiteFromDirectory2($parentSuite, $name, $directoryName . '/benchmark', $whatToTest);
            }
        }

        public static function buildAndAddSuiteFromDirectory2($parentSuite, $name, $directoryName, $whatToTest)
        {
            assert('is_string($directoryName) && $directoryName != ""');
            if (is_dir($directoryName))
            {
                $suite = new PHPUnit_Framework_TestSuite();
                $suite->setName(ucfirst($name) . ' Tests');
                $fileNames = scandir($directoryName);
                foreach ($fileNames as $fileName)
                {
                    if (substr($fileName, strlen($fileName) - strlen('Test.php')) == 'Test.php')
                    {
                        require_once("$directoryName/$fileName");
                        $className = substr($fileName, 0, strlen($fileName) - 4);
                        if (substr($className, strlen($className) - 8) != 'BaseTest')
                        {
                            if ($whatToTest == 'All'                                           ||
                                $whatToTest == 'Framework'       && $name == 'Framework'       ||
                                $whatToTest == 'Misc'            && $name == 'Misc'            ||
                                $whatToTest == 'BadDependencies' && $name == 'BadDependencies' ||
                                $whatToTest == $name                                           ||
                                $whatToTest == $className)
                            {
                                $suite->addTestSuite(new PHPUnit_Framework_TestSuite($className));
                                static::resolveDependentTestModelClassNamesForClass($className);
                            }
                        }
                    }
                }
                if ($suite->count() > 0)
                {
                    $parentSuite->addTestSuite($suite);
                }
            }
        }

        public static function buildDependentTestModels($messageLogger)
        {
            if (!empty(static::$dependentTestModelClassNames))
            {
                RedBeanModelsToTablesAdapter::generateTablesFromModelClassNames(static::$dependentTestModelClassNames,
                                                                                                    $messageLogger);
                // TODO: @Shoaibi/@Jason: Critical: Shouldn't ::rebuild take care of this.
                foreach (static::$dependentTestModelClassNames as $modelClassName)
                {
                    if (is_subclass_of($modelClassName, 'SecurableItem') && $modelClassName::hasReadPermissionsOptimization())
                    {
                        ReadPermissionsOptimizationUtil::recreateTable(
                            ReadPermissionsOptimizationUtil::getMungeTableName($modelClassName));
                    }
                }
            }
        }

        protected static function resolveDependentTestModelClassNamesForClass($className)
        {
            if (@class_exists($className)) // some class definitions are wrapped inside if blocks
            {
                $dependentTestModelClassNames = $className::getDependentTestModelClassNames();
                if (!empty($dependentTestModelClassNames))
                {
                    $dependentTestModelClassNames = CMap::mergeArray(static::$dependentTestModelClassNames,
                        $dependentTestModelClassNames);
                    static::$dependentTestModelClassNames = array_unique($dependentTestModelClassNames);
                }
            }
        }

        protected static function setupDatabaseConnection($force = false)
        {
            if (!RedBeanDatabase::isSetup() || $force)
            {
                RedBeanDatabase::setup(Yii::app()->db->connectionString,
                                        Yii::app()->db->username,
                                        Yii::app()->db->password);
            }
        }

        protected static function closeDatabaseConnection()
        {
            if (RedBeanDatabase::isSetup())
            {
                RedBeanDatabase::close();
                assert('!RedBeanDatabase::isSetup()');
            }
        }
    }
?>