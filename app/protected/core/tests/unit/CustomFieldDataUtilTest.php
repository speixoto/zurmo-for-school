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

    class CustomFieldDataUtilTest extends BaseTest
    {
        public static $activateDefaultLanguages = true;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testSetAndGetTranslatedLabels()
        {
            $this->assertEquals('en', Yii::app()->language);
            $values = array(
                'Item 1',
                'Item 2',
                'Item 3',
            );
            $labels = array(
                'fr' => array('Item 1 fr',
                              'Item 2 fr',
                              'Item 3 fr'),
            );
            $customFieldData = CustomFieldData::getByName('Items');
            $customFieldData->serializedData   = serialize($values);
            $customFieldData->serializedLabels = serialize($labels);
            $this->assertTrue($customFieldData->save());
            $id = $customFieldData->id;
            $customFieldData->forget();
            unset($customFieldData);

            $customFieldData = CustomFieldData::getById($id);
            $dataAndLabels    = CustomFieldDataUtil::
                                getDataIndexedByDataAndTranslatedLabelsByLanguage($customFieldData, 'en');
            $compareData      = array('Item 1' => 'Item 1', 'Item 2' => 'Item 2', 'Item 3' => 'Item 3');
            $this->assertEquals($compareData, $dataAndLabels);

            $dataAndLabels    = CustomFieldDataUtil::
                                getDataIndexedByDataAndTranslatedLabelsByLanguage($customFieldData, 'fr');
            $compareData      = array('Item 1' => 'Item 1 fr', 'Item 2' => 'Item 2 fr', 'Item 3' => 'Item 3 fr');
            $this->assertEquals($compareData, $dataAndLabels);

            $dataAndLabels    = CustomFieldDataUtil::
                                getDataIndexedByDataAndTranslatedLabelsByLanguage($customFieldData, 'de');
            $compareData      = array('Item 1' => 'Item 1', 'Item 2' => 'Item 2', 'Item 3' => 'Item 3');
            $this->assertEquals($compareData, $dataAndLabels);
        }

        public function testSetAndGetEmptyValueWhichShouldUtilizeFallBack()
        {
            $this->assertEquals('en', Yii::app()->language);
            $values = array(
                'Item 1',
                'Item 2',
                'Item 3',
            );
            $labels = array(
                'fr' => array('Item 1 fr',
                              '',
                              'Item 3 fr'),
            );
            $customFieldData = CustomFieldData::getByName('Items2');
            $customFieldData->serializedData   = serialize($values);
            $customFieldData->serializedLabels = serialize($labels);
            $this->assertTrue($customFieldData->save());
            $id = $customFieldData->id;
            $customFieldData->forget();
            unset($customFieldData);

            $customFieldData = CustomFieldData::getById($id);
            $dataAndLabels    = CustomFieldDataUtil::
                                getDataIndexedByDataAndTranslatedLabelsByLanguage($customFieldData, 'en');
            $compareData      = array('Item 1' => 'Item 1', 'Item 2' => 'Item 2', 'Item 3' => 'Item 3');
            $this->assertEquals($compareData, $dataAndLabels);

            $dataAndLabels    = CustomFieldDataUtil::
                                getDataIndexedByDataAndTranslatedLabelsByLanguage($customFieldData, 'fr');
            $compareData      = array('Item 1' => 'Item 1 fr', 'Item 2' => 'Item 2', 'Item 3' => 'Item 3 fr');
            $this->assertEquals($compareData, $dataAndLabels);
        }

        public function testGetTranslatedLabelByValue()
        {
            $this->assertEquals('en', Yii::app()->language);
            $values = array(
                'Item 1',
                'Item 2',
                'Item 3',
            );
            $labels = array(
                'fr' => array('Item 1 fr',
                              '',
                              'Item 3 fr'),
            );
            $customFieldData = CustomFieldData::getByName('Items2');
            $customFieldData->serializedData   = serialize($values);
            $customFieldData->serializedLabels = serialize($labels);
            $this->assertTrue($customFieldData->save());
            $id = $customFieldData->id;
            $customFieldData->forget();
            unset($customFieldData);

            $customFieldData = CustomFieldData::getById($id);
            $value           = CustomFieldDataUtil::getTranslatedLabelByValue($customFieldData, 'Item 1', 'en');
            $this->assertEquals('Item 1', $value);
            $value           = CustomFieldDataUtil::getTranslatedLabelByValue($customFieldData, 'Item 1', 'fr');
            $this->assertEquals('Item 1 fr', $value);
        }
    }
?>
