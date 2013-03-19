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

    class ZurmoConfigurationFormAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('sally');
        }

        public function testMakeFormAndSetConfigurationFromForm()
        {
            Yii::app()->timeZoneHelper->setTimeZone           ('America/New_York');
            Yii::app()->pagination->setGlobalValueByType('listPageSize',          50);
            Yii::app()->pagination->setGlobalValueByType('subListPageSize',       51);
            Yii::app()->pagination->setGlobalValueByType('modalListPageSize',     52);
            Yii::app()->pagination->setGlobalValueByType('dashboardListPageSize', 53);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'applicationName', 'demoCompany');
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoWidth', 120);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoHeight', 40);
            $logoFileName = 'testImage.png';
            $logoFilePath = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files') . DIRECTORY_SEPARATOR . $logoFileName;
            ZurmoConfigurationFormAdapter::resizeLogoImageFile($logoFilePath, $logoFilePath, 120, 40);
            $logoFileId   = ZurmoConfigurationFormAdapter::saveLogoFile($logoFileName, $logoFilePath, 'logoFileModelId');
            ZurmoConfigurationFormAdapter::publishLogo($logoFileName, $logoFilePath);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoFileModelId', $logoFileId);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoThumbFileModelId', $logoFileId);
            $form = ZurmoConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $this->assertEquals('America/New_York', $form->timeZone);
            $this->assertEquals(50,                 $form->listPageSize);
            $this->assertEquals(51,                 $form->subListPageSize);
            $this->assertEquals(52,                 $form->modalListPageSize);
            $this->assertEquals(53,                 $form->dashboardListPageSize);
            $this->assertEquals('demoCompany',      $form->applicationName);
            $this->assertEquals(120,                $form->logoWidth);
            $this->assertEquals(40,                 $form->logoHeight);
            $this->assertEquals($logoFileName,      $form->logoFileData['name']);
            $form->timeZone              = 'America/Chicago';
            $form->listPageSize          = 60;
            $form->subListPageSize       = 61;
            $form->modalListPageSize     = 62;
            $form->dashboardListPageSize = 63;
            $form->applicationName       = 'demoCompany2';
            $form->logoWidth             = 107;
            $form->logoHeight            = 32;
            $logoFileName2               = 'testLogo.png';
            $logoFilePath2               = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files') . DIRECTORY_SEPARATOR . $logoFileName2;
            copy($logoFilePath2, sys_get_temp_dir().DIRECTORY_SEPARATOR . $logoFileName2);
            copy($logoFilePath2, sys_get_temp_dir().DIRECTORY_SEPARATOR . ZurmoConfigurationForm::LOGO_THUMB_FILE_NAME_PREFIX.$logoFileName2);
            Yii::app()->user->setState('logoFileName', $logoFileName2);
            ZurmoConfigurationFormAdapter::setConfigurationFromForm($form);
            $form = ZurmoConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $this->assertEquals('America/Chicago',  $form->timeZone);
            $this->assertEquals(60,                 $form->listPageSize);
            $this->assertEquals(61,                 $form->subListPageSize);
            $this->assertEquals(62,                 $form->modalListPageSize);
            $this->assertEquals(63,                 $form->dashboardListPageSize);
            $this->assertEquals('demoCompany2',     $form->applicationName);
            $this->assertEquals(107,                $form->logoWidth);
            $this->assertEquals(32,                 $form->logoHeight);
            $this->assertEquals($logoFileName2,     $form->logoFileData['name']);
        }
    }
?>