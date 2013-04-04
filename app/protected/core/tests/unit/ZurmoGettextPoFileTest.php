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
     * Test class to test the ZurmoGettextPoFile class.
     */
    class ZurmoGettextPoFileTest extends BaseTest
    {
        protected static $poFileName = 'messages-test.po';

        protected static $compareArray = array(
            'PO file Testmessage. Category: 1. Type: source 1' => array(
                'msgctxt' => 'TestCategory1',
                'msgid' => 'PO file Testmessage. Category: 1. Type: source 1',
                'msgstr' => 'PO file Testmessage. Category: 1. Type: translation 1'
            ),
            'PO file Testmessage. Category: 1. Type: source 2' => array(
                'msgctxt' => 'TestCategory1',
                'msgid' => 'PO file Testmessage. Category: 1. Type: source 2',
                'msgstr' => 'PO file Testmessage. Category: 1. Type: translation 2'
            ),
            'PO file Testmessage. Category: 1. Type: source 3' => array(
                'msgctxt' => 'TestCategory1',
                'msgid' => 'PO file Testmessage. Category: 1. Type: source 3',
                'msgstr' => 'PO file Testmessage. Category: 1. Type: translation 3'
            ),
            'PO file Testmessage. Category: 1. Type: source 4' => array(
                'msgctxt' => 'TestCategory1',
                'msgid' => 'PO file Testmessage. Category: 1. Type: source 4',
                'msgstr' => 'PO file Testmessage. Category: 1. Type: translation 4'
            ),
            'PO file Testmessage. Category: 1. Type: source 5' => array(
                'msgctxt' => 'TestCategory1',
                'msgid' => 'PO file Testmessage. Category: 1. Type: source 5',
                'msgstr' => 'PO file Testmessage. Category: 1. Type: translation 5'
            ),
            'PO file Testmessage. Category: 2. Type: source 1' => array(
                'msgctxt' => 'TestCategory2',
                'msgid' => 'PO file Testmessage. Category: 2. Type: source 1',
                'msgstr' => 'PO file Testmessage. Category: 2. Type: translation 1'
            ),
            'PO file Testmessage. Category: 2. Type: source 2' => array(
                'msgctxt' => 'TestCategory2',
                'msgid' => 'PO file Testmessage. Category: 2. Type: source 2',
                'msgstr' => 'PO file Testmessage. Category: 2. Type: translation 2'
            ),
            'PO file Testmessage. Category: 2. Type: source 3' => array(
                'msgctxt' => 'TestCategory2',
                'msgid' => 'PO file Testmessage. Category: 2. Type: source 3',
                'msgstr' => 'PO file Testmessage. Category: 2. Type: translation 3'
            ),
            'PO file Testmessage. Category: 2. Type: source 4' => array(
                'msgctxt' => 'TestCategory2',
                'msgid' => 'PO file Testmessage. Category: 2. Type: source 4',
                'msgstr' => 'PO file Testmessage. Category: 2. Type: translation 4'
            ),
            'PO file Testmessage. Category: 2. Type: source 5' => array(
                'msgctxt' => 'TestCategory2',
                'msgid' => 'PO file Testmessage. Category: 2. Type: source 5',
                'msgstr' => 'PO file Testmessage. Category: 2. Type: translation 5'
            ),
            'PO file Testmessage. Category: 3. Type: source 1' => array(
                'msgctxt' => 'TestCategory3',
                'msgid' => 'PO file Testmessage. Category: 3. Type: source 1',
                'msgstr' => 'PO file Testmessage. Category: 3. Type: translation 1'
            ),
            'PO file Testmessage. Category: 3. Type: source 2' => array(
                'msgctxt' => 'TestCategory3',
                'msgid' => 'PO file Testmessage. Category: 3. Type: source 2',
                'msgstr' => 'PO file Testmessage. Category: 3. Type: translation 2'
            ),
            'PO file Testmessage. Category: 3. Type: source 3' => array(
                'msgctxt' => 'TestCategory3',
                'msgid' => 'PO file Testmessage. Category: 3. Type: source 3',
                'msgstr' => 'PO file Testmessage. Category: 3. Type: translation 3'
            ),
            'PO file Testmessage. Category: 3. Type: source 4' => array(
                'msgctxt' => 'TestCategory3',
                'msgid' => 'PO file Testmessage. Category: 3. Type: source 4',
                'msgstr' => 'PO file Testmessage. Category: 3. Type: translation 4'
            ),
            'PO file Testmessage. Category: 3. Type: source 5' => array(
                'msgctxt' => 'TestCategory3',
                'msgid' => 'PO file Testmessage. Category: 3. Type: source 5',
                'msgstr' => 'PO file Testmessage. Category: 3. Type: translation 5'
            )
        );

        protected static function getFilePath($fileName)
        {
            $pathToFiles = Yii::getPathOfAlias('application.tests.unit.files');

            return $pathToFiles . DIRECTORY_SEPARATOR . $fileName;
        }

        protected static function getCompareArray()
        {
            $array =  self::$compareArray;

            return $array;
        }

        public function testRead()
        {
            $filePath = self::getFilePath(self::$poFileName);
            $compareArray = self::getCompareArray();

            $poFile = new ZurmoGettextPoFile($filePath);
            $contextArray = $poFile->read();
            $this->assertEquals(
                                md5(json_encode($compareArray)),
                                md5(json_encode($contextArray))
                                );
        }
    }
?>
