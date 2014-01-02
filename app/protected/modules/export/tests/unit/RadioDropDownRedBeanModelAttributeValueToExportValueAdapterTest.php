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

    class RadioDropDownRedBeanModelAttributeValueToExportValueAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
        }

        public static function getDependentTestModelClassNames()
        {
            return array('ExportTestModelItem');
        }

        public function testGetExportValue()
        {
            $values = array(
                'Test1',
                'Test2',
                'Test3',
                'Sample',
                'Demo',
            );
            $customFieldData = CustomFieldData::getByName('ExportTestRadioDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            $this->assertTrue($saved);

            $data = array();
            $model = new ExportTestModelItem();
            $model->radioDropDown->value = $values[1];
            $model->lastName = "Smith";
            $model->string = "Some Test String";
            // We have to save model, to get correct currencyValue id.
            $this->assertTrue($model->save());

            $adapter = new RadioDropDownRedBeanModelAttributeValueToExportValueAdapter($model, 'radioDropDown');
            $adapter->resolveData($data);
            $compareData = array('Test2');
            $this->assertEquals($compareData, $data);
            $data = array();
            $adapter->resolveHeaderData($data);
            $compareData = array($model->getAttributeLabel('radioDropDown'));
            $this->assertEquals($compareData, $data);

            $data = array();
            $model = new ExportTestModelItem();
            $adapter = new RadioDropDownRedBeanModelAttributeValueToExportValueAdapter($model, 'radioDropDown');
            $adapter->resolveData($data);
            $compareData = array('');
            $this->assertEquals($compareData, $data);
            $data = array();
            $adapter->resolveHeaderData($data);
            $compareData = array($model->getAttributeLabel('radioDropDown'));
            $this->assertEquals($compareData, $data);
        }
    }
?>