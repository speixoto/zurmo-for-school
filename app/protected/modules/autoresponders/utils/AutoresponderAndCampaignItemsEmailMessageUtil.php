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
     * Helper class for working with autoresponderItem and campaignItem email Messages
     * At places we intentionally use all lowercase variable names instead of camelCase to do easy
     * compact() on them and have them match column names in db on queries.
     */
    abstract class AutoresponderAndCampaignItemsEmailMessageUtil
    {
        public static $marketingListIdToSenderMapping    = array();

        public static $itemClass                         = null;

        public static $returnPath                        = null;

        public static $personId                          = null;

        const CONTENT_ID                                = "@contentId";

        const SENDER_ID                                 = "@senderId";

        const RECIPIENT_ID                              = "@recipientId";

        const EMAIL_MESSAGE_ITEM_ID                     = "@emailMessageItemId";

        const EMAIL_MESSAGE_ID                          = "@emailMessageId";

        public static function resolveAndSaveEmailMessage($textContent, $htmlContent, Item $itemOwnerModel,
                                                    Contact $contact, MarketingList $marketingList, $itemId, $folderId)
        {
            $time = microtime(true);
            $userId                 = static::resolveCurrentUserId();
            $ownerId                = $marketingList->owner->id;
            $subject                = $itemOwnerModel->subject;
            $serializedData         = serialize($subject);
            $headers                = static::resolveHeaders($itemId);
            $emailMessageData       = compact('subject', 'serializedData', 'textContent', 'htmlContent', 'userId', 'ownerId',
                                                'headers', 'attachments', 'folderId');
            $attachments            = array('relatedModelType' => strtolower(get_class($itemOwnerModel)),
                                            'relatedModelId' => $itemOwnerModel->id);
            $sender                 = static::resolveSender($marketingList, $itemOwnerModel);
            $recipient              = static::resolveRecipient($contact);
            $emailMessageData       = CMap::mergeArray($emailMessageData, $sender, $recipient, $attachments);
            $cTime = microtime(true);
            $emailMessageId         = static::saveEmailMessageWithRelated($emailMessageData);
            if (empty($emailMessageId))
            {
                throw new FailedToSaveModelException();
            }
            print(PHP_EOL . __CLASS__  . "/saving EmailMessage: " . (microtime(true) - $cTime));
            $emailMessage           = EmailMessage::getById($emailMessageId);
            print(PHP_EOL . __CLASS__  . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $emailMessage;
        }

        protected static function resolveSender(MarketingList $marketingList, $itemOwnerModel)
        {
            $time   = microtime(true);
            $sender = null;
            $marketingListId    = intval($marketingList->id);
            if (isset(static::$marketingListIdToSenderMapping[$marketingListId]))
            {
                $sender = static::$marketingListIdToSenderMapping[$marketingListId];
            }
            else
            {
                if (get_class($itemOwnerModel) == 'Campaign')
                {
                    $fromAddress    = $itemOwnerModel->fromAddress;
                    $fromName       = $itemOwnerModel->fromName;
                }
                else
                {
                    if (!empty($marketingList->fromname) && !empty($marketingList->fromaddress))
                    {
                        $fromAddress    = $marketingList->fromAddress;
                        $fromName       = $marketingList->fromName;
                    } else {
                        $userToSendMessagesFrom = BaseControlUserConfigUtil::getUserToRunAs();
                        $fromAddress    = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
                        $fromName       = strval($userToSendMessagesFrom);
                    }
                }
                $sender                                                     = compact('fromName', 'fromAddress');
                static::$marketingListIdToSenderMapping[$marketingListId]   = $sender;
            }
            print(PHP_EOL . __CLASS__  . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $sender;
        }

        protected static function resolveRecipient(Contact $contact)
        {
            $time       = microtime(true);
            $recipient  = null;
            if ($contact->primaryEmail->emailAddress != null)
            {
                $toAddress      = $contact->primaryEmail->emailAddress;
                $toName         = strval($contact);
                $recipientType  = EmailMessageRecipient::TYPE_TO;
                $contactItemId  = $contact->getClassId('Item');
                $recipient      = compact('toAddress', 'toName', 'recipientType', 'contactItemId');
            }
            print(PHP_EOL . __CLASS__  . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $recipient;
        }

        protected static function resolveHeaders($zurmoItemId)
        {
            $time               = microtime(true);
            $zurmoItemClass     = static::$itemClass;
            $zurmoPersonId      = static::$personId;
            $headers            = compact('zurmoItemId', 'zurmoItemClass', 'zurmoPersonId');
            if (static::$returnPath)
            {
                $headers['Return-Path'] = static::$returnPath;
            }
            $headers            = serialize($headers);
            print(PHP_EOL . __CLASS__  . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $headers;
        }

        protected static function saveEmailMessageWithRelated(array $emailMessageData)
        {
            $time           = microtime(true);
            $query          = static::resolveEmailMessageCreationFunctionQuery($emailMessageData);
            $emailMessageId = static::getCell($query);
            print(PHP_EOL . __CLASS__  . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $emailMessageId;
        }

        protected static function resolveEmailMessageCreationFunctionQueryWithPlaceholders()
        {
            $time       = microtime(true);
            $query      = "SELECT create_email_message(textContent, htmlContent, fromName,fromAddress , userId,
                                                    ownerId, subject, headers, folderId, serializedData, toAddress,
                                                    toName, recipientType, contactItemId, relatedModelType,
                                                    relatedModelId)";
            print(PHP_EOL . __CLASS__  . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $query;
        }

        protected static function resolveEmailMessageCreationFunctionQuery(array $emailMessageData)
        {
            $time       = microtime(true);
            $query                  = static::resolveEmailMessageCreationFunctionQueryWithPlaceholders();
            $emailMessageData       = static::escapeValues($emailMessageData);
            static::quoteValues($emailMessageData);
            $query                  = strtr($query, $emailMessageData);
            print(PHP_EOL . __CLASS__  . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $query;
        }

        protected static function getCell($query, $expectingAtLeastOne = true)
        {
            $result     = ZurmoRedBean::getCell($query);
            if (!isset($result) || ($result < 1 && $expectingAtLeastOne))
            {
                $selectQuery    = str_replace('COUNT(*)', '*', $query);
                echo PHP_EOL . $selectQuery . PHP_EOL;
                var_dump(ZurmoRedBean::getAll($selectQuery));
                throw new NotSupportedException("Query: " . PHP_EOL . $query);
            }
            return intval($result);
        }

        protected static function escapeValues(array $values)
        {
            $escapedValues  = array_map(array(ZurmoRedBean::$adapter, 'escape'), $values);
            return $escapedValues;
        }

        protected static function quoteValues(array & $values)
        {
            array_walk($values, create_function('&$value', '$value = "\'$value\'";'));
        }

        protected static function resolveCurrentUserId()
        {
            return Yii::app()->user->userModel->id;
        }
    }
?>