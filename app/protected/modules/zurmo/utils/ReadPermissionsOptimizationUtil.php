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

    abstract class ReadPermissionsOptimizationUtil
    {
        /**
         * At some point if performance is a problem with rebuilding activity models, then the stored procedure
         * needs to be refactored to somehow support more joins dynamically.
         * @see https://www.pivotaltracker.com/story/show/38804909
         * @param boolean $forcePhp
         */
        public static function rebuild($forcePhp = false)
        {
            //Forcing php way until we can fix failing tests here: AccountReadPermissionsOptimizationScenariosTest
            $forcePhp = true;
            assert('is_bool($forcePhp)');
            foreach (self::getMungableModelClassNames() as $modelClassName)
            {
                if (!SECURITY_OPTIMIZED || $forcePhp)
                {
                    self::rebuildViaSlowWay($modelClassName);
                }
                else
                {
                    //models that extend activity are special and can only be done with the PHP process.  They cannot
                    //be done using the stored procedure because it does not support the extra joins needed to determine
                    //which securable items to look at.
                    if (is_subclass_of($modelClassName, 'Activity'))
                    {
                        self::rebuildViaSlowWay($modelClassName);
                    }
                    else
                    {
                        $modelTableName = RedBeanModel::getTableName($modelClassName);
                        $mungeTableName = self::getMungeTableName($modelClassName);
                        if (!is_subclass_of($modelClassName, 'OwnedSecurableItem'))
                        {
                            throw new NotImplementedException($message, $code, $previous);
                        }
                        if (is_subclass_of($modelClassName, 'Person'))
                        {
                            if ($modelClassName != 'Contact')
                            {
                                throw new NotSupportedException();
                            }
                            else
                            {
                                $modelTableName = Person::getTableName('Person');
                            }
                        }
                        ZurmoDatabaseCompatibilityUtil::
                            callProcedureWithoutOuts("rebuild('$modelTableName', '$mungeTableName')");
                    }
                }
            }
        }

        protected static function rebuildViaSlowWay($modelClassName)
        {
            // The slow way will remain here as documentation
            // for what the optimized way is doing.
            $mungeTableName  = self::getMungeTableName($modelClassName);
            self::recreateTable($mungeTableName);
            //Specifically call RedBeanModel to avoid the use of the security in OwnedSecurableItem since for
            //rebuild it needs to look at all models regardless of permissions of the current user.
            $modelCount = RedBeanModel::getCount(null, null, $modelClassName);
            $subset = intval($modelCount / 20);
            if ($subset < 100)
            {
                $subset = 100;
            }
            elseif ($subset > 1000)
            {
                $subset = 1000;
            }
            for ($i = 0; $i < $modelCount; $i += $subset)
            {
                //Specifically call RedBeanModel to avoid the use of the security in OwnedSecurableItem since for
                //rebuild it needs to look at all models regardless of permissions of the current user.
                $models = RedBeanModel::getSubset(null, $i, $subset, null, null, $modelClassName);
                foreach ($models as $model)
                {
                    assert('$model instanceof SecurableItem');
                    $securableItemId = $model->getClassId('SecurableItem');
                    $users = User::getAll();
                    foreach ($users as $user)
                    {
                        list($allowPermissions, $denyPermissions) = $model->getExplicitActualPermissions($user);
                        $effectiveExplicitPermissions = $allowPermissions & ~$denyPermissions;
                        if (($effectiveExplicitPermissions & Permission::READ) == Permission::READ)
                        {
                            self::incrementCount($mungeTableName, $securableItemId, $user);
                        }
                    }
                    $groups = Group::getAll();
                    foreach ($groups as $group)
                    {
                        list($allowPermissions, $denyPermissions) = $model->getExplicitActualPermissions($group);
                        $effectiveExplicitPermissions = $allowPermissions & ~$denyPermissions;
                        if (($effectiveExplicitPermissions & Permission::READ) == Permission::READ)
                        {
                            self::incrementCount($mungeTableName, $securableItemId, $group);
                            foreach ($group->users as $user)
                            {
                                if ($user->role->id > 0)
                                {
                                    self::incrementParentRolesCounts($mungeTableName, $securableItemId, $user->role);
                                }
                            }
                            foreach ($group->groups as $subGroup)
                            {
                                self::processNestedGroupWhereParentHasReadPermissionOnSecurableItem(
                                      $mungeTableName, $securableItemId, $subGroup);
                            }
                        }
                    }
                    $roles = Role::getAll();
                    foreach ($roles as $role)
                    {
                        $count = self::getRoleMungeCount($model, $role);
                        assert('$count >= 0');
                        if ($count > 0)
                        {
                            self::setCount($mungeTableName, $securableItemId, $role, $count);
                        }
                    }
                }
            }
        }

        protected static function processNestedGroupWhereParentHasReadPermissionOnSecurableItem(
                                  $mungeTableName, $securableItemId, Group $group)
        {
            assert('is_string($mungeTableName) && $mungeTableName != ""');
            assert('is_int($securableItemId) && $securableItemId > 0');
            self::incrementCount($mungeTableName, $securableItemId, $group);
            foreach ($group->users as $user)
            {
                if ($user->role->id > 0)
                {
                    self::incrementParentRolesCounts($mungeTableName, $securableItemId, $user->role);
                }
            }
            foreach ($group->groups as $subGroup)
            {
                self::processNestedGroupWhereParentHasReadPermissionOnSecurableItem(
                      $mungeTableName, $securableItemId, $subGroup);
            }
        }

        protected static function getRoleMungeCount(SecurableItem $securableItem, Role $role)
        {
            $count = 0;
            foreach ($role->roles as $subRole)
            {
                $count += self::getSubRoleMungeCount($securableItem, $subRole);
            }
            return $count;
        }

        protected static function getSubRoleMungeCount(SecurableItem $securableItem, Role $role)
        {
            $count = self::getImmediateRoleMungeCount($securableItem, $role);
            foreach ($role->roles as $subRole)
            {
                $count += self::getSubRoleMungeCount($securableItem, $subRole);
            }
            return $count;
        }

        protected static function getImmediateRoleMungeCount(SecurableItem $securableItem, Role $role)
        {
            $count = 0;
            foreach ($role->users as $user)
            {
                if ($securableItem->owner->isSame($user))
                {
                    $count++;
                }
                list($allowPermissions, $denyPermissions) = $securableItem->getExplicitActualPermissions($user);
                $effectiveExplicitPermissions = $allowPermissions & ~$denyPermissions;
                if (($effectiveExplicitPermissions & Permission::READ) == Permission::READ)
                {
                    $count++;
                }
                foreach ($user->groups as $group)
                {
                    $count += self::getGroupMungeCount($securableItem, $group);
                }
            }
            return $count;
        }

        protected static function getGroupMungeCount(SecurableItem $securableItem, Group $group)
        {
            $count = 0;
            list($allowPermissions, $denyPermissions) = $securableItem->getExplicitActualPermissions($group);
            $effectiveExplicitPermissions = $allowPermissions & ~$denyPermissions;
            if (($effectiveExplicitPermissions & Permission::READ) == Permission::READ)
            {
                $count++;
            }
            if ($group->group->id > 0 && !(!RedBeanDatabase::isFrozen() && $group->group->isSame($group))) // Prevent cycles in database auto build.
            {
                $count += self::getGroupMungeCount($securableItem, $group->group);
            }
            return $count;
        }

        // SecurableItem create, assigned, or deleted.

        // Past tense implies the method must be called immediately after the associated operation.
        public static function ownedSecurableItemCreated(OwnedSecurableItem $ownedSecurableItem)
        {
            self::ownedSecurableItemOwnerChanged($ownedSecurableItem);
        }

        public static function ownedSecurableItemOwnerChanged(OwnedSecurableItem $ownedSecurableItem, User $oldUser = null)
        {
            $modelClassName = get_class($ownedSecurableItem);
            assert('$modelClassName != "OwnedSecurableItem"');
            $mungeTableName = self::getMungeTableName($modelClassName);
            if ($oldUser !== null && $oldUser->role->id > 0)
            {
                self::decrementParentRolesCounts($mungeTableName, $ownedSecurableItem->getClassId('SecurableItem'), $oldUser->role);
                self::garbageCollect($mungeTableName);
            }
            if ($ownedSecurableItem->owner->role->id > 0)
            {
                self::incrementParentRolesCounts($mungeTableName, $ownedSecurableItem->getClassId('SecurableItem'), $ownedSecurableItem->owner->role);
            }
        }

        // Being implies the the method must be called just before the associated operation.
        // The object is needed before the delete occurs and the delete cannot fail.
        public static function securableItemBeingDeleted(SecurableItem $securableItem) // Call being methods before the destructive operation.
        {
            $modelClassName = get_class($securableItem);
            assert('$modelClassName != "OwnedSecurableItem"');
            $mungeTableName = self::getMungeTableName($modelClassName);
            $securableItemId = $securableItem->getClassId('SecurableItem');
            R::exec("delete from $mungeTableName
                     where       securableitem_id = $securableItemId");
        }

        // Permissions added or removed.

        public static function securableItemGivenPermissionsForUser(SecurableItem $securableItem, User $user)
        {
            $modelClassName = get_class($securableItem);
            assert('$modelClassName != "OwnedSecurableItem"');
            $mungeTableName = self::getMungeTableName($modelClassName);
            $securableItemId = $securableItem->getClassId('SecurableItem');
            self::incrementCount($mungeTableName, $securableItemId, $user);
            if ($user->role->id > 0)
            {
                self::incrementParentRolesCounts($mungeTableName, $securableItemId, $user->role);
            }
        }

        public static function securableItemGivenPermissionsForGroup(SecurableItem $securableItem, Group $group)
        {
            $modelClassName = get_class($securableItem);
            assert('$modelClassName != "OwnedSecurableItem"');
            $mungeTableName = self::getMungeTableName($modelClassName);
            $securableItemId = $securableItem->getClassId('SecurableItem');
            self::incrementCount($mungeTableName, $securableItemId, $group);
            foreach ($group->users as $user)
            {
                if ($user->role->id > 0)
                {
                    self::incrementParentRolesCounts($mungeTableName, $securableItemId, $user->role);
                }
            }
            foreach ($group->groups as $subGroup)
            {
                self::securableItemGivenPermissionsForGroup($securableItem, $subGroup);
            }
        }

        public static function securableItemLostPermissionsForUser(SecurableItem $securableItem, User $user)
        {
            $modelClassName = get_class($securableItem);
            assert('$modelClassName != "OwnedSecurableItem"');
            $mungeTableName = self::getMungeTableName($modelClassName);
            $securableItemId = $securableItem->getClassId('SecurableItem');
            self::decrementCount($mungeTableName, $securableItemId, $user);
            if ($user->role->id > 0)
            {
                self::decrementParentRolesCounts($mungeTableName, $securableItemId, $user->role);
            }
            self::garbageCollect($mungeTableName);
        }

        public static function securableItemLostPermissionsForGroup(SecurableItem $securableItem, Group $group)
        {
            $modelClassName = get_class($securableItem);
            assert('$modelClassName != "OwnedSecurableItem"');
            $mungeTableName = self::getMungeTableName($modelClassName);
            $securableItemId = $securableItem->getClassId('SecurableItem');
            self::decrementCount($mungeTableName, $securableItemId, $group);
            foreach ($group->users as $user)
            {
                self::securableItemLostPermissionsForUser($securableItem, $user);
            }
            foreach ($group->groups as $subGroup)
            {
                self::securableItemLostPermissionsForGroup($securableItem, $subGroup);
            }
            self::garbageCollect($mungeTableName);
        }

        // User operations.

        public static function userBeingDeleted($user) // Call being methods before the destructive operation.
        {
            foreach (self::getMungableModelClassNames() as $modelClassName)
            {
                $mungeTableName = self::getMungeTableName($modelClassName);
                if ($user->role->id > 0)
                {
                    self::decrementParentRolesCountsForAllSecurableItems($mungeTableName, $user->role);
                    self::garbageCollect($mungeTableName);
                }
                $userId = $user->id;
                R::exec("delete from $mungeTableName
                         where       munge_id = 'U$userId'");
            }
        }

        // Group operations.

        public static function userAddedToGroup(Group $group, User $user)
        {
            foreach (self::getMungableModelClassNames() as $modelClassName)
            {
                $mungeTableName = self::getMungeTableName($modelClassName);
                $groupId = $group->id;
                $sql = "select securableitem_id
                        from   $mungeTableName
                        where  munge_id = concat('G', $groupId)";
                $securableItemIds = R::getCol($sql);
                self::bulkIncrementParentRolesCounts($mungeTableName, $securableItemIds, $user->role);
                /*
                 * This extra step is not needed. See slide 21.  This is similar to userBeingRemovedFromRole in that
                 * the above query already is trapping the information needed.
                    Follow the same process for any upstream groups that the group is a member of.
                */
            }
        }

        public static function userRemovedFromGroup(Group $group, User $user)
        {
            foreach (self::getMungableModelClassNames() as $modelClassName)
            {
                $mungeTableName = self::getMungeTableName($modelClassName);
                $groupId = $group->id;
                $sql = "select securableitem_id
                        from   $mungeTableName
                        where  munge_id = concat('G', $groupId)";
                $securableItemIds = R::getCol($sql);
                self::bulkDecrementParentRolesCounts($mungeTableName, $securableItemIds, $user->role);
                /*
                 * This extra step is not needed. See slide 22. This is similar to userBeingRemovedFromRole or
                 * userAddedToGroup in that the above query is already trapping the information needed.
                    Follow the same process for any upstream groups that the group is a member of.
                */
                self::garbageCollect($mungeTableName);
            }
        }

        public static function groupAddedToGroup(Group $group)
        {
            self::groupAddedOrRemovedFromGroup(true, $group);
        }

        public static function groupBeingRemovedFromGroup(Group $group) // Call being methods before the destructive operation.
        {
            self::groupAddedOrRemovedFromGroup(false, $group);
        }

        public static function groupBeingDeleted($group) // Call being methods before the destructive operation.
        {
            if ($group->group->id > 0 && !(!RedBeanDatabase::isFrozen() && $group->group->isSame($group))) // Prevent cycles in database auto build.
            {
                self::groupBeingRemovedFromGroup($group);
            }
            foreach ($group->groups as $childGroup)
            {
                if (!RedBeanDatabase::isFrozen() && $group->isSame($childGroup)) // Prevent cycles in database auto build.
                {
                    continue;
                }
                self::groupBeingRemovedFromGroup($childGroup);
            }
            foreach ($group->users as $user)
            {
                self::userRemovedFromGroup($group, $user);
            }
            foreach (self::getMungableModelClassNames() as $modelClassName)
            {
                $groupId = $group->id;
                $mungeTableName = self::getMungeTableName($modelClassName);
                R::exec("delete from $mungeTableName
                     where       munge_id = 'G$groupId'");
            }
        }

        protected static function groupAddedOrRemovedFromGroup($isAdd, Group $group)
        {
            assert('is_bool($isAdd)');
            if (!RedBeanDatabase::isFrozen() && $group->group->isSame($group)) // Prevent cycles in database auto build.
            {
                return;
            }

            $countMethod1 = $isAdd ? 'bulkIncrementCount'             : 'bulkDecrementCount';
            $countMethod2 = $isAdd ? 'bulkIncrementParentRolesCounts' : 'bulkDecrementParentRolesCounts';

            $parentGroups = self::getAllParentGroups($group);
            $users  = self::getAllUsersInGroupAndChildGroupsRecursively($group);

            // Handle groups that $parentGroup is in. In/decrement for the containing groups' containing
            // groups the models they have explicit permissions on.
            // And handle user's role's parents. In/decrement for all users that have permission because
            // they are now in the containing group.
            if (count($parentGroups) > 0)
            {
                $parentGroupPermitableIds = array();
                foreach ($parentGroups as $parentGroup)
                {
                    $parentGroupPermitableIds[] = $parentGroup->getClassId('Permitable');
                }
                $sql = 'select securableitem_id
                        from   permission
                        where  permitable_id in (' . join(', ', $parentGroupPermitableIds) . ')';
                $securableItemIds = R::getCol($sql);
                foreach (self::getMungableModelClassNames() as $modelClassName)
                {
                    $mungeTableName = self::getMungeTableName($modelClassName);
                    self::$countMethod1($mungeTableName, $securableItemIds, $group);
                    foreach ($users as $user)
                    {
                        if ($user->role->id > 0)
                        {
                            self::$countMethod2($mungeTableName, $securableItemIds, $user->role);
                        }
                    }
                }
            }
            if (!$isAdd)
            {
                foreach (self::getMungableModelClassNames() as $modelClassName)
                {
                    $mungeTableName = self::getMungeTableName($modelClassName);
                    self::garbageCollect($mungeTableName);
                }
            }
        }

        protected static function getAllUsersInGroupAndChildGroupsRecursively(Group $group)
        {
            $users = array();
            foreach ($group->users as $user)
            {
                $users[] = $user;
            }
            foreach ($group->groups as $childGroup)
            {
                if (!RedBeanDatabase::isFrozen() && $group->isSame($childGroup)) // Prevent cycles in database auto build.
                {
                    continue;
                }
                $users = array_merge($users, self::getAllUsersInGroupAndChildGroupsRecursively($childGroup));
            }
            return $users;
        }

        protected static function getAllParentGroups(Group $group)
        {
            $parentGroups = array();
            $parentGroup = $group->group;
            while ($parentGroup->id > 0 && !(!RedBeanDatabase::isFrozen() && $parentGroup->isSame($parentGroup->group))) // Prevent cycles in database auto build.
            {
                $parentGroups[] = $parentGroup;
                $parentGroup = $parentGroup->group;
            }
            return $parentGroups;
        }

        // Role operations.

        public static function roleParentSet(Role $role)
        {
            assert('$role->role->id > 0');
            self::roleParentSetOrRemoved(true, $role);
        }

        public static function roleParentBeingRemoved(Role $role) // Call being methods before the destructive operation.
        {
            assert('$role->role->id > 0');
            self::roleParentSetOrRemoved(false, $role);
        }

        public static function roleBeingDeleted(Role $role) // Call being methods before the destructive operation.
        {
            foreach (self::getMungableModelClassNames() as $modelClassName)
            {
                if ($role->role->id > 0)
                {
                    self::roleParentBeingRemoved($role);
                }
                foreach ($role->roles as $childRole)
                {
                    if ($childRole->role->id > 0)
                    {
                        self::roleParentBeingRemoved($childRole);
                    }
                }
                $mungeTableName = self::getMungeTableName($modelClassName);
                $roleId = $role->id;
                $sql = "delete from $mungeTableName
                        where       munge_id = 'R$roleId'";
                R::exec($sql);
            }
        }

        protected static function roleParentSetOrRemoved($isSet, Role $role)
        {
            assert('is_bool($isSet)');
            if (!RedBeanDatabase::isFrozen() && $role->role->isSame($role)) // Prevent cycles in database auto build.
            {
                return;
            }

            $countMethod = $isSet ? 'bulkIncrementParentRolesCounts' : 'bulkDecrementParentRolesCounts';

            foreach (self::getMungableModelClassNames() as $modelClassName)
            {
                $mungeTableName = self::getMungeTableName($modelClassName);

                $usersInRolesChildren = self::getAllUsersInRolesChildRolesRecursively($role);

                // Handle users in $role. In/decrement for the parent's parent
                // roles the models they either own or have explicit permissions on.

                if (count($role->users) > 0)
                {
                    $userIds      = array();
                    $permitableIds = array();
                    foreach ($role->users as $user)
                    {
                        $userIds[]       = $user->id;
                        $permitableIds[] = $user->getClassId('Permitable');
                    }
                    $sql = 'select securableitem_id
                            from   ownedsecurableitem
                            where  owner__user_id in (' . join(', ', $userIds) . ')
                            union all
                            select securableitem_id
                            from   permission
                            where  permitable_id in (' . join(', ', $permitableIds) . ')';
                    $securableItemIds = R::getCol($sql);
                    self::$countMethod($mungeTableName, $securableItemIds, $role->role);
                }

                // Handle users in the child roles of $role. Increment for the parent's parent
                // roles the models they either own or have explicit permissions on.

                if (count($usersInRolesChildren))
                {
                    $userIds       = array();
                    $permitableIds = array();
                    foreach ($usersInRolesChildren as $user)
                    {
                        $userIds[]       = $user->id;
                        $permitableIds[] = $user->getClassId('Permitable');
                    }
                    $sql = 'select securableitem_id
                            from   ownedsecurableitem
                            where  owner__user_id in (' . join(', ', $userIds) . ')
                            union all
                            select securableitem_id
                            from   permission
                            where  permitable_id in (' . join(', ', $permitableIds) . ')';
                    $securableItemIds = R::getCol($sql);
                    self::$countMethod($mungeTableName, $securableItemIds, $role);
                }

                // Handle groups for the users in $role. Increment for the parent's parent
                // roles the models they have explicit permissions on.

                if (count($role->users) > 0)
                {
                    $permitableIds = array();
                    foreach ($role->users as $user)
                    {
                        foreach ($user->groups as $group)
                        {
                            $permitableIds[] = $group->getClassId('Permitable');
                        }
                    }
                    $permitableIds = array_unique($permitableIds);
                    $sql = 'select securableitem_id
                            from   permission
                            where  permitable_id in (' . join(', ', $permitableIds) . ')';
                    $securableItemIds = R::getCol($sql);
                    self::$countMethod($mungeTableName, $securableItemIds, $role->role);
                }

                // Handle groups for the users $role's child roles. Increment for the role's parent
                // roles the models they have explicit permissions on.

                if (count($usersInRolesChildren))
                {
                    $permitableIds = array();
                    foreach ($usersInRolesChildren as $user)
                    {
                        foreach ($user->groups as $group)
                        {
                            $permitableIds[] = $group->getClassId('Permitable');
                        }
                    }
                    $permitableIds = array_unique($permitableIds);
                    if (count($permitableIds) > 0)
                    {
                        $sql = 'select securableitem_id
                                from   permission
                                where  permitable_id in (' . join(', ', $permitableIds) . ')';
                        $securableItemIds = R::getCol($sql);
                    }
                    else
                    {
                        $securableItemIds = array();
                    }
                    self::$countMethod($mungeTableName, $securableItemIds, $role);
                }
                if (!$isSet)
                {
                    self::garbageCollect($mungeTableName);
                }
            }
        }

        protected static function getAllUsersInRolesChildRolesRecursively(Role $role)
        {
            $users = array();
            foreach ($role->roles as $childRole)
            {
                if (!RedBeanDatabase::isFrozen() && $role->isSame($childRole)) // Prevent cycles in database auto build.
                {
                    continue;
                }
                foreach ($childRole->users as $user)
                {
                    $users[] = $user;
                }
                $users = array_merge($users, self::getAllUsersInRolesChildRolesRecursively($childRole));
            }
            return $users;
        }

        public static function userAddedToRole(User $user)
        {
            assert('$user->role->id > 0');
            foreach (self::getMungableModelClassNames() as $modelClassName)
            {
                $mungeTableName = self::getMungeTableName($modelClassName);
                $userId = $user->id;
                $sql = "select securableitem_id
                        from   ownedsecurableitem
                        where  owner__user_id = $userId";
                $securableItemIds = R::getCol($sql);
                //Increment the parent roles for securableItems that the user is the owner on.
                self::bulkIncrementParentRolesCounts($mungeTableName, $securableItemIds, $user->role);

                //Get all downstream groups the user is in including any groups that are in those groups recursively.
                //Then for each group found, add weight for the user's upstream roles.
                $groupMungeIds = array();
                foreach ($user->groups as $group)
                {
                    $groupMungeIds[] = 'G' . $group->id;
                    self::getAllUpstreamGroupsRecursively($group, $groupMungeIds);
                }
                if (count($groupMungeIds) > 0)
                {
                    $inSqlPart = SQLOperatorUtil::resolveOperatorAndValueForOneOf('oneOf', $groupMungeIds, true);
                    $sql = "select distinct $mungeTableName.securableitem_id
                            from   $mungeTableName
                            where  $mungeTableName.munge_id $inSqlPart";
                    $securableItemIds = R::getCol($sql);
                    self::bulkIncrementParentRolesCounts($mungeTableName, $securableItemIds, $user->role);
                }
            }
        }

        public static function userBeingRemovedFromRole(User $user, Role $role)
        {
            foreach (self::getMungableModelClassNames() as $modelClassName)
            {
                $mungeTableName = self::getMungeTableName($modelClassName);
                $userId = $user->id;
                $sql = "select securableitem_id
                        from   ownedsecurableitem
                        where  owner__user_id = $userId";
                $securableItemIds = R::getCol($sql);
                self::bulkDecrementParentRolesCounts($mungeTableName, $securableItemIds, $role);

                $sql = "select $mungeTableName.securableitem_id
                        from   $mungeTableName, _group__user
                        where  $mungeTableName.munge_id = concat('G', _group__user._group_id) and
                               _group__user._user_id = $userId";
                $securableItemIds = R::getCol($sql);
                self::bulkDecrementParentRolesCounts($mungeTableName, $securableItemIds, $role);
                /*
                 * This additional step I don't think is needed because the sql query above actually traps
                 * the upstream explicit securableItems because the lower level groups will already have a point for
                 * each of them.
                    What groups are the user part of and what groups are those groups children of recursively?
                    For any models that have that group explicity for read, subtract 1 point for the user's
                    upstream roles from the disconnected role.
                */
                self::garbageCollect($mungeTableName);
            }
        }

        ///////////////////////////////////////////////////////////////////////

        public static function getAllUpstreamGroupsRecursively(Group $group, & $groupMungeIds)
        {
            assert('is_array($groupMungeIds)');
            if ($group->group->id > 0 )
            {
                $groupMungeIds[] = 'G' . $group->group->id;
                if (!RedBeanDatabase::isFrozen() && $group->isSame($group->group))
                {
                    //Do Nothing. Prevent cycles in database auto build.
                }
                else
                {
                    self::getAllUpstreamGroupsRecursively($group->group, $groupMungeIds);
                }
            }
        }

        public static function getUserRoleIdAndGroupIds(User $user)
        {
            if ($user->role->id > 0)
            {
                $roleId = $user->role->id;
            }
            else
            {
                $roleId = null;
            }
            $groupIds = array();
            foreach ($user->groups as $group)
            {
                $groupIds[] = $group->id;
            }
            return array($roleId, $groupIds);
        }

        public static function getMungeIdsByUser(User $user)
        {
            list($roleId, $groupIds) = self::getUserRoleIdAndGroupIds($user);
            $mungeIds = array("U$user->id");
            if ($roleId != null)
            {
                $mungeIds[] = "R$roleId";
            }
            foreach ($groupIds as $groupId)
            {
                $mungeIds[] = "G$groupId";
            }
            //Add everyone group
            $everyoneGroupId = Group::getByName(Group::EVERYONE_GROUP_NAME)->id;
            if (!in_array("G" . $everyoneGroupId, $mungeIds) && $everyoneGroupId > 0)
            {
                $mungeIds[] = "G" . $everyoneGroupId;
            }
            return $mungeIds;
        }

        /**
         * Public for testing only. Need to manually create test model tables that would not be picked up normally.
         */
        public static function recreateTable($mungeTableName)
        {
            assert('is_string($mungeTableName) && $mungeTableName  != ""');
            R::exec("drop table if exists $mungeTableName");
            R::exec("create table $mungeTableName (
                        securableitem_id int(11)     unsigned not null,
                        munge_id         varchar(12)          not null,
                        count            int(8)      unsigned not null,
                        primary key (securableitem_id, munge_id)
                     )");
            R::exec("create index index_${mungeTableName}_securable_item_id
                     on $mungeTableName (securableitem_id);");
        }

        protected static function incrementCount($mungeTableName, $securableItemId, $item)
        {
            assert('is_string($mungeTableName) && $mungeTableName != ""');
            assert('is_int($securableItemId) && $securableItemId > 0');
            assert('$item instanceof User || $item instanceof Group || $item instanceof Role');
            $itemId  = $item->id;
            $type    = self::getMungeType($item);
            $mungeId = "$type$itemId";
            R::exec("insert into $mungeTableName
                                 (securableitem_id, munge_id, count)
                     values ($securableItemId, '$mungeId', 1)
                     on duplicate key
                     update count = count + 1");
        }

        protected static function setCount($mungeTableName, $securableItemId, $item, $count)
        {
            assert('is_string($mungeTableName) && $mungeTableName != ""');
            assert('is_int($securableItemId) && $securableItemId > 0');
            assert('$item instanceof User || $item instanceof Group || $item instanceof Role');
            $itemId  = $item->id;
            $type    = self::getMungeType($item);
            $mungeId = "$type$itemId";
            R::exec("insert into $mungeTableName
                                 (securableitem_id, munge_id, count)
                     values ($securableItemId, '$mungeId', $count)
                     on duplicate key
                     update count = $count");
        }

        protected static function decrementCount($mungeTableName, $securableItemId, $item)
        {
            assert('is_string($mungeTableName) && $mungeTableName != ""');
            assert('is_int($securableItemId) && $securableItemId > 0');
            assert('$item instanceof User || $item instanceof Group || $item instanceof Role');
            $itemId  = $item->id;
            $type    = self::getMungeType($item);
            $mungeId = "$type$itemId";
            R::exec("update $mungeTableName
                     set count = count - 1
                     where securableitem_id = $securableItemId and
                           munge_id         = '$mungeId'");
        }

        protected static function decrementCountForAllSecurableItems($mungeTableName, $item)
        {
            assert('is_string($mungeTableName) && $mungeTableName != ""');
            assert('$item instanceof User || $item instanceof Group || $item instanceof Role');
            $itemId  = $item->id;
            $type    = self::getMungeType($item);
            $mungeId = "$type$itemId";
            R::exec("update $mungeTableName
                     set count = count - 1
                     where munge_id = '$mungeId'");
        }

        protected static function bulkIncrementCount($mungeTableName, $securableItemIds, $item)
        {
            assert('is_string($mungeTableName) && $mungeTableName != ""');
            assert('$item instanceof User || $item instanceof Group || $item instanceof Role');
            foreach ($securableItemIds as $securableItemId)
            {
                self::incrementCount($mungeTableName, intval($securableItemId), $item);
            }
        }

        protected static function bulkDecrementCount($mungeTableName, $securableItemIds, $item)
        {
            assert('is_string($mungeTableName) && $mungeTableName != ""');
            assert('$item instanceof User || $item instanceof Group || $item instanceof Role');
            foreach ($securableItemIds as $securableItemId)
            {
                self::decrementCount($mungeTableName, intval($securableItemId), $item);
            }
        }

        protected static function incrementParentRolesCounts($mungeTableName, $securableItemId, Role $role)
        {
            assert('is_string($mungeTableName) && $mungeTableName != ""');
            assert('is_int($securableItemId) && $securableItemId > 0');
            if (!RedBeanDatabase::isFrozen() && $role->role->isSame($role)) // Prevent cycles in database auto build.
            {
                return;
            }
            if ($role->role->id > 0)
            {
                self::incrementCount            ($mungeTableName, $securableItemId, $role->role);
                self::incrementParentRolesCounts($mungeTableName, $securableItemId, $role->role);
            }
        }

        protected static function decrementParentRolesCounts($mungeTableName, $securableItemId, Role $role)
        {
            assert('is_string($mungeTableName) && $mungeTableName != ""');
            assert('is_int($securableItemId) && $securableItemId > 0');
            if (!RedBeanDatabase::isFrozen() && $role->role->isSame($role)) // Prevent cycles in database auto build.
            {
                return;
            }
            if ($role->role->id > 0)
            {
                self::decrementCount            ($mungeTableName, $securableItemId, $role->role);
                self::decrementParentRolesCounts($mungeTableName, $securableItemId, $role->role);
            }
        }

        protected static function decrementParentRolesCountsForAllSecurableItems($mungeTableName, Role $role)
        {
            assert('is_string($mungeTableName) && $mungeTableName != ""');
            if (!RedBeanDatabase::isFrozen() && $role->role->isSame($role)) // Prevent cycles in database auto build.
            {
                return;
            }
            if ($role->role->id > 0)
            {
                self::decrementCountForAllSecurableItems            ($mungeTableName, $role->role);
                self::decrementParentRolesCountsForAllSecurableItems($mungeTableName, $role->role);
            }
        }

        protected static function bulkIncrementParentRolesCounts($mungeTableName, $securableItemIds, Role $role)
        {
            foreach ($securableItemIds as $securableItemId)
            {
                self::incrementParentRolesCounts($mungeTableName, intval($securableItemId), $role);
            }
        }

        protected static function bulkDecrementParentRolesCounts($mungeTableName, $securableItemIds, Role $role)
        {
            foreach ($securableItemIds as $securableItemId)
            {
                self::decrementParentRolesCounts($mungeTableName, intval($securableItemId), $role);
            }
        }

        // This must be called ny any public method which decrements
        // counts after it has done all its count decrementing.
        // It is not done in decrementCount to avoid doing it more
        // than is necessary.
        protected static function garbageCollect($mungeTableName)
        {
            assert("(int)R::getCell('select count(*)
                                from   $mungeTableName
                                where  count < 0') == 0");
            R::exec("delete from $mungeTableName
                     where       count = 0");
            assert("(int)R::getCell('select count(*)
                                from   $mungeTableName
                                where  count < 1') == 0");
        }

        protected static function getMungeType($item)
        {
            assert('$item instanceof User || $item instanceof Group || $item instanceof Role');
            return substr(get_class($item), 0, 1);
        }

        //public for testing only
        public static function getMungableModelClassNames()
        {
            try
            {
                return GeneralCache::getEntry('mungableModelClassNames');
            }
            catch (NotFoundException $e)
            {
                $mungableClassNames = self::findMungableModelClassNames();
                GeneralCache::cacheEntry('mungableModelClassNames', $mungableClassNames);
                return $mungableClassNames;
            }
        }

        //public for testing only.
        public static function findMungableModelClassNames()
        {
            $mungableModelClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $modelClassNames = $module::getModelClassNames();
                foreach ($modelClassNames as $modelClassName)
                {
                    if (is_subclass_of($modelClassName, 'SecurableItem') &&
                        $modelClassName::hasReadPermissionsOptimization())
                    {
                        $mungableModelClassNames[] = $modelClassName;
                    }
                }
            }
            return $mungableModelClassNames;
        }

        protected static function getMainTableName($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            return RedBeanModel::getTableName($modelClassName);
        }

        public static function getMungeTableName($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            return self::getMainTableName($modelClassName) . '_read';
        }
    }
?>
