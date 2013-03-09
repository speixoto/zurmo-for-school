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
     * Form to help manage marketing list member display options supporting filtering by subscription type.
     */
    class MarketingListMembersConfigurationForm extends CFormModel
    {
        /**
         * Value to be used to signal that the filtering is for all subscriptions and not a specific one.
         * @var string
         */
        const  FILTERED_USER_ALL = 'all';

        /**
         * Value to be used to signal that the filtering is for just subscribers.
         * @var string
         */
        const  FILTER_USER_SUBSCRIBERS = 'subscribers';

        /**
         * Value to be used to signal that the filtering is for just unsubscribers
         * @var string
         */
        const FILTER_USER_UNSUBSCRIBERS = 'unsubscribers';


        /**
         * Whether to filter marketing list member feed by subscription type.
         * Defaults to not filtering on anything
         * @var string
         */
        public $filteredBySubscriptionType = self::FILTERED_USER_ALL;

        public function rules()
        {
            return array(
                array('filteredBySubscriptionType', 'type',    'type' => 'string'),
            );
        }
    }
?>