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
    * Test Note related API functions.
    */
    class ApiRestNoteTest extends ApiRestTest
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
        public function testGetNote()
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

            $note = NoteTestHelper::createNoteByNameForOwner('First Note', $super);
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($note);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/read/' . $note->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testGetNote
         */
        public function testDeleteNote()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $notes = Note::getByName('First Note');
            $this->assertEquals(1, count($notes));

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/delete/' . $notes[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/read/' . $notes[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('The ID specified was invalid.', $response['message']);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testCreateNote()
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

            $occurredOnStamp          = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $data['description']    = "Note description";
            $data['occurredOnDateTime'] = $occurredOnStamp;

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $data['owner'] = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['createdByUser']    = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['modifiedByUser'] = array(
                'id' => $super->id,
                'username' => 'super'
            );

            // We need to unset some empty values from response.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['id']);
            $data['latestDateTime'] = $occurredOnStamp;

            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);
        }

        /**
         * @depends testCreateNote
         */
        public function testUpdateNote()
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

            $notes = Note::getByName('Note description');
            $this->assertEquals(1, count($notes));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($notes[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();
            $notes[0]->forget();

            $updateData['description']    = "Updated note description";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/update/' . $compareData['id'], 'PUT', $headers, array('data' => $updateData));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $compareData['description'] = "Updated note description";
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/read/' . $notes[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            unset($response['data']['modifiedDateTime']);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testUpdateNote
         */
        public function testListNotes()
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

            $notes = Note::getByName('Updated note description');
            $this->assertEquals(1, count($notes));

            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($notes[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals(array($compareData), $response['data']['items']);
        }

        /**
         * @depends testListNotes
         */
        public function testUnprivilegedUserViewUpdateDeleteNotes()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $notAllowedUser = UserTestHelper::createBasicUser('Steven');
            $notAllowedUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $saved = $notAllowedUser->save();

            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertTrue($everyoneGroup->save());

            $notes = Note::getByName('Updated note description');
            $this->assertEquals(1, count($notes));
            $data['description']    = "Updated note description";

            // Test with unprivileged user to view, edit and delete account.
            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/read/' . $notes[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/update/' . $notes[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/delete/' . $notes[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            //now check if user have rights, but no permissions.
            $notAllowedUser->setRight('NotesModule', NotesModule::getAccessRight());
            $notAllowedUser->setRight('NotesModule', NotesModule::getCreateRight());
            $notAllowedUser->setRight('NotesModule', NotesModule::getDeleteRight());
            $saved = $notAllowedUser->save();
            $this->assertTrue($saved);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/read/' . $notes[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/update/' . $notes[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/delete/' . $notes[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            // Allow everyone group to read/write note
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            unset($data);
            $data['explicitReadWriteModelPermissions'] = array(
                'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/update/' . $notes[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/read/' . $notes[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            unset($data);
            $data['description']    = "Updated note description 2";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/update/' . $notes[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals("Updated note description 2", $response['data']['description']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/delete/' . $notes[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            // Test with privileged user
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            //Test Delete
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/delete/' . $notes[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/read/' . $notes[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        /**
        * @depends testUnprivilegedUserViewUpdateDeleteNotes
        */
        public function testSearchNotes()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $anotherUser = User::getByUsername('steven');

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $firstAccount = AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('First Account', 'Customer', 'Automotive', $super);
            $secondAccount = AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Second Account', 'Customer', 'Automotive', $super);

            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('First Note', $super, $firstAccount);
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('Second Note', $super, $firstAccount);
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('Third Note', $super, $secondAccount);
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('Forth Note', $anotherUser, $secondAccount);
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('Fifth Note', $super, $firstAccount);

            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'description' => '',
                ),
                'sort' => 'description',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Fifth Note', $response['data']['items'][0]['description']);
            $this->assertEquals('First Note', $response['data']['items'][1]['description']);
            $this->assertEquals('Forth Note', $response['data']['items'][2]['description']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Note', $response['data']['items'][0]['description']);
            $this->assertEquals('Third Note', $response['data']['items'][1]['description']);

            // Search by name
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['description'] = 'First Note';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('First Note', $response['data']['items'][0]['description']);

            // No results
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['description'] = 'First Note 2';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(0, $response['data']['totalCount']);
            $this->assertFalse(isset($response['data']['items']));

            // Search by name desc.
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'description' => '',
                ),
                'sort' => 'description.desc',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Third Note', $response['data']['items'][0]['description']);
            $this->assertEquals('Second Note', $response['data']['items'][1]['description']);
            $this->assertEquals('Forth Note', $response['data']['items'][2]['description']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('First Note', $response['data']['items'][0]['description']);
            $this->assertEquals('Fifth Note', $response['data']['items'][1]['description']);

            // Search by owner, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'owner'   => array( 'id' => $super->id),
                ),
                'sort' => 'description.desc',
            );

            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(4, $response['data']['totalCount']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Third Note', $response['data']['items'][0]['description']);
            $this->assertEquals('Second Note', $response['data']['items'][1]['description']);
            $this->assertEquals('First Note', $response['data']['items'][2]['description']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(4, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Fifth Note', $response['data']['items'][0]['description']);

            // Search by account, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'activityItems'   => array('id' => $firstAccount->getClassId('Item')),
                ),
                'sort' => 'description.desc',
            );

            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Second Note', $response['data']['items'][0]['description']);
            $this->assertEquals('First Note', $response['data']['items'][1]['description']);
            $this->assertEquals('Fifth Note', $response['data']['items'][2]['description']);
        }

    /**
        * @depends testSearchNotes
        */
        public function testAdvancedSearchNotes()
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

            $data = array(
                'dynamicSearch' => array(
                    'dynamicClauses' => array(
                        array(
                            'attributeIndexOrDerivedType' => 'owner',
                            'structurePosition' => 1,
                            'owner' => array(
                                'id' => Yii::app()->user->userModel->id,
                            ),
                        ),
                        array(
                            'attributeIndexOrDerivedType' => 'name',
                            'structurePosition' => 2,
                            'description' => 'Fi',
                        ),
                        array(
                            'attributeIndexOrDerivedType' => 'name',
                            'structurePosition' => 3,
                            'description' => 'Se',
                        ),
                    ),
                    'dynamicStructure' => '1 AND (2 OR 3)',
                ),
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 2,
                ),
                'sort' => 'description.asc',
           );

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Fifth Note', $response['data']['items'][0]['description']);
            $this->assertEquals('First Note', $response['data']['items'][1]['description']);

            // Get second page
            $data['pagination']['page'] = 2;
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Note', $response['data']['items'][0]['description']);
        }

        /**
        * Test get notes that are related with particular contact(MANY_MANY relationship)
        *
        * @depends testApiServerUrl
        */
        public function testGetNotesThatAreRelatedWithContactModel()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $firstNote = NoteTestHelper::createNoteByNameForOwner('First note with relations', $super);
            $secondNote = NoteTestHelper::createNoteByNameForOwner('Second note with relations', $super);
            $thirdNote = NoteTestHelper::createNoteByNameForOwner('Third note with relations', $super);
            $forthNote = NoteTestHelper::createNoteByNameForOwner('Forth note with relations', $super);

            $firstContact = ContactTestHelper::createContactByNameForOwner('First', $super);
            $secondContact = ContactTestHelper::createContactByNameForOwner('Second', $super);
            $thirdContact = ContactTestHelper::createContactByNameForOwner('Third', $super);

            $firstNote->activityItems->add($firstContact);
            $firstNote->save();
            $firstNote->activityItems->add($secondContact);
            $firstNote->save();
            $thirdNote->activityItems->add($firstContact);
            $thirdNote->save();
            $forthNote->activityItems->add($firstContact);
            $forthNote->save();

            $this->assertEquals(2, count($firstNote->activityItems));
            $this->assertEquals($firstContact->id, $firstNote->activityItems[0]->id);
            $this->assertEquals($secondContact->id, $firstNote->activityItems[1]->id);

            //$id = $firstContact->getClassId('Item');

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $data = array(
                'dynamicSearch' => array(
                    'dynamicClauses' => array(
                        array(
                            'attributeIndexOrDerivedType' => 'activityItems',
                            'structurePosition' => 1,
                            'activityItems' => array(
                                'modelClassName' => 'Contact',
                                'modelId' => $firstContact->id,
                            ),
                        )
                    ),
                    'dynamicStructure' => '1',
                ),
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 2,
                ),
                'sort' => 'description.asc',
           );

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);

            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('First note with relations', $response['data']['items'][0]['description']);
            $this->assertEquals('Forth note with relations', $response['data']['items'][1]['description']);

            // Get second page
            $data['pagination']['page'] = 2;
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/list/filter/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Third note with relations', $response['data']['items'][0]['description']);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testCreateWithRelations()
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

            $contact  = ContactTestHelper::createContactByNameForOwner('Simon', $super);
            $contactItemId = $contact->getClassId('Item');

            $occurredOnStamp          = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $data['description']    = "Note description";
            $data['occurredOnDateTime'] = $occurredOnStamp;

            $data['modelRelations'] = array(
                'activityItems' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $contact->id,
                        'modelClassName' => 'Contact'
                    ),
                ),
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($data['description'], $response['data']['description']);
            $this->assertEquals($data['occurredOnDateTime'], $response['data']['occurredOnDateTime']);

            RedBeanModel::forgetAll();
            $note = Note::getById($response['data']['id']);
            $this->assertEquals(1, count($note->activityItems));
            $this->assertEquals($contactItemId, $note->activityItems[0]->id);
        }

        /**
        * @depends testCreateWithRelations
        */
        public function testUpdateWithRelations()
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

            $contact  = ContactTestHelper::createContactByNameForOwner('Mark', $super);
            $contactItemId = $contact->getClassId('Item');
            $note = NoteTestHelper::createNoteByNameForOwner('Note update test.', $super);

            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($note);
            $compareData  = $redBeanModelToApiDataUtil->getData();
            $this->assertEquals(0, count($note->activityItems));
            $note->forget();

            $data['modelRelations'] = array(
                'activityItems' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $contact->id,
                        'modelClassName' => 'Contact'
                    ),
                ),
            );
            $data['description']    = "Updated note description";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $compareData['description'] = "Updated note description";
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);

            RedBeanModel::forgetAll();
            $note = Note::getById($compareData['id']);
            $this->assertEquals(1, count($note->activityItems));
            $this->assertEquals($contactItemId, $note->activityItems[0]->id);

            // Now test remove relations
            $data['modelRelations'] = array(
                'activityItems' => array(
                    array(
                        'action' => 'remove',
                        'modelId' => $contact->id,
                        'modelClassName' => 'Contact'
                    ),
                ),
            );

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            RedBeanModel::forgetAll();
            $note = Note::getById($compareData['id']);
            $this->assertEquals(0, count($note->activityItems));
        }

        /**
        * @depends testApiServerUrl
        */
        public function testEditNoteWithIncompleteData()
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

            $note = NoteTestHelper::createNoteByNameForOwner('New Note', $super);

            // Provide data without required fields.
            $data['description']         = "";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));

            $id = $note->id;
            $data = array();
            $data['description']                = '';
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        /**
        * @depends testApiServerUrl
        */
        public function testEditNoteWIthIncorrectDataType()
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

            $note = NoteTestHelper::createNoteByNameForOwner('Newest Note', $super);

            // Provide data with wrong type.
            $data['occurredOnDateTime']         = "A";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(2, count($response['errors']));

            $id = $note->id;
            $data = array();
            $data['occurredOnDateTime']         = "A";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/notes/note/api/update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }
    }
?>