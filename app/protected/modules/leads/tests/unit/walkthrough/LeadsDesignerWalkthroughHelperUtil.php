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

    /**
     * Helper functions to assist with testing designer walkthroughs specifically for leads search form parameters.
     */
    class LeadsDesignerWalkthroughHelperUtil
    {
        /**
         * This function returns the necessary get parameters for the lead search form
         * based on the lead edited data.
         */
        public static function fetchLeadsSearchFormGetData($leadStateId, $superUserId)
        {
            return  array(
                            'fullName'           => 'Sarah Williams Edit',
                            'officePhone'        => '739-742-3005',
                            'anyPostalCode'      => '95131',
                            'department'         => 'Sales Edit',
                            'companyName'        => 'ABC Telecom Edit',
                            'industry'           => array('value' => 'Banking'),
                            'website'            => 'http://www.companyedit.com',
                            'anyCountry'         => 'USA',
                            'anyInvalidEmail'    => array('value' => '0'),
                            'anyEmail'           => 'info@myNewLeadEdit.com',
                            'anyOptOutEmail'     => array('value' => '0'),
                            'ownedItemsOnly'     => '1',
                            'anyStreet'          => '26378 South Arlington Ave',
                            'anyCity'            => 'San Jose',
                            'anyState'           => 'CA',
                            'state'              => array('id' => $leadStateId),
                            'owner'              => array('id' => $superUserId),
                            'firstName'          => 'Sarah',
                            'lastName'           => 'Williams Edit',
                            'jobTitle'           => 'Sales Director Edit',
                            'officeFax'          => '255-454-1914',
                            'title'              => array('value' => 'Mrs.'),
                            'source'             => array('value' => 'Inbound Call'),
                            'decimalCstm'        => '12',
                            'integerCstm'        => '11',
                            'phoneCstm'          => '259-784-2069',
                            'textCstm'           => 'This is a test Edit Text',
                            'textareaCstm'       => 'This is a test Edit TextArea',
                            'urlCstm'            => 'http://wwww.abc-edit.com',
                            'checkboxCstm'       => array('value'  => '0'),
                            'currencyCstm'       => array('value'  => 40),
                            'picklistCstm'       => array('value'  => 'b'),
                            'multiselectCstm'    => array('values' => array('gg', 'hh')),
                            'tagcloudCstm'       => array('values' => array('reading', 'surfing')),
                            'countrylistCstm'    => array('value'  => 'aaaa'),
                            'statelistCstm'      => array('value'  => 'aaa1'),
                            'citylistCstm'       => array('value'  => 'ab1'),
                            'radioCstm'          => array('value'  => 'e'),
                            'dateCstm__Date'     => array('type'   => 'Today'),
                            'datetimeCstm__DateTime' => array('type'   => 'Today'));
        }
    }
?>