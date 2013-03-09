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
     * Helper class for working with marketing list member views.
     */
    class MarketingListMembersUtil
    {
        const CONFIG_MODULE_NAME = 'MarketingListsModule';

        public static function makeSearchAttributeData($marketingListId, $filterBySubscriptionType)
        {
            assert('is_int($marketingListId)');
            assert('is_string($filterBySubscriptionType)');
            $searchAttributeData            = array();
            $searchAttributeData['clauses'] = array(
                                                    1 => array(
                                                        'attributeName'        => 'marketingList',
                                                        'relatedAttributeName' => 'id',
                                                        'operatorType'         => 'equals',
                                                        'value'                => $marketingListId,
                                                    ),
                                                );
            $searchAttributeData['structure'] = 1;
            if ($filterBySubscriptionType !== null &&
                    $filterBySubscriptionType !== MarketingListMembersConfigurationForm::FILTERED_USER_ALL)
            {
                $UnsubscribeFlag = ($filterBySubscriptionType == MarketingListMembersConfigurationForm::FILTER_USER_UNSUBSCRIBERS)?
                                                1 : 0;
                $searchAttributeData['clauses'][]  = array(
                                                            'attributeName'   =>  'unsubscribed',
                                                            'operatorType'    =>  'equals',
                                                            'value'           =>  $UnsubscribeFlag,
                                                        );
                $searchAttributeData['structure']   = '(1 and 2)';
            }
            return array(array('MarketingListMember' => $searchAttributeData));
        }


        public static function makeSortAttributeData()
        {
            return array('MarketingListMember' => 'createdDateTime'); // TODO: @Shoaibi: change this.
        }
  }
?>