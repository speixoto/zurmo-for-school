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

    /*
     * Base class that defines Merge Tag delimiters, extracts them, and provides methods for converting them to values.
     */
    class MergeTagsUtil
    {
        const TAG_PREFIX            = '[[';

        const TAG_SUFFIX            = ']]';

        const PROPERTY_DELIMITER    = '__';

        const TIME_DELIMITER        = '%';

        const CAPITAL_DELIMITER     = '^';

        protected $mergeTags;

        protected $content;

        protected $language;

        protected static function resolveUniqueMergeTags(& $mergeTags, $key)
        {
            $mergeTags = array_unique($mergeTags);
        }

        protected static function resolveFullyQualifiedMergeTagRegularExpression(& $value, $key)
        {
            $value = '/' . preg_quote($value) . '/';
        }

        public function __construct($language, $content) // TODO: @Shoaibi/@Jason probably change it to locale object
        {
            $this->language = $language;
            $this->content  = $content;
        }

        public function resolveMergeTagsArrayToAttributes($model, & $invalidTags = null, $language = null)
        {
            $language = ($language)? $language : $this->language;
            if (empty($this->mergeTags))
            {
                return false;
            }
            else
            {
                return MergeTagsToModelAttributesAdapter::resolveMergeTagsArrayToAttributesFromModel($this->mergeTags[1],
                                        $model, $invalidTags, $language);
            }
        }

        public function resolveMergeTags($model,& $invalidTags = null, $language = null)
        {
            if (!$this->extractMergeTagsPlaceHolders() ||
                    $this->resolveMergeTagsArrayToAttributes($model, $invalidTags, $language) &&
                    $this->resolveMergeTagsInTemplateToAttributes())
            {
                return $this->content;
            }
            else
            {
                return false;
            }
        }

        public function extractMergeTagsPlaceHolders()
        {
            // Current RE: /((WAS\%)?((\^|__)?([A-Z]))+)/
            $pattern = '/' . preg_quote(static::TAG_PREFIX) .
                '((WAS' . preg_quote(static::TIME_DELIMITER) . ')?' .
                '((' . preg_quote(static::CAPITAL_DELIMITER) . '|' .
                preg_quote(static::PROPERTY_DELIMITER) . ')?' .
                '([A-Z]))+)' .
                preg_quote(static::TAG_SUFFIX) .
                '/';
            // $this->mergeTags index 0 = with tag prefix and suffix, index 1 = without tag prefix and suffix
            $matchesCounts = preg_match_all($pattern, $this->content, $this->mergeTags);
            array_walk($this->mergeTags, 'static::resolveUniqueMergeTags');
            return $matchesCounts;
        }

        protected function resolveMergeTagsInTemplateToAttributes()
        {
            $resolvedMergeTagsCount     = 0;
            $mergeTags                  = $this->mergeTags[0];
            $attributes                 = array_values($this->mergeTags[1]);
            $this->resolveFullyQualifiedMergeTagsRegularExpression($mergeTags);
            $content                    = preg_replace($mergeTags, $attributes, $this->content, -1, $resolvedMergeTagsCount);
            $this->content              = (!empty($content))? $content : $this->content;
            return $resolvedMergeTagsCount;
        }

        protected function resolveFullyQualifiedMergeTagsRegularExpression(& $mergeTags)
        {
            array_walk($mergeTags, 'static::resolveFullyQualifiedMergeTagRegularExpression');
        }
    }
?>