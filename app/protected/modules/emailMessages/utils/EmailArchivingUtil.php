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
     * Helper class to work with inbound emails
     */
    class EmailArchivingUtil
    {
        /**
         * For a given email find user.
         * Function consider that user sent email to dropbox (To, CC or BCC),
         * or forwarded email to dropbox, via his email client.
         * @param ImapMessage $emailMessage
         * @return User $user
         */
        public static function resolveOwnerOfEmailMessage(ImapMessage $emailMessage)
        {
            if (isset($emailMessage->fromEmail) && $emailMessage->fromEmail != '')
            {
                $searchAttributeData = array();
                $searchAttributeData['clauses'] = array(
                    1 => array(
                        'attributeName'        => 'primaryEmail',
                        'relatedAttributeName' => 'emailAddress',
                        'operatorType'         => 'equals',
                        'value'                => $emailMessage->fromEmail,
                    )
                );
                $searchAttributeData['structure'] = '1';
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('User');
                $where = RedBeanModelDataProvider::makeWhere('User', $searchAttributeData, $joinTablesAdapter);
                $models = User::getSubset($joinTablesAdapter, null, null, $where, null);
            }

            if (count($models) == 0)
            {
                throw new NotFoundException();
            }
            elseif (count($models) > 1)
            {
                return NotSupportedException();
            }
            else
            {
                return $models[0];
            }
        }

        /**
         * Get informations from email message, for example sender, recipient, subject...
         * It is quite different for forwarded messages, because we need to parse email
         * body to get those information.
         * @param ImapMessage $emailMessage
         * @param string $fromAddress
         */
        public static function resolveEmailSenderFromEmailMessage(ImapMessage $emailMessage)
        {
            // Check if email is forwarded or not.
            $emailSender = false;
            if (self::isMessageForwarded($emailMessage))
            {
                // Somebody sent email to user, and the user forwarded it to dropbox,
                // sender is in body->from field
                $emailSender = self::resolveEmailSenderFromForwardedEmailMessage($emailMessage);
            }
            else
            {
                // User sent email to somebody, and to dropbox(to, cc, bcc), so the user is sender
                $emailSender['email'] = $emailMessage->fromEmail;
                if (isset($emailMessage->fromName))
                {
                    $emailSender['name'] = $emailMessage->fromName;
                }
                else
                {
                    $emailSender['name'] = '';
                }
            }
            return $emailSender;
        }

        /**
        * Get recipient details from email message.
        * Have to cover two cases: when message is CC-ed or BCC-ed to dropbox,
        * and when email message is forwarded to dropbox.
        * 1. If message is CC-ed or BCC-ed to dropbox, recipients can be exctracted from "To" field of email message
        * 2. If message is forwarded, then email from which message is forwarded to dropbox is recipient
        * @param ImapMessage $emailMessage
        * @param array $emailRecipient
        */
        public static function resolveEmailRecipientsFromEmailMessage(ImapMessage $emailMessage)
        {
            // Check if email is forwarded or not.
            if (self::isMessageForwarded($emailMessage))
            {
                // Somebody sent email to Zurmo user, the user forwarded it to dropbox, so the user is a recipient
                $emailRecipients = array(
                    array(
                        'email' => $emailMessage->fromEmail,
                        'name'  => $emailMessage->fromName
                    )
                );
            }
            else
            {
                // Zurmo user sent email, so recipients are in 'To' and 'CC' fields
                $emailRecipients = $emailMessage->to;

                foreach ($emailMessage->to as $key => $value)
                {
                    if ($value['email'] == Yii::app()->imap->imapUsername)
                    {
                        unset($emailMessage->to[$key]);
                    }
                }
                if (!empty($emailMessage->cc))
                {
                    foreach ($emailMessage->cc as $key => $value)
                    {
                        if ($value['email'] == Yii::app()->imap->imapUsername)
                        {
                            unset($emailMessage->cc[$key]);
                        }
                    }
                    $emailRecipients = ArrayUtil::arrayUniqueRecursive(array_merge($emailRecipients, $emailMessage->cc));
                }
            }
            return $emailRecipients;
        }

        /**
         * Check if email message is forwarded or not, based on email subject.
         * For works only with few emails clients: Gmail, Outlook, ThunderBird, Yahoo
         * @param ImapMessage $emailMessage
         * @return boolean $isForwrded
         */
        public static function isMessageForwarded(ImapMessage $emailMessage)
        {
            $isForwarded = false;
            foreach ($emailMessage->to as $toAddress)
            {
                if ($toAddress['email'] == Yii::app()->imap->imapUsername)
                {
                    $isForwarded = true;
                    break;
                }
            }
            return $isForwarded;
        }

        /**
         * Parse email to get original sender(in case of forwarded messages)
         * For now we extract only from email and name
         * @param ImapMessage $emailMessage
         * @return array $emailInfo
         */
        public static function resolveEmailSenderFromForwardedEmailMessage(ImapMessage $emailMessage)
        {
            $emailSender   = false;
            $pattern = '/^\s*(?:.*?):\s*(.*)?\s*(?:\[mailto:|<)([\w.%+\-]+@[\w.\-]+\.[A-Za-z]{2,6})(?:[\]>])(?:.*)?$/mi'; // Not Coding Standard
            $noOfMatches = false;
            if ($emailMessage->textBody != '')
            {
                $noOfMatches = preg_match($pattern, $emailMessage->textBody, $matches);
            }
            elseif ($emailMessage->htmlBody != '')
            {
                $noOfMatches = preg_match($pattern, $emailMessage->htmlBody, $matches);
            }

            if ($noOfMatches > 0)
            {
                $emailSender['name'] = trim($matches[1]);
                $emailSender['email'] = trim($matches[2]);
            }

            return $emailSender;
        }

        /**
         * Given an email address and user, get the objects that match the email address filtered by the rights security
         * for the provided user.
         * @param string $emailAddress
         * @param User $user
         * @return array of objects which are either persons and/or accounts
         */
        public static function getPersonsAndAccountsByEmailAddressForUser($emailAddress, User $user)
        {
            assert('is_string($emailAddress)');
            $userCanAccessContacts = RightsUtil::canUserAccessModule('ContactsModule', $user);
            $userCanAccessLeads    = RightsUtil::canUserAccessModule('LeadsModule',    $user);
            $userCanAccessAccounts = RightsUtil::canUserAccessModule('AccountsModule', $user);
            $userCanAccessUsers    = RightsUtil::canUserAccessModule('UsersModule',    $user);
            return self::getPersonsAndAccountsByEmailAddress($emailAddress,
                                                                 $userCanAccessContacts,
                                                                 $userCanAccessLeads,
                                                                 $userCanAccessAccounts,
                                                                 $userCanAccessUsers);
        }

        /**
         * Get all
         * @param string $emailAddress
         * @param boolean $userCanAccessContacts
         * @param boolean $userCanAccessLeads
         * @param boolean $userCanAccessAccounts
         * @param boolean $userCanAccessUsers
         * @return Contact || Account || User || NULL || array of objects
         */
        public static function getPersonsAndAccountsByEmailAddress($emailAddress,
                                                                   $userCanAccessContacts  = false,
                                                                   $userCanAccessLeads     = false,
                                                                   $userCanAccessAccounts  = false,
                                                                   $userCanAccessUsers     = false)
        {
            assert('is_string($emailAddress)');
            assert('is_bool($userCanAccessContacts)');
            assert('is_bool($userCanAccessLeads)');
            assert('is_bool($userCanAccessAccounts)');
            assert('is_bool($userCanAccessUsers)');
            $personsAndAccounts    = array();
            if ($userCanAccessContacts || $userCanAccessLeads)
            {
                $stateMetadataAdapterClassName = LeadsStateMetadataAdapter::
                    resolveStateMetadataAdapterClassNameByAccess($userCanAccessContacts, $userCanAccessLeads);
                $personsAndAccounts = ContactSearch::
                                      getContactsByAnyEmailAddress($emailAddress, 1, $stateMetadataAdapterClassName);
            }
            if ($userCanAccessAccounts)
            {
                $personsAndAccounts = array_merge($personsAndAccounts,
                                                  AccountSearch::getAccountsByAnyEmailAddress($emailAddress, 1));
            }
            if ($userCanAccessUsers)
            {
                $personsAndAccounts = array_merge($personsAndAccounts,
                                                  UserSearch::getUsersByEmailAddress($emailAddress));
            }
            return $personsAndAccounts;
        }

        /**
         * Get Contact or Account or User, based on email address
         * @param string $emailAddress
         * @param boolean $userCanAccessContacts
         * @param boolean $userCanAccessLeads
         * @param boolean $userCanAccessAccounts
         * @param boolean $userCanAccessUsers
         * @return Contact || Account || User || NULL
         */
        public static function resolvePersonOrAccountByEmailAddress($emailAddress,
                                                                      $userCanAccessContacts = false,
                                                                      $userCanAccessLeads = false,
                                                                      $userCanAccessAccounts = false,
                                                                      $userCanAccessUsers = false)
        {
            assert('is_string($emailAddress)');
            assert('is_bool($userCanAccessContacts)');
            assert('is_bool($userCanAccessLeads)');
            assert('is_bool($userCanAccessAccounts)');
            assert('is_bool($userCanAccessUsers)');
            $personOrAccount   = null;
            $contactsOrLeads   = array();
            if ($userCanAccessContacts || $userCanAccessLeads)
            {
                $stateMetadataAdapterClassName = LeadsStateMetadataAdapter::
                    resolveStateMetadataAdapterClassNameByAccess($userCanAccessContacts, $userCanAccessLeads);
                $contactsOrLeads = ContactSearch::getContactsByAnyEmailAddress($emailAddress, null, $stateMetadataAdapterClassName);
            }

            if (!empty($contactsOrLeads))
            {
                $personOrAccount = $contactsOrLeads[0];
            }
            else
            {
                $accounts = array();
                // Check if email belongs to account
                if ($userCanAccessAccounts)
                {
                    $accounts = AccountSearch::getAccountsByAnyEmailAddress($emailAddress);
                }

                if (count($accounts))
                {
                    $personOrAccount = $accounts[0];
                }
                else
                {
                    $users = array();
                    if ($userCanAccessUsers)
                    {
                        $users = UserSearch::getUsersByEmailAddress($emailAddress);
                    }
                    if (count($users))
                    {
                        $personOrAccount = $users[0];
                     }
                }
            }
            return $personOrAccount;
        }
    }
?>
