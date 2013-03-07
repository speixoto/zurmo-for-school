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

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            
            $this->runControllerWithNoExceptionsAndGetContent      ('reports/default/list');
            $this->runControllerWithExitExceptionAndGetContent     ('reports/default/create');
            $this->runControllerWithNoExceptionsAndGetContent      ('reports/default/selectType');                        
            //actionList
            //actionCreate
            //actionSelectList
            //actionEdit
            //actionSave

            //test creating a report via walkthrough that has all the component parts.
            //test for all 3 report types

            //test actionDelete
        }
        
        public function testCreateAction()
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
            $this->resetPostArray(array(
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
                                        'ownerId' => '1',
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
            $this->runControllerWithNoExceptionsAndGetContent     ('reports/default/save');
        }
        
        /*
        * @depends on testCreateAction()
        */
        public function testExportActionForAsynchronous()
        {
          $super                    = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
          $savedReport              = SavedReport::getByName('DJTCD');          
          $savedReport              = SavedReport::getById((int)$savedReport->id);
          $report                   = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
          $stickySearchKey          = null;
          $dataProvider             = $this->getDataProviderForExport($report,$stickySearchKey,false);            
          $totalItems               = intval($dataProvider->calculateTotalItemCount());  
          if($totalItems > ExportModule::$asynchronusThreshold)
          {
            $this->setGetArray(array('id' => $savedReport->id));
            $this->resetPostArray();
            $content = $this->runControllerWithRedirectExceptionAndGetContent     ('reports/default/export');
            $this->assertEquals('A large amount of data has been requested for export.  You will receive ' .
                        'a notification with the download link when the export is complete.', Yii::app()->user->getFlash('notification'));
          }
          else
          {
            $this->markTestSkipped(Zurmo::t('ZurmoModule', 'Since data is not so huge normal export will work'));
          }
        }

        //actionRelationsAndAttributesTree - for different tree types and different report types

        //actionAddAttributeFromTree - all various attribute types

        //todo: test regular user and elevations for all actions not just on reports right, but on the base module for the report itself.

        //todO: list security elevated and not, also where the user is nobody and can't see any of the modules but has access to reports

        public function testChartWithTooManyGroupsToRender()
        {
            //todo: call setMaximumGroupsPerChart(2) and then run a report chart with more than 2. then it should render the chart warning
        }
        //todo: test saving a report and changing owner so you don't have permissions anymore. it should do a flashbar and redirect you to
        //the list view.
        //todo: test async export

        //todo: test contorller filters are working

        //todo: that the initial query thing works for filtering out modules you don’t have access to always.

        //todo: test details view comes up ok when user cant delete or edit report, make sure options button doesnt blow up since it shouldnt display

    }
?>