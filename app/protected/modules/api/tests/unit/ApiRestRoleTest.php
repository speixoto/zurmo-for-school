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
    * Test Role related API functions.
    */
    class ApiRestRoleTest extends ApiRestTest
    {
        public function testApiServerUrl()
        {
            if (!$this->isApiTestUrlConfigured())
            {
                $this->markTestSkipped(Yii::t('Default', 'API test url is not configured in perInstanceTest.php file.'));
            }
            $this->assertTrue(strlen($this->serverUrl) > 0);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testGetRole()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel        = $super;
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $role = new Role();
            $role->name = 'myRole';
            $role->validate();
            $saved = $role->save();
            $this->assertTrue($saved);

            $roles                 = Role::getAll();
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($roles[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/zurmo/role/api/read/' . $compareData['id'], 'GET', $headers);
            $response = json_decode($response, true);

            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
        * @depends testGetRole
        */
        public function testListRoles()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $roles                 = Role::getAll();
            $compareData = array();
            foreach ($roles as $role)
            {
                $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($role);
                $compareData[] = $redBeanModelToApiDataUtil->getData();
            }

            //Test List
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/zurmo/role/api/list/', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(count($roles), count($response['data']['items']));
            $this->assertEquals(count($roles), $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals($compareData, $response['data']['items']);
        }
    }
?>