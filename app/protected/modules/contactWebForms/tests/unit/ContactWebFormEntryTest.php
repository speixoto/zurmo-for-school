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

    class ContactWebFormEntryTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testCreateAndGetContactWebFormEntryById()
        {
            $contact     = new Contact();
            $adapter     = new ModelAttributesAdapter($contact);
            $attributes  = $adapter->getAttributes();
            $attributes  = ArrayUtil::subValueSort($attributes, 'attributeLabel', 'asort');
            $attributes  = array_keys($attributes);
            ContactsModule::loadStartingData();
            $contactStates                      = ContactState::getByName('New');
            $contactWebForm                     = new ContactWebForm();
            $contactWebForm->name               = 'Test Form';
            $contactWebForm->redirectUrl        = 'http://google.com';
            $contactWebForm->submitButtonLabel  = 'Save';
            $contactWebForm->defaultState       = $contactStates[0];
            $contactWebForm->serializedData     = serialize($attributes);
            $contactWebForm->save();

            $contactFormAttributes = array_flip($attributes);
            ArrayUtil::arrayClearValues($contactFormAttributes);

            $user           = User::getByUsername('super');
            $account        = new Account();
            $account->name  = 'Some Account';
            $account->owner = $user;
            $account->save();

            $contact->owner         = $user;
            $contact->title->value  = 'Mr.';
            $contact->firstName     = 'Super';
            $contact->lastName      = 'Man';
            $contact->jobTitle      = 'Superhero';
            $contact->source->value = 'Outbound';
            $contact->account       = $account;
            $contact->description   = 'Some Description';
            $contact->department    = 'Red Tape';
            $contact->officePhone   = '1234567890';
            $contact->mobilePhone   = '0987654321';
            $contact->officeFax     = '1222222222';
            $contact->state         = $contactStates[0];
            if ($contact->validate())
            {
                $contactWebFormEntryStatus  = ContactWebFormEntry::STATUS_SUCCESS;
                $contactWebFormEntryMessage = 'Success';
            }
            else
            {
                $contactWebFormEntryStatus  = ContactWebFormEntry::STATUS_ERROR;
                $contactWebFormEntryMessage = 'Validation Failed';
            }
            $contact->save();

            $contactFormAttributes['owner']       = $contact->owner->id;
            $contactFormAttributes['title']       = $contact->title->value;
            $contactFormAttributes['firstName']   = $contact->firstName;
            $contactFormAttributes['lastName']    = $contact->lastName;
            $contactFormAttributes['jobTitle']    = $contact->jobTitle;
            $contactFormAttributes['source']      = $contact->source->value;
            $contactFormAttributes['account']     = $contact->account->id;
            $contactFormAttributes['description'] = $contact->description;
            $contactFormAttributes['department']  = $contact->department;
            $contactFormAttributes['officePhone'] = $contact->officePhone;
            $contactFormAttributes['mobilePhone'] = $contact->mobilePhone;
            $contactFormAttributes['officeFax']   = $contact->officeFax;
            $contactFormAttributes['state']       = $contact->state->id;

            $contactWebFormEntry = new ContactWebFormEntry();
            $contactWebFormEntry->serializedData = serialize($contactFormAttributes);
            $contactWebFormEntry->status         = $contactWebFormEntryStatus;
            $contactWebFormEntry->message        = $contactWebFormEntryMessage;
            $contactWebFormEntry->contactWebForm = $contactWebForm;
            $contactWebFormEntry->contact        = $contact;
            $this->assertTrue($contactWebFormEntry->save());
            $contactWebFormEntryId               = $contactWebFormEntry->id;
            unset($contactWebFormEntry);

            $contactWebFormEntry = ContactWebFormEntry::getById($contactWebFormEntryId);
            $this->assertEquals('Test Form', $contactWebFormEntry->contactWebForm->name);
            $this->assertEquals('Super',     $contactWebFormEntry->contact->firstName);
            $this->assertEquals('Man',       $contactWebFormEntry->contact->lastName);
            $contactFormAttributes = unserialize($contactWebFormEntry->serializedData);
            $this->assertEquals('1234567890', $contactFormAttributes['officePhone']);
        }
    }
?>