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
     * This is a general cache helper that utilizes both php caching and memcaching if available. Utilized for
     * caching requirements that are simple in/out of a serialized array or string of information.
     */
    abstract class ZurmoCache
    {
        protected static $cacheIncrementValueVariableName = 'CacheIncrementValue';

        protected static $additionalStringForCachePrefix = '';

        public static function getCachePrefix($identifier)
        {
            if (self::isIdentifierCacheIncrementValueName(static::$cacheType, $identifier))
            {
                $prefix = ZURMO_TOKEN . '_' . static::$cacheType;
            }
            else
            {
                $cacheIncrementValue = self::getCacheIncrementValue(static::$cacheType);
                $prefix = ZURMO_TOKEN . '_' . $cacheIncrementValue . '_' . static::$cacheType;
            }

            if (self::getAdditionalStringForCachePrefix() != '')
            {
                $prefix = self::getAdditionalStringForCachePrefix() . '_' . $prefix;
            }

            return $prefix;
        }

        protected static function getCacheIncrementValue($cacheType)
        {
            try
            {
                $cacheIncrementValue = GeneralCache::getEntry(static::$cacheIncrementValueVariableName . $cacheType);
            }
            catch (NotFoundException $e)
            {
                $cacheIncrementValue = 0;
                self::setCacheIncrementValue($cacheType, $cacheIncrementValue);
            }
            return $cacheIncrementValue;
        }

        protected static function setCacheIncrementValue($cacheType, $value)
        {
            GeneralCache::cacheEntry(self::$cacheIncrementValueVariableName . $cacheType, $value);
        }

        protected static function incrementCacheIncrementValue($cacheType)
        {
            $currentCacheIncrementValue = self::getCacheIncrementValue($cacheType);
            $currentCacheIncrementValue++;
            self::setCacheIncrementValue($cacheType, $currentCacheIncrementValue);
        }

        protected static function isIdentifierCacheIncrementValueName($cacheType, $identifier)
        {
            if ($identifier == self::$cacheIncrementValueVariableName . $cacheType)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function setAdditionalStringForCachePrefix($prefix = '')
        {
            self::$additionalStringForCachePrefix = $prefix;
        }

        public static function getAdditionalStringForCachePrefix()
        {
            return self::$additionalStringForCachePrefix;
        }
    }
?>
