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

    class ProjectAuditEvent extends RedBeanModel
    {
        const PROJECT_CREATED            = 'Project Created';

        const TASK_ADDED                 = 'Task Added';

        const COMMENT_ADDED              = 'Comment Added';

        const TASK_COMPLETED             = 'Task Completed';

        const PROJECT_ARCHIVED           = 'Project Archived';

        public static $isTableOptimized = false;

        /**
         * Gets the appropriate time difference of the event from the current time
         * @param DateTime $dateTime in sql format
         * @return string
         */
        public static function getTimeDifference($dateTime)
        {
            assert('DateTimeUtil::isValidDbFormattedDateTime($dateTime)');
            $eventDateTime = new DateTime($dateTime);
            $currentDateTime = new DateTime();
            $interval = $currentDateTime->diff($eventDateTime);
            return $interval->d . ' d ' . $interval->h . ' h ' . $interval->m . ' m ' . $interval->s . ' s';
        }

        /**
         * Logs audit event
         * @param string $eventName
         * @param array $data
         * @param Project $project
         * @param User $user
         * @return boolean
         */
        public static function logAuditEvent($eventName, $data = null, Project $project = null, User $user = null)
        {
            assert('is_string($eventName)  && $eventName  != ""');
            if ($user === null)
            {
                $user = Yii::app()->user->userModel;
                if (!$user instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
            }

            if (!AuditEvent::$isTableOptimized && (!AUDITING_OPTIMIZED || !RedBeanDatabase::isFrozen()))
            {
                $tableName  = self::getTableName('ProjectAuditEvent');
                //RedBeanColumnTypeOptimizer::optimize($tableName, strtolower('modelId'), 'id');
                $auditEvent = new ProjectAuditEvent();
                $auditEvent->dateTime       = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                $auditEvent->eventName      = $eventName;
                $auditEvent->user           = $user;
                $auditEvent->project        = $project;
                $auditEvent->serializedData = serialize($data);
                $saved                      = $auditEvent->save();
                AuditEvent::$isTableOptimized = true;
            }
            else
            {
                $sql = "insert into projectauditevent (datetime,
                                                eventname,
                                                _user_id,
                                                project_id,
                                                serializeddata)
                        values ('" . DateTimeUtil::convertTimestampToDbFormatDateTime(time()) . "',
                                '$eventName',
                                {$user->id},
                                {$project->id},
                                :data)";
                R::exec($sql, array('data' => serialize($data))) !== null;
                $saved = true;
            }
            return $saved;
        }

        /**
         * Transalate attribute labels
         * @param string $language
         * @return array
         */
        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'projectAuditEvent' => Zurmo::t('ProjectsModule', 'Project Audit Event', array(), null, $language)
                )
            );
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'dateTime',
                    'eventName',
                    'serializedData',
                ),
                'relations' => array(
                    'user'    => array(RedBeanModel::HAS_ONE,  'User'),
                    'project' => array(RedBeanModel::HAS_ONE,  'Project'),
                ),
                'rules' => array(
                    array('dateTime',       'required'),
                    array('dateTime',       'type', 'type' => 'datetime'),
                    array('eventName',      'required'),
                    array('eventName',      'type',   'type' => 'string'),
                    array('eventName',      'length', 'min'  => 3, 'max' => 64),
                    array('serializedData', 'required'),
                    array('serializedData', 'type', 'type' => 'string'),
                )
            );
            return $metadata;
        }
    }
?>