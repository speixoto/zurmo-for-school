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
     * Represents a message source that stores translated messages in database.
     *
     * The ZurmoMessageSource::installSchema() method must be called to create
     * the tables with required indexes
     */
    class ZurmoMessageSource extends CDbMessageSource
    {
        const CACHE_KEY_PREFIX='ZurmoMessageSource';

        public static function installSchema() {
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();

                $source_bean = R::dispense('messagesource');
                $source_bean->category = 'Default';
                $source_bean->setMeta('hint', array('source' => 'blob'));
                $source_bean->source = 'x';
                R::store($source_bean);
                R::exec('ALTER TABLE `messagesource`
                        ADD  UNIQUE INDEX `source_category_Index`
                        (`category`,`source`(767));');

                $translation_bean = R::dispense('messagetranslation');
                $translation_bean->language = 'x';
                $translation_bean->translation = 'x';
                $translation_bean->setMeta('hint', array('translation' => 'blob'));
                $translation_bean->messagesource = $source_bean;
                R::store($translation_bean);
                R::exec('ALTER TABLE `messagetranslation`
                        ADD  UNIQUE INDEX `source_language_translation_Index`
                        (`messagesource_id`,`language`,`translation`(767));');

                R::wipe('messagesource');
                R::wipe('messagetranslation');
            }
        }

        protected function loadMessagesFromDb($category,$language)
        {
            $sql = <<<EOD
SELECT ms.source, mt.translation
FROM messagesource as ms, messagetranslation as mt
WHERE
    mt.messagesource_id = ms.id
    AND ms.category = :category
    AND mt.language = :language
EOD;
            $rows = R::getAll($sql,
                              array(
                                    ':category' => $category,
                                    ':language' => $language
                                    ));

            $messages = array();
            foreach ($rows as $row) {
                $messages[$row['source']] = $row['translation'];
            }

            return $messages;
        }
    }
?>