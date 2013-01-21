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

    // This is so that accessing a securable item several times, specifically
    // setting several of its attributes in succession, will not recalculate
    // the user's permissions every time. Changes to permissions during a
    // request may be ignored, then will be picked up during the next request.
    // Permissions are cached at three levels, in php - they will be remembered
    // during the request, in memcache - they will be remembered across requests,
    // in the database - they will be remembered across requests even if
    // memcache doesn't have them.
    class PermissionsCache extends ZurmoCache
    {
        private static $securableItemToPermitableToCombinedPermissions = array();

        private static $namedSecurableItemActualPermissions = array();

        public static $cacheType = 'P:';

        public static function getCombinedPermissions(SecurableItem $securableItem, Permitable $permitable)
        {
            if ($securableItem->getClassId('SecurableItem') == 0 ||
                $permitable   ->getClassId('Permitable')    == 0)
            {
                throw new NotFoundException();
            }

            $securableItemModelIdentifer = $securableItem->getModelIdentifier();
            $permitableModelIdentifier   = $permitable   ->getModelIdentifier();

            if (PHP_CACHING_ON)
            {
                if (isset(self::$securableItemToPermitableToCombinedPermissions
                                        [$securableItemModelIdentifer]
                                        [$permitableModelIdentifier]))
                {
                    return self::$securableItemToPermitableToCombinedPermissions
                                        [$securableItemModelIdentifer]
                                        [$permitableModelIdentifier];
                }
            }

            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($securableItemModelIdentifer, self::$cacheType);
                $serializedData = Yii::app()->cache->get($prefix . $securableItemModelIdentifer);
                if ($serializedData !== false)
                {
                    $permitablesCombinedPermissions = unserialize($serializedData);
                    assert('is_array($permitablesCombinedPermissions)');
                    if (isset($permitablesCombinedPermissions[$permitableModelIdentifier]))
                    {
                        $combinedPermissions = $permitablesCombinedPermissions[$permitableModelIdentifier];
                        if (PHP_CACHING_ON)
                        {
                            self::$securableItemToPermitableToCombinedPermissions
                                            [$securableItemModelIdentifer]
                                            [$permitableModelIdentifier] = $combinedPermissions;
                        }
                        return $combinedPermissions;
                    }
                }
            }

            // NOTE: the db level will get the permissions from the db level cache
            // when php asks for them to be calculated so it doesn't need to be done
            // explicity here.

            throw new NotFoundException();
        }

        public static function cacheCombinedPermissions(SecurableItem $securableItem, Permitable $permitable, $combinedPermissions)
        {
            assert('is_int($combinedPermissions) || ' .
                   'is_numeric($combinedPermissions[0]) && is_string($combinedPermissions[0])');

            if ($securableItem->getClassId('SecurableItem') == 0 ||
                $permitable   ->getClassId('Permitable')    == 0)
            {
                return;
            }

            $securableItemModelIdentifer = $securableItem->getModelIdentifier();
            $permitableModelIdentifier   = $permitable   ->getModelIdentifier();

            if (PHP_CACHING_ON)
            {
                self::$securableItemToPermitableToCombinedPermissions
                                        [$securableItemModelIdentifer]
                                        [$permitableModelIdentifier] = $combinedPermissions;
            }

            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($securableItemModelIdentifer, self::$cacheType);
                $permitablesCombinedPermissions = Yii::app()->cache->get($prefix . $securableItemModelIdentifer);
                if ($permitablesCombinedPermissions === false)
                {
                    $permitablesCombinedPermissions = array($permitableModelIdentifier => $combinedPermissions);
                    Yii::app()->cache->set($prefix . $securableItemModelIdentifer,
                                           serialize($permitablesCombinedPermissions));
                }
                else
                {
                    $permitablesCombinedPermissions = unserialize($permitablesCombinedPermissions);
                    assert('is_array($permitablesCombinedPermissions)');
                    $permitablesCombinedPermissions[$permitableModelIdentifier] = $combinedPermissions;
                    Yii::app()->cache->set($prefix . $securableItemModelIdentifer,
                                           serialize($permitablesCombinedPermissions));
                }
            }

            // NOTE: the db level caches the permissions when it calculates
            // them so php does not need to explicitly cache them here.
        }

        /**
         * Cache the actual permissions for a permitable against a named securable item.  The actual permissions against
         * a named securable item do not change very often making this cache useful to speed up performance.
         * @param string $namedSecurableItemName
         * @param object $permitable
         * @param array $actualPermissions
         */
        public static function cacheNamedSecurableItemActualPermissions($namedSecurableItemName, $permitable, $actualPermissions)
        {
            assert('is_string($namedSecurableItemName)');
            assert('$permitable instanceof Permitable');
            assert('is_array($actualPermissions)');
            $cacheKeyName = $namedSecurableItemName . get_class($permitable) . $permitable->id . 'ActualPermissions';
            if (PHP_CACHING_ON)
            {
                self::$namedSecurableItemActualPermissions[$cacheKeyName] = $actualPermissions;
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($cacheKeyName, self::$cacheType);
                Yii::app()->cache->set($prefix . $cacheKeyName, serialize($actualPermissions));
            }
        }

        /**
         * Given the name of a named securable item, return the cached entry if available.
         * @param string $namedSecurableItemName
         * @param object $permitable
         * @throws NotFoundException
         */
        public static function getNamedSecurableItemActualPermissions($namedSecurableItemName, $permitable)
        {
            assert('is_string($namedSecurableItemName)');
            assert('$permitable instanceof Permitable');
            $cacheKeyName = $namedSecurableItemName . get_class($permitable) . $permitable->id . 'ActualPermissions';
            if (PHP_CACHING_ON)
            {
                if (isset(self::$namedSecurableItemActualPermissions[$cacheKeyName]))
                {
                    return self::$namedSecurableItemActualPermissions[$cacheKeyName];
                }
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($cacheKeyName, self::$cacheType);
                $serializedData = Yii::app()->cache->get($prefix . $cacheKeyName);
                if ($serializedData !== false)
                {
                    $actualPermissions = unserialize($serializedData);
                    assert('is_array($actualPermissions)');
                    return $actualPermissions;
                }
            }
            throw new NotFoundException();
        }

        // The $forgetDbLevel cache is for testing.
        public static function forgetSecurableItem(SecurableItem $securableItem, $forgetDbLevelCache = true)
        {
            if ($securableItem->getClassId('SecurableItem') == 0)
            {
                return;
            }

            $securableItemModelIdentifer = $securableItem->getModelIdentifier();

            if (PHP_CACHING_ON)
            {
                self::$securableItemToPermitableToCombinedPermissions[$securableItemModelIdentifer] = array();
            }

            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($securableItemModelIdentifer, self::$cacheType);
                Yii::app()->cache->delete($prefix . $securableItemModelIdentifer);
            }

            if (SECURITY_OPTIMIZED && DB_CACHING_ON && $forgetDbLevelCache)
            {
                $securableItemId = $securableItem->getClassID('SecurableItem');
                ZurmoDatabaseCompatibilityUtil::callProcedureWithoutOuts("clear_cache_securableitem_actual_permissions($securableItemId)");
            }
        }

        // The $forgetDbLevel cache is for testing.
        public static function forgetAll($forgetDbLevelCache = true)
        {
            if (PHP_CACHING_ON)
            {
                self::$securableItemToPermitableToCombinedPermissions = array();
                self::$namedSecurableItemActualPermissions = array();
            }

            if (SECURITY_OPTIMIZED && DB_CACHING_ON && $forgetDbLevelCache)
            {
                ZurmoDatabaseCompatibilityUtil::callProcedureWithoutOuts("clear_cache_all_actual_permissions()");
            }

            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                self::incrementCacheIncrementValue(static::$cacheType);
            }
        }
    }
?>
