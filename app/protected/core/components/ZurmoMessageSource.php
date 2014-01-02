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
     * Represents a message source that stores translated messages in database.
     *
     * The ZurmoMessageSource::installSchema() method must be called to create
     * the tables with required indexes
     */
    class ZurmoMessageSource extends CDbMessageSource
    {
        const CACHE_KEY_PREFIX = 'ZurmoMessageSource';

        /*
         * Fallback for the Default category
         */
        public function translate($category, $message, $language = null)
        {
            $translation = parent::translate($category, $message, $language);
            if ($translation == $message)
            {
                $translation = parent::translate('Default', $message, $language);
            }

            return $translation;
        }

        public static function clearCache($category, $languageCode)
        {
            assert('is_string($category)');
            assert('is_string($languageCode)');
            GeneralCache::forgetEntry(self::getMessageSourceCacheIdentifier($category, $languageCode));
        }

        /**
         * Override of the parent method because of problems with Yii's default cache
         * @see CDbMessageSource::loadMessages()
         * @param string $category
         * @param string $languageCode
         * @return array $messages
         */
        protected function loadMessages($category, $languageCode)
        {
            assert('is_string($category)');
            assert('is_string($languageCode)');
            try
            {
                // not using default value to save cpu cycles on requests that follow the first exception.
                $messages = GeneralCache::getEntry(self::getMessageSourceCacheIdentifier($category, $languageCode));
            }
            catch (NotFoundException $e)
            {
                $messages = $this->loadMessagesFromDb($category, $languageCode);
                GeneralCache::cacheEntry(self::getMessageSourceCacheIdentifier($category, $languageCode), $messages);
            }
            return $messages;
        }

        /**
         * Override of the parent method using RedBean.
         * @param $category
         * @param $languageCode
         * @return array
         */
        protected function loadMessagesFromDb($category, $languageCode)
        {
            assert('is_string($category)');
            assert('is_string($languageCode)');
            $sourceTableName   = MessageSource::getTableName();
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('MessageTranslation');
            $joinTablesAdapter->addFromTableAndGetAliasName($sourceTableName, "{$sourceTableName}_id");
            $where             =  " messagesource.`category` = '$category' AND"
                                . " messagetranslation.`language` = '$languageCode' ";

            $beans    = MessageTranslation::getSubset($joinTablesAdapter, null, null, $where);
            $messages = array();
            foreach ($beans as $bean)
            {
                $messages[$bean->messagesource->source] = $bean->translation;
            }
            return $messages;
        }

        protected static function getMessageSourceCacheIdentifier($category, $languageCode)
        {
            assert('is_string($category)');
            assert('is_string($languageCode)');
            return self::CACHE_KEY_PREFIX . '.messages.' . $category . '.' . $languageCode;
        }
    }
?>