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
    class ImapHelper
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
        public static function resolveUserFromEmailAddress(ImapMessage $emailMessage)
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
         * Get original sender of email message.
         * @param ImapMessage $emailMessage
         * @param string $fromAddress
         */
        public static function resolveFromEmailAddress(ImapMessage $emailMessage)
        {
            // Check if email is forwarded or not.
            $fromAddress = false;
            if (self::isMessageForwarded($emailMessage))
            {
                $fromAddress = self::getOriginalSenderFromForwardedMessage($emailMessage);
            }
            else
            {
                $fromAddress = $emailMessage->from;
            }
            return $fromAddress;
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
         * @param ImapMessage $emailMessage
         * @return array $fromAddress
         */
        public static function getOriginalSenderFromForwardedMessage(ImapMessage $emailMessage)
        {
            $fromAddress = false;
            $pattern = '/^From:\s+(.*?)\s*(?:\[mailto:|<)(.*?)(?:[\]>])$/mi';

            // Try first to extract info from text body
            if ($emailMessage->textBody != '')
            {
                $noOfMatches = preg_match($pattern, $emailMessage->textBody, $matches);
            }
            else
            {
                $noOfMatches = preg_match($pattern, $emailMessage->htmlBody, $matches);
            }

            if ($noOfMatches > 0)
            {
                $fromAddress['name'] = $matches[1];
                $fromAddress['email'] = $matches[2];
            }
            return $fromAddress;
        }
    }
?>