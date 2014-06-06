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
        public static function processDueItem(OwnedModel $item)
        {
            $time = microtime(true);
            assert('is_object($item)');
            $emailMessageId             = null;
            $itemId                     = $item->id;
            $itemClass                  = get_class($item);
            assert('$itemClass === "AutoresponderItem" || $itemClass === "CampaignItem"');
            $contact                    = $item->contact;
            if (empty($contact) || $contact->id < 0)
            {
                throw new NotFoundException();
            }
            $ownerModelRelationName     = static::resolveItemOwnerModelRelationName($itemClass);
            $itemOwnerModel             = $item->$ownerModelRelationName;
            assert('is_object($itemOwnerModel)');
            assert('get_class($itemOwnerModel) === "Autoresponder" || get_class($itemOwnerModel) === "Campaign"');
            if ($contact->primaryEmail->optOut ||
                // TODO: @Shoaibi: Critical0: We could use SQL for getByMarketingListIdContactIdandUnsubscribed to save further performance here.
               (get_class($itemOwnerModel) === "Campaign" && MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed(
                                                                                $itemOwnerModel->marketingList->id,
                                                                                $contact->id,
                                                                                true) != false))
            {
                $activityClass  = $itemClass . 'Activity';
                $personId       = $contact->getClassId('Person');
                $type           = $activityClass::TYPE_SKIP;
                $activityClass::createNewActivity($type, $itemId, $personId);
            }
            else
            {
                $marketingList              = $itemOwnerModel->marketingList;
                assert('is_object($marketingList)');
                assert('get_class($marketingList) === "MarketingList"');
                $textContent                = $itemOwnerModel->textContent;
                $htmlContent                = null;
                if (($itemClass == 'CampaignItem' && $itemOwnerModel->supportsRichText) || ($itemClass == 'AutoresponderItem'))
                {
                    $htmlContent = $itemOwnerModel->htmlContent;
                }
                static::resolveContent($textContent, $htmlContent, $contact, $itemOwnerModel->enableTracking,
                                       (int)$itemId, $itemClass, (int)$marketingList->id);
                try
                {
                    $emailMessage   = static::resolveEmailMessage($textContent, $htmlContent, $itemOwnerModel,
                                                                        $contact, $marketingList, $itemId, $itemClass);
                    $emailMessageId = $emailMessage->id;
                }
                catch (MissingRecipientsForEmailMessageException $e)
                {
                    $activityClass  = $itemClass . 'Activity';
                    $personId       = $contact->getClassId('Person');
                    $type           = $activityClass::TYPE_SKIP;
                    $activityClass::createNewActivity($type, $itemId, $personId);
                }
            }
            static::markItemAsProcessed($itemId, $itemClass, $emailMessageId);
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        protected static function resolveContent(& $textContent, & $htmlContent, Contact $contact,
                                                            $enableTracking, $modelId, $modelType, $marketingListId)
        {
            $time = microtime(true);
            assert('is_int($modelId)');
            assert('is_int($marketingListId)');
            $personId                   = $contact->getClassId('Person');
            static::resolveContentForGlobalFooter($textContent, $htmlContent);
            static::resolveContentsForMergeTags($textContent, $htmlContent, $contact, $personId,
                                                $marketingListId, $modelId, $modelType);
            static::resolveContentForTracking($textContent, $htmlContent, $enableTracking, $modelId,
                                                $modelType, $personId);
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        public static function resolveContentsForMergeTags(& $textContent, & $htmlContent, Contact $contact, $personId,
                                                            $marketingListId, $modelId, $modelType)
        {
            $time = microtime(true);
            static::resolveContentForMergeTags($textContent, $contact, false, $personId, $marketingListId, $modelId, $modelType);
            static::resolveContentForMergeTags($htmlContent, $contact, true, $personId, $marketingListId, $modelId, $modelType);
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        protected static function resolveContentForMergeTags(& $content, Contact $contact, $isHtmlContent, $personId,
                                                                $marketingListId, $modelId, $modelType)
        {
            $time = microtime(true);
            // TODO: @Shoaibi/@Jason: Low: we might add support for language
            $language               = null;
            $errorOnFirstMissing    = true;
            $templateType           = EmailTemplate::TYPE_CONTACT;
            $invalidTags            = array();
            $textMergeTagsUtil      = MergeTagsUtilFactory::make($templateType, $language, $content);
            $params                 = GlobalMarketingFooterUtil::resolveFooterMergeTagsArray($personId, $marketingListId,
                                                                                            $modelId, $modelType, true,
                                                                                            false, $isHtmlContent);
            $resolvedContent        = $textMergeTagsUtil->resolveMergeTags($contact,
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
        }

        protected static function resolveContentForGlobalFooter(& $textContent, & $htmlContent)
        {
            $time = microtime(true);
            if (!empty($textContent))
            {
                $plain = microtime(true);
                GlobalMarketingFooterUtil::resolveContentGlobalFooter($textContent, false);
                print(PHP_EOL . "GlobalMarketingFooterUtil::resolveContentGlobalFooter/PlainText: " . (microtime(true) - $plain));
            }
            if (!empty($htmlContent))
            {
                $rich = microtime(true);
                GlobalMarketingFooterUtil::resolveContentGlobalFooter($htmlContent, true);
                print(PHP_EOL . "GlobalMarketingFooterUtil::resolveContentGlobalFooter/Html: " . (microtime(true) - $rich));
            }
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        protected static function resolveContentForTracking(& $textContent, & $htmlContent, $enableTracking, $modelId,
                                                            $modelType, $personId)
        {
            $time = microtime(true);
            if (!empty($textContent))
            {
                $plain = microtime(true);
                ContentTrackingUtil::resolveContentForTracking($enableTracking, $textContent, $modelId, $modelType,
                                                            $personId, false);
                print(PHP_EOL . "ContentTrackingUtil::resolveContentForTracking/PlainText: " . (microtime(true) - $plain));
            }
            if (!empty($htmlContent))
            {
                $rich = microtime(true);
                ContentTrackingUtil::resolveContentForTracking($enableTracking, $htmlContent, $modelId, $modelType,
                                                            $personId, true);
                print(PHP_EOL . "ContentTrackingUtil::resolveContentForTracking/Html: " . (microtime(true) - $rich));
            }
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        protected static function resolveEmailMessage($textContent, $htmlContent, Item $itemOwnerModel,
                                                    Contact $contact, MarketingList $marketingList, $itemId, $itemClass)
        {
            $time = microtime(true);
            $emailMessage                       = new EmailMessage();
            $emailMessage->subject              = $itemOwnerModel->subject;
            $emailContent                       = new EmailMessageContent();
            $emailContent->textContent          = $textContent;
            $emailContent->htmlContent          = $htmlContent;
            $emailMessage->content              = $emailContent;
            print(PHP_EOL . "emailMessage population before sender : " . (microtime(true) - $time));
            $emailMessage->sender               = static::resolveSender($marketingList, $itemOwnerModel);
            static::resolveRecipient($emailMessage, $contact);
            static::resolveAttachments($emailMessage, $itemOwnerModel);
            static::resolveHeaders($emailMessage, $itemId, $itemClass, $contact->getClassId('Person'));
            if ($emailMessage->recipients->count() == 0)
            {
                throw new MissingRecipientsForEmailMessageException();
            }
            $cTime = microtime(true);
            $boxName                            = static::resolveEmailBoxName(get_class($itemOwnerModel));
            print(PHP_EOL . "Resolving boxName: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            // TODO: @Shoaibi: Critical0: This can come from job directly.
            // Say if there are X items and this takes Y seconds while job processes X/2 items per run
            // then we would be spending 2Y seconds on this instead of X*Y.
            $box                                = EmailBox::resolveAndGetByName($boxName);
            print(PHP_EOL . "Resolving box: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            // TODO: @Shoaibi: Critical0: This can come from job directly.
            // Say if there are X items and this takes Y seconds while job processes X/2 items per run
            // then we would be spending 2Y seconds on this instead of X*Y.
            $emailMessage->folder               = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            print(PHP_EOL . "emailMessage population with folder: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            // TODO: @Shoaibi: Critical0: This should be refactored to pure sql
            ZurmoRedBean::exec('SELECT "BEFORE SEND, BEFORE SAVE";');
            if (!$emailMessage->save())
            {
                throw new FailedToSaveModelException("Unable to save EmailMessage");
            }
            ZurmoRedBean::exec('SELECT "BEFORE SEND, AFTER SAVE";');
            print(PHP_EOL . "Saving Email Message before sending: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            $emailMessage   = EmailMessage::getById($emailMessage->id);
            print(PHP_EOL . "Getting Email Message: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            Yii::app()->emailHelper->send($emailMessage, true);
            print(PHP_EOL . "emailMessage sending: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            $emailMessage->owner                = $marketingList->owner;
            print(PHP_EOL . "Resolving owner for emailMessage: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            $explicitReadWriteModelPermissions  = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
            print(PHP_EOL . "ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($emailMessage,
                                                                                    $explicitReadWriteModelPermissions);
            print(PHP_EOL . "ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            // TODO: @Shoaibi: Critical0: This should be refactored to pure sql
            ZurmoRedBean::exec('SELECT "AFTER SEND, BEFORE SAVE";');

            if (!$emailMessage->save())
            {
                throw new FailedToSaveModelException("Unable to save EmailMessage");
            }
            ZurmoRedBean::exec('SELECT "AFTER SEND, AFTER SAVE";');
            print(PHP_EOL . "Saving Email Message: " . (microtime(true) - $cTime));
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $emailMessage;
        }

        protected static function resolveSender(MarketingList $marketingList, $itemOwnerModel)
        {
            $time = microtime(true);
            $sender                         = new EmailMessageSender();
            if (get_class($itemOwnerModel) == 'Campaign')
            {
                $sender->fromAddress        = $itemOwnerModel->fromAddress;
                $sender->fromName           = $itemOwnerModel->fromName;
                print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
                return $sender;
            }
            if (!empty($marketingList->fromName) && !empty($marketingList->fromAddress))
            {
                $sender->fromAddress        = $marketingList->fromAddress;
                $sender->fromName           = $marketingList->fromName;
            }
            else
            {
                // TODO: @Shoaibi: Critical0: This can come from job directly.
                // Say if there are X items and this takes Y seconds while job processes X/2 items per run
                // then we would be spending 2Y seconds on this instead of X*Y.
                $userToSendMessagesFrom         = BaseControlUserConfigUtil::getUserToRunAs();
                $sender->fromAddress            = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
                $sender->fromName               = strval($userToSendMessagesFrom);
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

        protected static function resolveHeaders(EmailMessage $emailMessage, $zurmoItemId, $zurmoItemClass, $zurmoPersonId)
        {
            $time = microtime(true);
            $headers            = compact('zurmoItemId', 'zurmoItemClass', 'zurmoPersonId');
            $returnPathHeader   = static::resolveReturnPathHeaderValue();
            if ($returnPathHeader)
            {
                $headers['Return-Path'] = $returnPathHeader;
            }
            $emailMessage->headers  = serialize($headers);
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        protected static function resolveReturnPathHeaderValue()
        {
            $time = microtime(true);
            // TODO: @Shoaibi: Critical0: This can come from job directly.
            // Say if there are X items and this takes Y seconds while job processes X/2 items per run
            // then we would be spending 2Y seconds on this instead of X*Y.
            $returnPath = ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'bounceReturnPath');
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $returnPath;
        }

        protected static function markItemAsProcessed($itemId, $itemClass, $emailMessageId = null)
        {
            $time   = microtime(true);
            // TODO: @Shoaibi: Critical0: This can come from job directly.
            // Say if there are X items and this takes Y seconds while job processes X/2 items per run
            // then we would be spending 2Y seconds on this instead of X*Y.
            $emailMessageForeignKey = RedBeanModel::getForeignKeyName($itemClass, 'emailMessage');
            // TODO: @Shoaibi: Critical0: This can come from job directly.
            // Say if there are X items and this takes Y seconds while job processes X/2 items per run
            // then we would be spending 2Y seconds on this instead of X*Y.
            $itemTableName          = $itemClass::getTableName();

            $sql                    = "UPDATE " . DatabaseCompatibilityUtil::quoteString($itemTableName);
            $sql                    .= " SET " . DatabaseCompatibilityUtil::quoteString('processed') . ' = 1';
            if ($emailMessageId)
            {
                $sql .= ", " . DatabaseCompatibilityUtil::quoteString($emailMessageForeignKey);
                $sql .= " = ${emailMessageId}";
            }
            $sql                    .= " WHERE " . DatabaseCompatibilityUtil::quoteString('id') . " = ${itemId};";
            ZurmoRedBean::exec($sql);
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return true;
        }

        protected static function resolveItemOwnerModelRelationName($itemClass)
        {
            // TODO: @Shoaibi: Critical0: This can come from job directly.
            // Say if there are X items and this takes Y seconds while job processes X/2 items per run
            // then we would be spending 2Y seconds on this instead of X*Y.
            $relationName   = 'campaign';
            if ($itemClass == 'AutoresponderItem')
            {
                $relationName = 'autoresponder';
            }
            return $relationName;
        }

        protected static function resolveEmailBoxName($itemOwnerModelClassName)
        {
            // TODO: @Shoaibi: Critical0: This can come from job directly.
            // Say if there are X items and this takes Y seconds while job processes X/2 items per run
            // then we would be spending 2Y seconds on this instead of X*Y.
            $time   = microtime(true);
            $box    = EmailBox::CAMPAIGNS_NAME;
            if ($itemOwnerModelClassName == "Autoresponder")
            {
                $box = EmailBox::AUTORESPONDERS_NAME;
            }
            print(PHP_EOL . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $box;
        }
    }
?>