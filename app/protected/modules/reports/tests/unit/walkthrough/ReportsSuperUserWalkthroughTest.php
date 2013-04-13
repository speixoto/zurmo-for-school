<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Reports module walkthrough tests for super users.
     */
    class ReportsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
        }

        public function setUp()
        {
            parent::setUp();
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $this->runControllerWithNoExceptionsAndGetContent      ('reports/default/list');
            $this->runControllerWithExitExceptionAndGetContent     ('reports/default/create');
            $this->runControllerWithNoExceptionsAndGetContent      ('reports/default/selectType');
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testCreateActionForRowsAndColumns()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $content = $this->runControllerWithExitExceptionAndGetContent     ('reports/default/create');
            $this->assertFalse(strpos($content, 'Rows and Columns Report') === false);
            $this->assertFalse(strpos($content, 'Summation Report') === false);
            $this->assertFalse(strpos($content, 'Matrix Report') === false);

            $this->setGetArray(array('type' => 'RowsAndColumns'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent     ('reports/default/create');
            $this->assertFalse(strpos($content, 'Accounts') === false);

            $this->setGetArray(array('type' => 'RowsAndColumns'));
            $this->setPostArray(array(
                                    'validationScenario' => 'ValidateForDisplayAttributes',
                                    'RowsAndColumnsReportWizardForm' => array(
                                        'moduleClassName' => 'AccountsModule',
                                        'Filters' => array(
                                                        '0' => array(
                                                               'structurePosition' => 1,
                                                               'attributeIndexOrDerivedType' => 'name',
                                                               'operator' => 'isNotNull',
                                                               'value' => '',
                                                               'availableAtRunTime' => '0')),
                                        'filtersStructure' => '1',
                                        'displayAttributes' => '',
                                        'DisplayAttributes' => array(
                                                                '0' => array(
                                                                        'attributeIndexOrDerivedType' => 'name',
                                                                        'label' => 'Name')),

                                        'name' => 'DJTCD',
                                        'description' => 'DJTCD',
                                        'currencyConversionType' => '1',
                                        'spotConversionCurrencyCode' => '',
                                        'ownerId' => Yii::app()->user->userModel->id,
                                        'ownerName' => 'Super User',
                                        'explicitReadWriteModelPermissions' => array(
                                                                               'type' => '',
                                                                               'nonEveryoneGroup' => '4')),
                                        'FiltersRowCounter' => '1',
                                        'DisplayAttributesRowCounter' => '1',
                                        'OrderBysRowCounter' => '0',
                                        'save' => 'save',
                                        'ajax' => 'edit-form'
                                    ));
            $content = $this->runControllerWithExitExceptionAndGetContent     ('reports/default/save');
            echo $content;
            exit;
            //todo: confirm validated, then continue with save
        }

        /*
        * @depends on testCreateActionForRowsAndColumns
        */
        public function testExportActionForAsynchronous()
        {
          $super                    = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
          $savedReports              = SavedReport::getAll();
          $this->assertEquals(1, count($savedReports));
          $savedReport              = $savedReports[0];
          $report                   = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
          $stickySearchKey          = null;
          $dataProvider             = $this->getDataProviderForExport($report,$stickySearchKey,false);
          $totalItems               = intval($dataProvider->calculateTotalItemCount());
          echo 'test' . $totalItems;
          exit;
        }

        /**
         * @depends testExportActionForAsynchronous
         */
        public function testActionRelationsAndAttributesTree()
        {
            //todo: actionRelationsAndAttributesTree($type, $treeType, $id = null, $nodeId = null)
        }

        /**
         * @depends testActionRelationsAndAttributesTree
         */
        public function testActionAddAttributeFromTree()
        {
            //todo: actionAddAttributeFromTree($type, $treeType, $nodeId, $rowNumber, $trackableStructurePosition = false, $id = null)
        }

        /**
         * @depends testActionAddAttributeFromTree
         */
        public function testGetAvailableSeriesAndRangesForChart()
        {
            //todo: actionGetAvailableSeriesAndRangesForChart($type, $id = null)
        }


        //todo: ApplyRuntimeFilters($id) actionApplyRuntimeFilters($id)
        //todo: ResetRuntimeFilters($id) actionResetRuntimeFilters($id)

        //todo: actionDelete

        //todo: actionDrillDownDetails($id, $rowId)

        //todo: actionAutoComplete($term, $moduleClassName, $type)


        //todo: test saving a report and changing owner so you don't have permissions anymore. it should do a flashbar and redirect you to the list view.
        //todo: test details view comes up ok when user cant delete or edit report, make sure options button doesn't blow up since it shouldn't display
    }
?>