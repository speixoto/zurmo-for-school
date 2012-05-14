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

    class ExportPrivilegesTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();

            //Create some extra users
            SecurityTestHelper::createUsers();
            $nobody    = UserTestHelper::createBasicUser('nobody');
        }

        public function testSearch()
        {
            $super = User::getByUsername('super');
            $nobody = User::getByUsername('nobody');

            Yii::app()->user->userModel = $super;
            $accounts = array();
            for ($i = 0; $i < 2; $i++)
            {
                $accounts[] = AccountTestHelper::createAccountByNameForOwner('superAccount' . $i, $super);
            }


            // St rights for user nobody
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_DELETE_ACCOUNTS);
            $this->assertTrue($nobody->save());

            // Add permissions for nobody
            foreach ($accounts as $account)
            {
                $account->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
                $this->assertTrue($account->save());
            }

            Yii::app()->user->userModel = $nobody;

            $_GET = array(
                'AccountsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 =>'All'),
                    'anyMixedAttributes'      => '',
                    'name'                    => '',
                    'officePhone'             => ''
                ),
                'multiselect_AccountsSearchForm_anyMixedAttributesScope' => 'All',
                'selectAll' => '1',
                'selectedIds' => '',
                'Account_page'   => '1',
                'export'         => '',
                'ajax'           => ''
            );

            $modelClassName        = 'Account';
            $searchFormClassName   = 'AccountsSearchForm';
            $filteredListClassName = 'AccountsFilteredList';

            $pageSize = 0;
            $model = new $modelClassName(false);

            if (isset($searchFormClassName))
            {
                $searchForm = new $searchFormClassName($model);
            }
            else
            {
                $searchForm = null;
            }
            $stateMetadataAdapterClassName = null;

            $searchModel = $searchForm;
            $listModelClassName = $modelClassName;

            $searchAttributes          = SearchUtil::resolveSearchAttributesFromGetArray(get_class($searchModel));
            SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromGetArray($searchModel, get_class($searchModel));
            $sanitizedSearchAttributes = GetUtil::sanitizePostByDesignerTypeForSavingModel($searchModel,
                                                                                           $searchAttributes);
            $sortAttribute             = SearchUtil::resolveSortAttributeFromGetArray($listModelClassName);
            $sortDescending            = SearchUtil::resolveSortDescendingFromGetArray($listModelClassName);

            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchModel,
                Yii::app()->user->userModel->id,
                $sanitizedSearchAttributes
            );

            $dataProvider =  RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter,
                $modelClassName,
                'RedBeanModelDataProvider',
                 $sortAttribute,
                $sortDescending,
                $pageSize,
                null
            );

            $totalItems = intval($dataProvider->calculateTotalItemCount());
            $this->assertEquals(2, $totalItems);
        }
    }
?>