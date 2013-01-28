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

    $cwd = getcwd();

    require_once('../PhpUnitServiceUtil.php');
    require_once('../testRoots.php');
    require_once('../bootstrap.php');

    $freeze = true; // TODO - figure out the correct was to pass information like this into tests.

    class TestSuite
    {
        public static function suite()
        {
            global $argv, $freeze;

            PhpUnitServiceUtil::checkVersion();
            $usage = "\n"                                                                                                    .
                     "  Usage: phpunit [phpunit options] TestSuite.php <All|Framework|Misc|moduleName|TestClassName> [custom options]\n" .
                     "\n"                                                                                                    .
                     "    All                     Run all tests.\n"                                                           .
                     "    Framework               Run the tests in app/protected/extensions/framework/tests/unit.\n"          .
                     "    Misc                    Run the tests in app/protected/tests/unit.\n"                               .
                     "    moduleName              Run the tests in app/protected/modules/moduleName/tests/unit.\n"            .
                     "    TestClassName           Run the tests in TestClassName.php, wherever that happens to be.\n"         .
                     "\n"                                                                                                    .
                     "  Custom Options:\n"                                                                                   .
                     "\n"                                                                                                    .
                     "    --only-walkthroughs     For the specified test, only includes tests under a walkthroughs directory.\n" .
                     "    --exclude-walkthroughs  For the specified test, exclude tests under a walkthroughs directory.\n"       .
                     "    --only-benchmarks       For the specified test, only includes tests under a benchmarks directory.\n" .
                     "    --exclude-benchmarks    For the specified test, exclude tests under a benchmarks directory.\n"      .
                     "    --reuse-schema          Reload a previously auto build database. (Will auto build if there is no\n" .
                     "                            previous one. The auto built schema is dumped to the system temp dir in\n"  .
                     "                            autobuild.sql.)\n"                                                          .
                     "    --no-freeze             Don't auto build and freeze the database.\n"                                .
                     "\n"                                                                                                    .
                     "  Examples:\n"                                                                                         .
                     "\n"                                                                                                    .
                     "    phpunit --verbose TestSuite.php accounts (Run the tests in the Accounts module.)\n"                . // Not Coding Standard
                     "    phpunit TestSuite.php RedBeanModelTest   (Run the tests in RedBeanModelTest.php.)\n"               .
                     "\n"                                                                                                    .
                     "  Note:\n"                                                                                             .
                     "\n"                                                                                                    .
                     "    Framework and Misc tests run only when -no-freeze is specified.\n"                                 .
                     "\n"                                                                                                    .
                     "    To run specific tests use the phpunit --filter <regex> option.\n"                                  . // Not Coding Standard
                     "    phpunit has its own options. Check phpunit --help.\n\n";                                             // Not Coding Standard

            $onlyWalkthroughs     =  self::customOptionSet('--only-walkthroughs',     $argv);
            $excludeWalkthroughs  =  self::customOptionSet('--exclude-walkthroughs',  $argv);
            $onlyBenchmarks       =  self::customOptionSet('--only-benchmarks',       $argv);
            $excludeBenchmarks    =  self::customOptionSet('--exclude-benchmarks',    $argv);
            $reuse                =  self::customOptionSet('--reuse-schema',          $argv);
            $freeze               = !self::customOptionSet('--no-freeze',             $argv);

            if ($argv[count($argv) - 2] != 'TestSuite.php')
            {
                echo $usage;
                exit;
            }

            if ($onlyWalkthroughs && $onlyBenchmarks)
            {
                echo $usage;
                echo "It doesn't have sense to select both \"--only-walkthroughs\" and \"--only-benchmarks\" options. \n\n";
                exit;
            }

            $whatToTest           = $argv[count($argv) - 1];
            $includeUnitTests     = !$onlyWalkthroughs && !$onlyBenchmarks;
            $includeWalkthroughs  = !$excludeWalkthroughs && !$onlyBenchmarks;
            $includeBenchmarks    = !$excludeBenchmarks && !$onlyWalkthroughs;

            echo "Testing with database: '"  . Yii::app()->db->connectionString . '\', ' .
                              'username: \'' . Yii::app()->db->username         . "'.\n";

            if ($freeze && !$reuse)
            {
                InstallUtil::connectToDatabaseWithConnectionString(Yii::app()->db->connectionString,
                                                                   Yii::app()->db->username,
                                                                   Yii::app()->db->password);
                echo "Auto building database schema...\n";
                InstallUtil::dropAllTables();
                Yii::app()->user->userModel = InstallUtil::createSuperUser('super', 'super');
                $messageLogger = new MessageLogger();
                InstallUtil::autoBuildDatabase($messageLogger);
                $messageLogger->printMessages();
                ReadPermissionsOptimizationUtil::rebuild();
                assert('RedBeanDatabase::isSetup()');

                echo "Saving auto built schema...\n";
                $schemaFile = sys_get_temp_dir() . '/autobuilt.sql';
                $success = preg_match("/;dbname=([^;]+)/", Yii::app()->db->connectionString, $matches); // Not Coding Standard
                assert('$success == 1');
                $databaseName = $matches[1];
                system('mysqldump -u' . Yii::app()->db->username .
                                ' -p' . Yii::app()->db->password .
                                  ' ' . $databaseName            .
                       " > $schemaFile");

                InstallUtil::close();
                echo "Database closed.\n";
                assert('!RedBeanDatabase::isSetup()');
            }

            $suite = new PHPUnit_Framework_TestSuite();
            $suite->setName("$whatToTest Tests");
            if (!$freeze)
            {
                self::buildAndAddSuiteFromDirectory($suite, 'Framework', COMMON_ROOT . '/protected/core/tests/unit', $whatToTest, true, false, $includeBenchmarks);
            }
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
            if (!$freeze)
            {
                self::buildAndAddSuiteFromDirectory($suite, 'Misc',            COMMON_ROOT . '/protected/tests/unit',                     $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks);
////////////////////////////////////////////////////////////////////////////////
// Temporary - See Readme.txt in the notSupposedToBeHere directory.
                self::buildAndAddSuiteFromDirectory($suite, 'BadDependencies', COMMON_ROOT . '/protected/tests/unit/notSupposedToBeHere', $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks);
////////////////////////////////////////////////////////////////////////////////
            }
            else
            {
                self::buildAndAddSuiteFromDirectory($suite, 'Commands',        COMMON_ROOT . '/protected/commands/tests/unit',             $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks);
            }
            if ($suite->count() == 0)
            {
                echo $usage;
                echo "  No tests found for '$whatToTest'.\n\n";
                exit;
            }
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
    }
?>
