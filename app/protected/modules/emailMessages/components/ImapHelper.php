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
         * For now this function works only for emails that are sent directly to user,
         * or forwarded to dropbox, using .forward directive on Linux systems.
         * We need to add parser, in case when user forwarded email to dropbox, or
         * watched email box, via his email client.
         * @param email message $email
         * @param boolean $forward
         * @return array of User $users
         */
        public static function resolveUserFromEmailAddress(ImapMessage $emailMessage)
        {
            // Check if email is forwarded or not.
            $to = false;
            if (self::isMessageForwarded($emailMessage))
            {
                $to['email'] = $emailMessage->from;
            }
            else
            {
                if (isset($emailMessage->to) && count($emailMessage->to) > 0)
                {
                    $to = $emailMessage->to[0];
                }
            }

            if ($to)
            {
                $searchAttributeData = array();
                $searchAttributeData['clauses'] = array(
                    1 => array(
                        'attributeName'        => 'primaryEmail',
                        'relatedAttributeName' => 'emailAddress',
                        'operatorType'         => 'equals',
                        'value'                => $to['email'],
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

        public static function resolveFromEmailAddress(ImapMessage $emailMessage)
        {
            // Check if email is forwarded or not.
            $from = false;
            if (self::isMessageForwarded($emailMessage))
            {
                $from = self::getOriginalSenderFromForwardedMessage($emailMessage);
            }
            else
            {
                $from = $emailMessage->from;
            }
            return $from;
        }

        /**
         * Check if email message is forwarded or not, based on subject.
         * @param ImapMessage $emailMessage
         * @return boolean
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

        public static function getOriginalSenderFromForwardedMessage(ImapMessage $emailMessage)
        {
            $from = false;
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
                $from['name'] = $matches[1];
                $from['email'] = $matches[2];
            }
            return $from;
        }
    }
?>