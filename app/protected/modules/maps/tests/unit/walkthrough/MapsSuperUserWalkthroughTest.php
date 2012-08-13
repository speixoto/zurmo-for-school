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
     * Maps Module Super User Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class MapsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AddressGeoCodeTestHelper::createAndRemoveAccountWithAddress($super);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retriving the super account id.
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');

            //Create address array for the super account id.
            $address = array('street1'    => '123 Knob Street',
                             'street2'    => 'Apartment 4b',
                             'city'       => 'Chicago',
                             'state'      => 'Illinois',
                             'postalCode' => '60606',
                             'country'    => 'USA'
                       );

            //Assign Address to the super user account.
            AddressGeoCodeTestHelper::updateTestAccountsWithBillingAddress($superAccountId, $address, $super);

            //Fetch Latitute and Longitude values for address and save in Address.
            AddressMappingUtil::updateChangedAddresses();

            $accounts = Account::getByName('superAccount');
            $this->assertEquals(1, count($accounts));

            $this->assertEquals(round('42.1153153', 4),  round($accounts[0]->billingAddress->latitude, 4));
            $this->assertEquals(round('-87.9763703', 4), round($accounts[0]->billingAddress->longitude, 4));
            $this->assertEquals(0,             $accounts[0]->billingAddress->invalid);

            $addressString = $accounts[0]->billingAddress->makeAddress();
            $this->setGetArray(array('addressString' => $addressString,
                                     'latitude'      => $accounts[0]->billingAddress->latitude,
                                     'longitude'     => $accounts[0]->billingAddress->longitude));

            $content = $this->runControllerWithNoExceptionsAndGetContent('maps/default/mapAndPoint');
            $this->assertTrue(strpos($content, 'plotMap') > 0);
        }
     }
?>
