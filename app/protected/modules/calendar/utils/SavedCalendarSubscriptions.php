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

    class SavedCalendarSubscriptions
    {
        protected $mySavedCalendarsAndSelected;

        protected $subscribedToSavedCalendarsAndSelected;

        public static function makeByUser(User $user)
        {
            $savedCalendarSubscriptions = new SavedCalendarSubscription();

            //todo: get saved calendars that the user owns (from somewhereelse?)

                //$savedCalendarSubscriptions->addMySavedCalendar($mySavedCalendar, $selected);

            //todo: get saved calendars that the user is subscribed too (from some subscription model?)

                //$savedCalendarSubscriptions->addSubscribedToSavedCalendar($subscribedToSavedCalendar, $selected);

            return $savedCalendarSubscriptions;
        }

        /**
         * @param SavedCalendar $savedCalendar
         * @param bool $selected
         */
        public function addMySavedCalendar(SavedCalendar $savedCalendar, $selected)
        {
            assert('is_bool($selected)');
            if(!isset($this->mySavedCalendarsAndSelected[$savedCalendar->id]) &&
               !isset($this->subscribedToSavedCalendarsAndSelected[$savedCalendar->id]))
            {
                $this->mySavedCalendarsAndSelected[$savedCalendar->id] = array($savedCalendar, $selected);
            }
        }

        /**
         * @param SavedCalendar $savedCalendar
         * @param bool $selected
         */
        public function addSubscribedToCalendar(SavedCalendar $savedCalendar, $selected)
        {
            assert('is_bool($selected)');
            if(!isset($this->mySavedCalendarsAndSelected[$savedCalendar->id]) &&
                !isset($this->subscribedToSavedCalendarsAndSelected[$savedCalendar->id]))
            {
                $this->subscribedToSavedCalendarsAndSelected[$savedCalendar->id] = array($savedCalendar, $selected);
            }
        }

        public function getMySavedCalendarsAndSelected()
        {
            return $this->mySavedCalendarsAndSelected;
        }

        public function getSubscribedToSavedCalendarsAndSelected()
        {
            return $this->subscribedToSavedCalendarsAndSelected;
        }
    }
?>