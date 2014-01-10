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
     * Class that builds demo Web Forms Entry.
     */
    class ContactWebFormEntryDemoDataMaker extends DemoDataMaker
    {
        protected $index;

        protected $seedData;

        public static function getDependencies()
        {
            return array();
        }

        /**
         * @param DemoDataHelper $demoDataHelper
         */
        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');

            $contactWebFormEntries = array();
            for ($this->index = 0; $this->index < 5; $this->index++)
            {
                $contactWebFormEntry                 = new ContactWebFormEntry();
                $contactWebFormEntry->contactWebForm = $demoDataHelper->getRandomByModelName('ContactWebForm');
                $this->populateModel($contactWebFormEntry);
                $saved                               = $contactWebFormEntry->save();
                assert('$saved');
                $contactWebFormEntries[]             = $contactWebFormEntry->id;
            }
            $demoDataHelper->setRangeByModelName('ContactWebFormEntry', $contactWebFormEntries[0], $contactWebFormEntries[count($contactWebFormEntries)-1]);
        }

        /**
         * @param RedBeanModel $model
         */
        public function populateModel(& $model)
        {
            assert('$model instanceof ContactWebFormEntry');
            parent::populateModel($model);
            if (empty($this->seedData))
            {
                $this->seedData =  ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('ContactWebFormsModule',
                                                                                                'ContactWebFormEntry');
            }
            $contactFormAttributes  = array();
            $contact                = new Contact();
            $contact->owner         = $model->contactWebForm->defaultOwner;
            $contact->state         = $model->contactWebForm->defaultState;
            $contact->firstName     = $contactFormAttributes['firstName']   = $this->seedData['firstName'][$this->index];
            $contact->lastName      = $contactFormAttributes['lastName']    = $this->seedData['lastName'][$this->index];
            $contact->companyName   = $contactFormAttributes['companyName'] = $this->seedData['companyName'][$this->index];
            $contact->jobTitle      = $contactFormAttributes['jobTitle']    = $this->seedData['jobTitle'][$this->index];
            if ($contact->validate())
            {
                $contactWebFormEntryStatus  = ContactWebFormEntry::STATUS_SUCCESS;
                $contactWebFormEntryMessage = ContactWebFormEntry::STATUS_SUCCESS_MESSAGE;
                $contact->save();
            }
            else
            {
                $contactWebFormEntryStatus  = ContactWebFormEntry::STATUS_ERROR;
                $contactWebFormEntryMessage = ContactWebFormEntry::STATUS_ERROR_MESSAGE;
                $contact = null;
            }
            $model->contact                 = $contact;
            $model->status                  = $contactWebFormEntryStatus;
            $model->message                 = $contactWebFormEntryMessage;
            $contactFormAttributes['owner'] = $model->contactWebForm->defaultOwner->id;
            $contactFormAttributes['state'] = $model->contactWebForm->defaultState->id;
            $model->serializedData          = serialize($contactFormAttributes);
        }
    }
?>