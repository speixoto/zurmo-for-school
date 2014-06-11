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
     * At places we intentionally use all lowercase variable names instead of camelCase to do easy
     * compact() on them and have them match column names in db on queries.
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
                if (static::supportsRichText($itemOwnerModel))
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
            print(PHP_EOL . __CLASS__ . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
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

        protected static function supportsRichText(Item $itemOwnerModel)
        {
            return ((static::$itemClass == 'CampaignItem' && $itemOwnerModel->supportsRichText) ||
                        (static::$itemClass == 'AutoresponderItem'));
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
            GlobalMarketingFooterUtil::resolveContentsForGlobalFooter($textContent, $htmlContent);
            static::resolveContentsForMergeTags($textContent, $htmlContent, $contact,
                                                $marketingListId, $modelId, $modelType);
            ContentTrackingUtil::resolveContentsForTracking($textContent, $htmlContent, $enableTracking,
                                                            $modelId, $modelType, static::$personId);
            print(PHP_EOL . __CLASS__ . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
        }

        public static function resolveContentsForMergeTags(& $textContent, & $htmlContent, Contact $contact,
                                                            $marketingListId, $modelId, $modelType)
        {
            $time = microtime(true);
            static::resolveContentForMergeTags($textContent, $contact, false, $marketingListId, $modelId, $modelType);
            static::resolveContentForMergeTags($htmlContent, $contact, true, $marketingListId, $modelId, $modelType);
            print(PHP_EOL . __CLASS__ . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
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
            print(PHP_EOL . __CLASS__ . '.' . __FUNCTION__ . '/isHtml=' . intval($isHtmlContent) . ': ' . (microtime(true) - $time));
            //echo PHP_EOL . $content . PHP_EOL;
        }

        protected static function resolveEmailMessage($textContent, $htmlContent, Item $itemOwnerModel,
                                                    Contact $contact, MarketingList $marketingList, $itemId)
        {
            $time = microtime(true);
            $emailMessage   = static::saveEmailMessage($textContent, $htmlContent, $itemOwnerModel,
                                                        $contact, $marketingList, $itemId);
            $cTime = microtime(true);
            ZurmoRedBean::exec("SELECT 'SHOAIBI: Sending emailMessage'");
            Yii::app()->emailHelper->send($emailMessage, true, false);
            ZurmoRedBean::exec("SELECT 'SHOAIBI: emailMessageSent'");
            print(PHP_EOL . __CLASS__ . "/emailMessage sending: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            $explicitReadWriteModelPermissions  = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
            print(PHP_EOL . __CLASS__ . "/ExplicitReadWriteModelPermissionsUtil.makeBySecurableItem: " . (microtime(true) - $cTime));
            $cTime = microtime(true);
            ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($emailMessage,
                                                                                    $explicitReadWriteModelPermissions);
            print(PHP_EOL . __CLASS__ . "/ExplicitReadWriteModelPermissionsUtil.resolveExplicitReadWriteModelPermissions: " . (microtime(true) - $cTime));
            print(PHP_EOL . __CLASS__ . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $emailMessage;
        }

        protected static function saveEmailMessage($textContent, $htmlContent, Item $itemOwnerModel,
                                                    Contact $contact, MarketingList $marketingList, $itemId)
        {
            $time = microtime(true);
            AutoresponderAndCampaignItemsEmailMessageUtil::$itemClass   = static::$itemClass;
            AutoresponderAndCampaignItemsEmailMessageUtil::$personId    = static::$personId;
            AutoresponderAndCampaignItemsEmailMessageUtil::$returnPath  = static::$returnPath;
            $emailMessage   = AutoresponderAndCampaignItemsEmailMessageUtil::resolveAndSaveEmailMessage($textContent,
                                                                                                    $htmlContent,
                                                                                                    $itemOwnerModel,
                                                                                                    $contact,
                                                                                                    $marketingList,
                                                                                                    $itemId,
                                                                                                    static::$folder->id);
            print(PHP_EOL . __CLASS__ . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return $emailMessage;
        }

        protected static function markItemAsProcessed(OwnedModel $item)
        {
            $time   = microtime(true);
            $item->processed    = 1;
            if (!$item->unrestrictedSave(false))
            {
                throw new FailedToSaveModelException();
            }
            print(PHP_EOL . __CLASS__ . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return true;
        }

        protected static function markItemAsProcessedWithSQL($itemId, $emailMessageId = null)
        {
            $time   = microtime(true);
            $sql                    = "UPDATE " . DatabaseCompatibilityUtil::quoteString(static::$itemTableName);
            $sql                    .= " SET " . DatabaseCompatibilityUtil::quoteString('processed') . ' = 1';
            if ($emailMessageId)
            {
                $sql .= ", " . DatabaseCompatibilityUtil::quoteString(static::$emailMessageForeignKey);
                $sql .= " = ${emailMessageId}";
            }
            $sql                    .= " WHERE " . DatabaseCompatibilityUtil::quoteString('id') . " = ${itemId};";
            $effectedRows           = ZurmoRedBean::exec($sql);
            print(PHP_EOL . __CLASS__ . '.' . __FUNCTION__ . ': ' . (microtime(true) - $time));
            return ($effectedRows == 1);
        }
    }
?>