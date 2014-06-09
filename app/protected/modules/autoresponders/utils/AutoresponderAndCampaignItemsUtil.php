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
     * Helper class for working with autoresponderItem and campaignItem
     */
    abstract class AutoresponderAndCampaignItemsUtil
    {
        public static $folder                   = null;

        public static $returnPath               = null;

        public static $ownerModelRelationName   = null;

        public static $emailMessageForeignKey   = null;

        public static $itemTableName            = null;

        public static $itemClass                = null;

        public static $personId                 = null;

        protected static $marketingListIdToSenderMapping    = array();

        public static function processDueItem(OwnedModel $item)
        {
            $time = microtime(true);
            assert('is_object($item)');
            $emailMessageId             = null;
            $itemId                     = $item->id;
            assert('static::$itemClass === "AutoresponderItem" || static::$itemClass === "CampaignItem"');
            $contact                    = static::resolveContact($item);
            $itemOwnerModel             = static::resolveItemOwnerModel($item);
            static::$personId           = $contact->getClassId('Person');

            if (static::skipMessage($contact, $itemOwnerModel))
            {
               static::createSkipActivity($itemId);
            }
            else
            {
                $marketingList              = $itemOwnerModel->marketingList;
                assert('is_object($marketingList)');
                assert('get_class($marketingList) === "MarketingList"');
                $textContent                = $itemOwnerModel->textContent;
                $htmlContent                = null;
                if ((static::$itemClass == 'CampaignItem' && $itemOwnerModel->supportsRichText) || (static::$itemClass == 'AutoresponderItem'))
                {
                    $htmlContent = $itemOwnerModel->htmlContent;
                }
                static::resolveContent($textContent, $htmlContent, $contact, $itemOwnerModel->enableTracking,
                                       (int)$itemId, static::$itemClass, (int)$marketingList->id);
                try
                {
                    $item->emailMessage   = static::resolveEmailMessage($textContent, $htmlContent, $itemOwnerModel,
                                                                        $contact, $marketingList, $itemId);
                }
                catch (MissingRecipientsForEmailMessageException $e)
                {
                   static::createSkipActivity($itemId);
                }
            }
            //$marked = static::markItemAsProcessed($item);
            $marked = static::markItemAsProcessedWithSQL($itemId, $item->emailMessage->id);
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
            print(PHP_EOL . PHP_EOL . PHP_EOL );
            return $marked;
        }

        protected static function resolveContact(OwnedModel $item)
        {
            $contact                    = $item->contact;
            if (empty($contact) || $contact->id < 0)
            {
                throw new NotFoundException();
            }
            return $contact;
        }

        protected static function resolveItemOwnerModel(OwnedModel $item)
        {
            $itemOwnerModel             = $item->{static::$ownerModelRelationName};
            assert('is_object($itemOwnerModel)');
            assert('get_class($itemOwnerModel) === "Autoresponder" || get_class($itemOwnerModel) === "Campaign"');
            return $itemOwnerModel;
        }

        protected static function skipMessage(Contact $contact, Item $itemOwnerModel)
        {
            return ($contact->primaryEmail->optOut ||
                // TODO: @Shoaibi: Critical0: We could use SQL for getByMarketingListIdContactIdandUnsubscribed to save further performance here.
                (get_class($itemOwnerModel) === "Campaign" && MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed(
                        $itemOwnerModel->marketingList->id,
                        $contact->id,
                        true) != false));
        }

        protected static function createSkipActivity($itemId)
        {
            $activityClass  = static::$itemClass . 'Activity';
            $type           = $activityClass::TYPE_SKIP;
            $activityClass::createNewActivity($type, $itemId, static::$personId);
        }

        protected static function resolveContent(& $textContent, & $htmlContent, Contact $contact,
                                                            $enableTracking, $modelId, $modelType, $marketingListId)
        {
            $time = microtime(true);
            assert('is_int($modelId)');
            assert('is_int($marketingListId)');
            static::resolveContentForGlobalFooter($textContent, $htmlContent);
            static::resolveContentsForMergeTags($textContent, $htmlContent, $contact,
                                                $marketingListId, $modelId, $modelType);
            static::resolveContentForTracking($textContent, $htmlContent, $enableTracking, $modelId,
                                                $modelType);
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        public static function resolveContentsForMergeTags(& $textContent, & $htmlContent, Contact $contact,
                                                            $marketingListId, $modelId, $modelType)
        {
            $time = microtime(true);
            static::resolveContentForMergeTags($textContent, $contact, false, $marketingListId, $modelId, $modelType);
            static::resolveContentForMergeTags($htmlContent, $contact, true, $marketingListId, $modelId, $modelType);
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        protected static function resolveContentForMergeTags(& $content, Contact $contact, $isHtmlContent,
                                                                $marketingListId, $modelId, $modelType)
        {
            $time = microtime(true);
            // TODO: @Shoaibi/@Jason: Low: we might add support for language
            $language               = null;
            $errorOnFirstMissing    = true;
            $templateType           = EmailTemplate::TYPE_CONTACT;
            $invalidTags            = array();
            $params                 = GlobalMarketingFooterUtil::resolveFooterMergeTagsArray(static::$personId, $marketingListId,
                                                                                            $modelId, $modelType, true,
                                                                                            false, $isHtmlContent);
            $mergeTagsUtil          = MergeTagsUtilFactory::make($templateType, $language, $content);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags($contact,
                                                                            $invalidTags,
                                                                            $language,
                                                                            $errorOnFirstMissing,
                                                                            $params);
            if ($resolvedContent === false)
            {
                throw new NotSupportedException(Zurmo::t('EmailTemplatesModule', 'Provided content contains few invalid merge tags.'));
            }
            $content    = $resolvedContent;
            print(PHP_EOL . __FUNCTION__ . '/isHtml=' . intval($isHtmlContent) . ': ' . (microtime(true) - $time));
            //echo PHP_EOL . $content . PHP_EOL;
        }

        protected static function resolveContentForGlobalFooter(& $textContent, & $htmlContent)
        {
            $time = microtime(true);
            if (!empty($textContent))
            {
                $plain = microtime(true);
                GlobalMarketingFooterUtil::resolveContentGlobalFooter($textContent, false);
                print(PHP_EOL . "GlobalMarketingFooterUtil.resolveContentGlobalFooter/PlainText: " . (microtime(true) - $plain));
            }
            if (!empty($htmlContent))
            {
                $rich = microtime(true);
                GlobalMarketingFooterUtil::resolveContentGlobalFooter($htmlContent, true);
                print(PHP_EOL . "GlobalMarketingFooterUtil.resolveContentGlobalFooter/Html: " . (microtime(true) - $rich));
            }
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        protected static function resolveContentForTracking(& $textContent, & $htmlContent, $enableTracking, $modelId,
                                                            $modelType)
        {
            $time = microtime(true);
            if (!empty($textContent))
            {
                $plain = microtime(true);
                ContentTrackingUtil::resolveContentForTracking($enableTracking, $textContent, $modelId, $modelType,
                                                            static::$personId, false);
                print(PHP_EOL . "ContentTrackingUtil.resolveContentForTracking/PlainText: " . (microtime(true) - $plain));
            }
            if (!empty($htmlContent))
            {
                $rich = microtime(true);
                ContentTrackingUtil::resolveContentForTracking($enableTracking, $htmlContent, $modelId, $modelType,
                                                            static::$personId, true);
                print(PHP_EOL . "ContentTrackingUtil.resolveContentForTracking/Html: " . (microtime(true) - $rich));
            }
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        protected static function resolveEmailMessage($textContent, $htmlContent, Item $itemOwnerModel,
                                                    Contact $contact, MarketingList $marketingList, $itemId)
        {
            $time = microtime(true);
            $emailMessage                       = new EmailMessage();
            $cTime = microtime(true);
            $emailMessage->owner                = $marketingList->owner;
            print(PHP_EOL . "Resolving owner for emailMessage: " . (microtime(true) - $cTime));
            $emailMessage->subject              = $itemOwnerModel->subject;
            $emailContent                       = new EmailMessageContent();
            $emailContent->textContent          = $textContent;
            $emailContent->htmlContent          = $htmlContent;
            $emailMessage->content              = $emailContent;
            print(PHP_EOL . "emailMessage population before sender : " . (microtime(true) - $time));
            $emailMessage->sender               = static::resolveSender($marketingList, $itemOwnerModel);
            static::resolveRecipient($emailMessage, $contact);
            static::resolveAttachments($emailMessage, $itemOwnerModel);
            static::resolveHeaders($emailMessage, $itemId);
            if ($emailMessage->recipients->count() == 0)
            {
                throw new MissingRecipientsForEmailMessageException();
            }
            $cTime = microtime(true);
            $emailMessage->folder               = static::$folder;
            print(PHP_EOL . "emailMessage population with folder: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            Yii::app()->emailHelper->send($emailMessage, true, false);
            print(PHP_EOL . "emailMessage sending: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            $explicitReadWriteModelPermissions  = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
            print(PHP_EOL . "ExplicitReadWriteModelPermissionsUtil.makeBySecurableItem: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($emailMessage,
                                                                                    $explicitReadWriteModelPermissions);
            print(PHP_EOL . "ExplicitReadWriteModelPermissionsUtil.resolveExplicitReadWriteModelPermissions: " . (microtime(true) - $cTime));
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $emailMessage;
        }

        protected static function resolveSender(MarketingList $marketingList, $itemOwnerModel)
        {
            $time   = microtime(true);
            $sender = null;
            if (isset(static::$marketingListIdToSenderMapping[$marketingList->id]))
            {
                $sender = static::$marketingListIdToSenderMapping[$marketingList->id];
            }
            else
            {
                $sender = new EmailMessageSender();
                if (get_class($itemOwnerModel) == 'Campaign') {
                    $sender->fromAddress = $itemOwnerModel->fromAddress;
                    $sender->fromName = $itemOwnerModel->fromName;
                } else
                {
                    if (!empty($marketingList->fromName) && !empty($marketingList->fromAddress)) {
                        $sender->fromAddress = $marketingList->fromAddress;
                        $sender->fromName = $marketingList->fromName;
                    } else {
                        $userToSendMessagesFrom = BaseControlUserConfigUtil::getUserToRunAs();
                        $sender->fromAddress = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
                        $sender->fromName = strval($userToSendMessagesFrom);
                    }
                }
                static::$marketingListIdToSenderMapping[$marketingList->id] = $sender;
            }
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $sender;
        }

        protected static function resolveRecipient(EmailMessage $emailMessage, Contact $contact)
        {
            $time = microtime(true);
            if ($contact->primaryEmail->emailAddress != null)
            {
                $recipient                  = new EmailMessageRecipient();
                $recipient->toAddress       = $contact->primaryEmail->emailAddress;
                $recipient->toName          = strval($contact);
                $recipient->type            = EmailMessageRecipient::TYPE_TO;
                $recipient->personsOrAccounts->add($contact);
                $emailMessage->recipients->add($recipient);
            }
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        protected static function resolveAttachments(EmailMessage $emailMessage, Item $itemOwnerModel)
        {
            $time = microtime(true);
            if (!empty($itemOwnerModel->files))
            {
                foreach ($itemOwnerModel->files as $file)
                {
                    $emailMessageFile   = FileModelUtil::makeByFileModel($file);
                    $emailMessage->files->add($emailMessageFile);
                }
            }
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        protected static function resolveHeaders(EmailMessage $emailMessage, $zurmoItemId)
        {
            $time = microtime(true);
            $zurmoItemClass     = static::$itemClass;
            $zurmoPersonId      = static::$personId;
            $headers            = compact('zurmoItemId', 'zurmoItemClass', 'zurmoPersonId');
            if (static::$returnPath)
            {
                $headers['Return-Path'] = static::$returnPath;
            }
            $emailMessage->headers  = serialize($headers);
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        protected static function markItemAsProcessed(OwnedModel $item)
        {
            $time   = microtime(true);
            $item->processed    = 1;
            if (!$item->unrestrictedSave())
            {
                throw new FailedToSaveModelException();
            }
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return true;
        }

        protected static function markItemAsProcessedWithSQL($itemId, $emailMessageId = null)
        {
            $time   = microtime(true);
            $class                  = static::$itemClass;
            $emailMessageForeignKey = RedBeanModel::getForeignKeyName($class, 'emailMessage');
            $itemTableName          = $class::getTableName();
            $sql                    = "UPDATE " . DatabaseCompatibilityUtil::quoteString($itemTableName);
            $sql                    .= " SET " . DatabaseCompatibilityUtil::quoteString('processed') . ' = 1';
            if ($emailMessageId)
            {
                $sql .= ", " . DatabaseCompatibilityUtil::quoteString($emailMessageForeignKey);
                $sql .= " = ${emailMessageId}";
            }
            $sql                    .= " WHERE " . DatabaseCompatibilityUtil::quoteString('id') . " = ${itemId};";
            $effectedRows           = ZurmoRedBean::exec($sql);
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return ($effectedRows == 1);
        }
    }
?>