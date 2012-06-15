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

    class AddressGeoCodeTestHelper
    {
        public static function createTestAccountsWithBillingAddressAndGetAccount($address, $owner)
        {
            $account                             = new Account();
            $account->owner                      = $owner;
            $account->name                       = "Account";
            $account->officePhone                = rand(10000000, 90000000);
            $account->officeFax                  = rand(10000000, 90000000);
            $account->employees                  = rand(1, 100);
            $account->website                    = "http://www.account.com";
            $account->annualRevenue              = rand(10000, 10000000);
            $account->description                = "An account for some company called Account.";
            $account->primaryEmail->emailAddress = "info@account.com";
            $account->primaryEmail->optOut       = false;
            $account->primaryEmail->isInvalid    = false;
            foreach ($address as $key => $value)
            {
                $account->billingAddress->$key   = $value;
            }
            $account->save();
            return $account;
        }

        public static function updateTestAccountsWithBillingAddress($accountid, $address, $owner)
        {
            $account        = Account::getById($accountid);
            $account->owner = $owner;
            foreach ($address as $key => $value)
            {
                $account->billingAddress->$key = $value;
            }
            $account->save();
        }

        /**
         * Used for non-frozen mode to ensure the latitude and longitude columns are properly created
         * @param User $owner
         */
        public static function createAndRemoveAccountWithAddress($owner)
        {
            $address = array();
            $address['street1']    = '123 Knob Street';
            $address['street2']    = 'Apartment 4b';
            $address['city']       = 'Chicago';
            $address['state']      = 'Illinois';
            $address['postalCode'] = '60606';
            $address['country']    = 'USA';
            $address['latitude']   = 45.00;
            $address['longitude']  = 45.00;
            $account               = self::createTestAccountsWithBillingAddressAndGetAccount($address, $owner);
            $account->delete();
        }
    }
?>
