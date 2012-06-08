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
    class EmailArchivingHelper
    {
        public static $validEmailClientForwardSubjectPrefixes = array(
            'Fwd:', // Gmail, ThunderBird/IceDove
            'FW:',  // Outlook
            'Fw:',  //Yahoo
        );
        /**
         * For a given email find user.
         * Function consider that user sent email to dropbox (To, CC or BCC),
         * or forwarded email to dropbox, via his email client/
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
         * Get informations from email message, for example sender, receiver, subject...
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
                //Somebody sent email to me, and I forwarded it to dropbox
                // so, sender is in body_>from field
                $emailSender = self::resolveEmailSenderFromForwardedEmailMessage($emailMessage);
            }
            else
            {
                // I sent email to somebody
                // soI am sender
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
        * Get receiver details from email message.
        * Have to cover two cases: when message is CC-ed or BCC-ed to dropbox,
        * and when email message is forwarded to dropbox.
        * 1. If message is CC-ed or BCC-ed to dropbox, receipts can be exctracted from "To" field of email message
        * 2. If message is forwarded, then email from which message is forwarded to dropbox is receiver
        * @param ImapMessage $emailMessage
        * @param array $emailReceivers
        */
        public static function resolveEmailReceiversFromEmailMessage(ImapMessage $emailMessage)
        {
            // Check if email is forwarded or not.
            $emailReceivers = false;
            if (self::isMessageForwarded($emailMessage))
            {
                // Somebody sent email to me, I forwarded it to dropbox, so I am receiver
                $emailReceivers = array(
                    array(
                        'email' => $emailMessage->fromEmail,
                        'name'  => $emailMessage->fromName
                    )
                );
            }
            else
            {
                //I am sending email, so receivers is to
                $emailReceivers = $emailMessage->to;
                // To-Do: Get additional details, like CC receipts
            }
            return $emailReceivers;
        }
        /**
         * Check if email message is forwarded or not, based on email subject.
         * For works only with few emails clients: Gmail, Outlook, ThunderBird, Yahoo
         * @param ImapMessage $emailMessage
         * @return boolean $isForwrded
         */
        public static function isMessageForwarded(ImapMessage $emailMessage)
        {
            $isForwrded = false;
            foreach (self::$validEmailClientForwardSubjectPrefixes as $forwardSubjectPrefix)
            {
                if (stristr($emailMessage->subject, $forwardSubjectPrefix))
                {
                    $isForwrded = true;
                }
            }
            return $isForwrded;
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
            $pattern = '/^\s*From:\s+(.*?)\s*(?:\[mailto:|<)(.*?)(?:[\]>])\s*$/mi';

            $noOfMatches = false;
            if ($emailMessage->textBody != '')
            {
                $noOfMatches = preg_match($pattern, $emailMessage->textBody, $matches);
            }
            else
            {
                // It is low probability that we can extract data from html message,
                // because formats are very different for each email client
                $noOfMatches = preg_match($pattern, $emailMessage->htmlBody, $matches);
            }

            // Fix this, so we can match email only for example!!!
            if ($noOfMatches > 0)
            {
                $emailSender['name'] = $matches[1];
                $emailSender['email'] = $matches[2];
            }

            return $emailSender;
        }

        // Not used
        public function extract_from_email($string){
            // preg_match("/From.*\w+([\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+)/i", $string, $matches);
            preg_match("/(From|Von).*\w+[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $string, $matches);
            preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $matches[0], $matches);
            return $matches[0];
        }
    }
?>