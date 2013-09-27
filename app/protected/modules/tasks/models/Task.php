<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class Task extends MashableActivity
    {
        /*
         * Constants for task status
         */
        const TASK_STATUS_NEW                   = 1;

        const TASK_STATUS_IN_PROGRESS           = 2;

        const TASK_STATUS_AWAITING_ACCEPTANCE   = 3;

        const TASK_STATUS_REJECTED              = 4;

        const TASK_STATUS_COMPLETED             = 5;

        /**
         * @return string
         */
        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('TasksModule', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        /**
         * Gets module class name
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'TasksModule';
        }

        /**
         * @return bool
         */
        public static function canSaveMetadata()
        {
            return true;
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'completedDateTime',
                    'completed',
                    'description',
                    'dueDateTime',
                    'name',
                    'status'
                ),
                'relations' => array(
                    'requestedByUser'           => array(RedBeanModel::HAS_ONE, 'User'),
                    'comments'                  => array(RedBeanModel::HAS_MANY, 'Comment', RedBeanModel::OWNED,
                                                        RedBeanModel::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                    'checkListItems'            => array(RedBeanModel::HAS_MANY, 'TaskCheckListItem', RedBeanModel::OWNED),
                    'notificationSubscribers'   => array(RedBeanModel::HAS_MANY, 'NotificationSubscriber', RedBeanModel::OWNED),
                    'files'                     => array(RedBeanModel::HAS_MANY, 'FileModel', RedBeanModel::OWNED,
                                                        RedBeanModel::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                ),
                'rules' => array(
                    array('completedDateTime','type', 'type' => 'datetime'),
                    array('completed',        'boolean'),
                    array('dueDateTime',      'type', 'type' => 'datetime'),
                    array('description',      'type',    'type' => 'string'),
                    array('name',             'required'),
                    array('name',             'type',    'type' => 'string'),
                    array('name',             'length',  'min'  => 1, 'max' => 64),
                    array('status',           'type', 'type' => 'integer'),
                ),
                'elements' => array(
                    'completedDateTime' => 'DateTime',
                    'dueDateTime'       => 'DateTime',
                    'requestedByUser'   => 'User',
                    'comment'           => 'Comment',
                    'checkListItem'     => 'TaskCheckListItem',
                    'files'             => 'Files',
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                    'description'
                ),
            );
            return $metadata;
        }

        /**
         * @return array
         */
        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'completedDateTime' => Zurmo::t('TasksModule', 'Completed On', array(), null, $language),
                    'completed'         => Zurmo::t('TasksModule', 'Completed',  array(), null, $language),
                    'description'       => Zurmo::t('ZurmoModule', 'Description',  array(), null, $language),
                    'dueDateTime'       => Zurmo::t('TasksModule', 'Due On',       array(), null, $language),
                    'name'              => Zurmo::t('TasksModule', 'Name',  array(), null, $language),
                    'status'            => Zurmo::t('TasksModule', 'Status',  array(), null, $language),
                    'requestedByUser'   => Zurmo::t('TasksModule', 'Requested By User',  array(), null, $language),
                    'files'             => Zurmo::t('ZurmoModule', 'Files',  array(), null, $language),
                )
            );
        }

        /**
         * @return bool
         */
        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * @return string
         */
        public static function getMashableActivityRulesType()
        {
            return 'Task';
        }

        /**
         * @return bool
         */
        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if (array_key_exists('completed', $this->originalAttributeValues) &&
                    $this->completed == true)
                {
                    if ($this->completedDateTime == null)
                    {
                        $this->completedDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                    }
                    $this->unrestrictedSet('latestDateTime', $this->completedDateTime);
                }
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * @return bool
         */
        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        /**
         * @return string
         */
        public static function getGamificationRulesType()
        {
            return 'TaskGamification';
        }

        /**
         * @return array of status values and labels
         */
        public static function getStatusDropDownArray()
        {
            return array(
                self::TASK_STATUS_NEW                      => Zurmo::t('TasksModule', 'New'),
                self::TASK_STATUS_IN_PROGRESS              => Zurmo::t('TasksModule', 'In Progress'),
                self::TASK_STATUS_AWAITING_ACCEPTANCE      => Zurmo::t('TasksModule', 'Awaiting Acceptance'),
                self::TASK_STATUS_REJECTED                 => Zurmo::t('TasksModule', 'Rejected'),
                self::TASK_STATUS_COMPLETED                => Zurmo::t('TasksModule', 'Completed'),
            );
        }

        /**
         * Gets the display name for the status
         * @param int $status
         */
        public static function getStatusDisplayName($status)
        {
            $statusArray = self::getStatusDropDownArray();
            if(array_key_exists($status, $statusArray))
            {
                return $statusArray[$status];
            }
            return Zurmo::t('core', '(None)');
        }

        public static function hasReadPermissionsSubscriptionOptimization()
        {
            return true;
        }
    }
?>
