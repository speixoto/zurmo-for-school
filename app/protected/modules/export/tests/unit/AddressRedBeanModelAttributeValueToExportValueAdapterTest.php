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

    class AddressRedBeanModelAttributeValueToExportValueAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
        }

        public function testGetExportValue()
        {
            $data                                = array();
            $model                               = new ExportTestModelItem();
            $model->lastName                     = 'testLastName';
            $model->string                       = 'testString';
            $model->primaryAddress->city         = 'testCity';
            $model->primaryAddress->country      = 'testCountry';
            $model->primaryAddress->postalCode   = 'testPostalCode';
            $model->primaryAddress->street1      = 'testStreet1';
            $model->primaryAddress->street2      = 'testStreet2';
            $model->primaryAddress->state        = 'testState';
            $this->assertTrue($model->save());

            $adapter     = new AddressRedBeanModelAttributeValueToExportValueAdapter($model, 'primaryAddress');
            $adapter->resolveData($data);
            $compareData = array('testCity',
                                 'testCountry',
                                 'testPostalCode',
                                 'testStreet1',
                                 'testStreet2',
                                 'testState'
                                 );
            $this->assertEquals($compareData, $data);
            $data = array();
            $adapter->resolveHeaderData($data);
            $compareData = array($model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'City'),
                                 $model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'Country'),
                                 $model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'Postal Code'),
                                 $model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'Street 1'),
                                 $model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'Street 2'),
                                 $model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'State')
                                );
            $this->assertEquals($compareData, $data);
            $data        = array();
            $model       = new ExportTestModelItem();
            $adapter     = new AddressRedBeanModelAttributeValueToExportValueAdapter($model, 'primaryAddress');
            $adapter->resolveData($data);
            $compareData = array(null,
                                 null,
                                 null,
                                 null,
                                 null,
                                 null);
            $this->assertEquals($compareData, $data);
            $data        = array();
            $adapter->resolveHeaderData($data);
            $compareData = array($model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'City'),
                                 $model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'Country'),
                                 $model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'Postal Code'),
                                 $model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'Street 1'),
                                 $model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'Street 2'),
                                 $model->getAttributeLabel('primaryAddress') . ' - ' . Zurmo::t('ExportModule', 'State')
                                );
            $this->assertEquals($compareData, $data);
        }
    }
?>
