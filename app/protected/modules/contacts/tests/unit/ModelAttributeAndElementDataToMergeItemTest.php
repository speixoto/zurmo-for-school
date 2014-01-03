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

    class ModelAttributeAndElementDataToMergeItemTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
        }

        public function testPreElementContentForContacts()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = UserTestHelper::createBasicUser('Steven');

            $account = new Account();
            $account->name  = 'Some Account';
            $account->owner = $user;
            $this->assertTrue($account->save());

            $contactStates = ContactState::getByName('Qualified');
            $contactCustomerStates = ContactState::getByName('Customer');

            $contact = new Contact();
            $this->assertNull($contact->latestActivityDateTime);
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $contact->setLatestActivityDateTime($dateTime);
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
            $contact->primaryEmail->emailAddress   = 'thejman@zurmoinc.com';
            $contact->primaryEmail->optOut         = 0;
            $contact->primaryEmail->isInvalid      = 0;
            $contact->secondaryEmail->emailAddress = 'digi@magic.net';
            $contact->secondaryEmail->optOut       = 1;
            $contact->secondaryEmail->isInvalid    = 1;
            $contact->primaryAddress->street1      = '129 Noodle Boulevard';
            $contact->primaryAddress->street2      = 'Apartment 6000A';
            $contact->primaryAddress->city         = 'Noodleville';
            $contact->primaryAddress->postalCode   = '23453';
            $contact->primaryAddress->country      = 'The Good Old US of A';
            $contact->secondaryAddress->street1    = '25 de Agosto 2543';
            $contact->secondaryAddress->street2    = 'Local 3';
            $contact->secondaryAddress->city       = 'Ciudad de Los Fideos';
            $contact->secondaryAddress->postalCode = '5123-4';
            $contact->secondaryAddress->country    = 'Latinoland';
            $this->assertTrue($contact->save());
            $id = $contact->id;
            unset($contact);

            $contact = Contact::getById($id);
            $contact2   = ContactTestHelper::createContactByNameForOwner('shozin', Yii::app()->user->userModel);
            $contact2->title->value  = 'Mrs.';
            $contact2->state         = $contactCustomerStates[0];
            $contact2->primaryEmail->emailAddress   = 'test@yahoo.com';
            $contact2->primaryEmail->optOut         = 0;
            $contact2->primaryEmail->isInvalid      = 0;
            $contact2->secondaryEmail->emailAddress = 'test@gmail.com';
            $contact2->secondaryEmail->optOut       = 1;
            $contact2->secondaryEmail->isInvalid    = 1;
            $contact2->primaryAddress->street1      = '302';
            $contact2->primaryAddress->street2      = '9A/1';
            $contact2->primaryAddress->city         = 'New Delhi';
            $contact2->primaryAddress->postalCode   = '110005';
            $contact2->primaryAddress->country      = 'India';
            $contact2->secondaryAddress->street1    = 'A-8';
            $contact2->secondaryAddress->street2    = 'Sector 56';
            $contact2->secondaryAddress->city       = 'Gurgaon';
            $contact2->secondaryAddress->postalCode = '5123-4';
            $contact2->secondaryAddress->country    = 'IndiaTest';
            $contact2->save();
            $selectedContacts = array($contact->id => $contact,
                                      $contact2->id => $contact2);
            $primaryModel     = $selectedContacts[$contact->id];
            $attributesToBeTested = array('title'     => 'DropDown',
                                          'firstName' => 'Text',
                                          'lastName'  => 'Text',
                                          'null'      => 'ContactStateDropDown',
                                          'primaryEmail'    => 'EmailAddressInformation',
                                          'secondaryEmail'  => 'EmailAddressInformation',
                                          'primaryAddress'    => 'Address',
                                          'secondaryAddress'  => 'Address'
                                          );
            $content = null;
            foreach($attributesToBeTested as $attribute => $elementType)
            {
                $attributeContent = null;
                foreach($selectedContacts as $selectedContact)
                {
                    $elementClass = $elementType . 'Element';
                    $element      = new $elementClass($selectedContact, $attribute);
                    $contactModelAttributeAndElementDataToMergeItem
                            = new ContactModelAttributeAndElementDataToMergeItem($selectedContact,
                                                                    $element->getAttribute(), $element, $primaryModel);
                    $attributeContent .= $contactModelAttributeAndElementDataToMergeItem->getAttributeRenderedContent();
                    $content .= ZurmoHtml::tag('div', array(), $attributeContent);
                }
            }
            //First Name
            $matcherFirstName = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_firstName',
                                      'data-value' => 'Super')
            );
            $matcherFirstName2 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_firstName',
                                      'data-value' => 'shozin')
            );
            $this->assertTag($matcherFirstName, $content);
            $this->assertTag($matcherFirstName2, $content);
            //Last Name
            $matcherLastName = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_lastName',
                                      'data-value' => 'Man')
            );
            $matcherLastName2 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_lastName',
                                      'data-value' => 'shozinson')
            );
            $this->assertTag($matcherLastName, $content);
            $this->assertTag($matcherLastName2, $content);

            //Title
            $matcherTitle = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_title_value',
                                      'data-value' => 'Mr.')
            );
            $matcherTitle2 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_title_value',
                                      'data-value' => 'Mrs.')
            );
            $this->assertTag($matcherTitle, $content);
            $this->assertTag($matcherTitle2, $content);

            //Contact state dropdown
            $matcherContactState = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_state_id',
                                      'data-value' => $contactStates[0]->id)
            );
            $matcherContactState2 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_state_id',
                                      'data-value' => $contactCustomerStates[0]->id)
            );
            $this->assertTag($matcherContactState, $content);
            $this->assertTag($matcherContactState2, $content);

            //Primary email
            $matcherPrimaryEmail = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_primaryEmail_emailAddress',
                                      'data-value' => 'thejman@zurmoinc.com')
            );
            $matcherPrimaryEmail2 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_primaryEmail_emailAddress',
                                      'data-value' => 'test@yahoo.com')
            );
            $this->assertTag($matcherPrimaryEmail, $content);
            $this->assertTag($matcherPrimaryEmail2, $content);

            //Secondary email
            $matcherSecEmail = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_secondaryEmail_emailAddress',
                                      'data-value' => 'digi@magic.net')
            );
            $matcherSecEmail2 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_secondaryEmail_emailAddress',
                                      'data-value' => 'test@gmail.com')
            );
            $this->assertTag($matcherSecEmail, $content);
            $this->assertTag($matcherSecEmail2, $content);

            /*----------------Primary address starts here---------------*/
            //street1
            $matcherPrimaryAddressStreet1 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_primaryAddress_street1',
                                      'data-value' => '129 Noodle Boulevard')
            );
            $matcherPrimaryAddressStreet12 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_primaryAddress_street1',
                                      'data-value' => '302')
            );
            $this->assertTag($matcherPrimaryAddressStreet1, $content);
            $this->assertTag($matcherPrimaryAddressStreet12, $content);

            //street2
            $matcherPrimaryAddressStreet2 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_primaryAddress_street2',
                                      'data-value' => 'Apartment 6000A')
            );
            $matcherPrimaryAddressStreet22 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_primaryAddress_street2',
                                      'data-value' => '9A/1')
            );
            $this->assertTag($matcherPrimaryAddressStreet2, $content);
            $this->assertTag($matcherPrimaryAddressStreet22, $content);
            /*----------------Primary address ends here---------------*/

            /*----------------Secondary address starts here---------------*/
            //street1
            $matcherSecondaryAddressStreet1 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_secondaryAddress_street1',
                                      'data-value' => '25 de Agosto 2543')
            );
            $matcherSecondaryAddressStreet12 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_secondaryAddress_street1',
                                      'data-value' => 'A-8')
            );
            $this->assertTag($matcherSecondaryAddressStreet1, $content);
            $this->assertTag($matcherSecondaryAddressStreet12, $content);

            //street2
            $matcherSecondaryAddressStreet2 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_secondaryAddress_street2',
                                      'data-value' => 'Local 3')
            );
            $matcherSecondaryAddressStreet22 = array(
                'tag' => 'a',
                'attributes' => array('data-id' => 'Contact_secondaryAddress_street2',
                                      'data-value' => 'Sector 56')
            );
            $this->assertTag($matcherSecondaryAddressStreet2, $content);
            $this->assertTag($matcherSecondaryAddressStreet22, $content);
            /*----------------Secondary address ends here---------------*/
        }
    }
?>
