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
    /**
     * Column adapter for project in feeds on dashboard
     */
    class ProjectFeedListViewColumnAdapter extends TextListViewColumnAdapter
    {
        /**
         * Get message templates
         * @return array
         */
        public static function getMessageTemplates()
        {
            $projectNameTemplate       = '<strong><i>{projectname}</i></strong>';
            $userNameTemplate          = '<strong>{username}</strong>';
            $taskNameTemplate          = '<strong>{data}</strong>';
            $commentTemplate           = '<strong>{data}</strong>';
            $statusTemplate            = '<strong>{data}</strong>';
            $taskCheckListItemTemplate = '<strong>{data}</strong>';
            $logMessageTemplates = array(
                ProjectAuditEvent::PROJECT_CREATED         => $projectNameTemplate  . ' ' .
                                                                'is added by user ' .
                                                                $userNameTemplate,
                ProjectAuditEvent::PROJECT_ARCHIVED        =>  $projectNameTemplate  . ' ' .
                                                                'is archived by user ' .
                                                                $userNameTemplate,
                ProjectAuditEvent::TASK_STATUS_CHANGED     => $projectNameTemplate  . ' ' .
                                                                $userNameTemplate  . ' ' .
                                                                'changed status from "' .
                                                                $statusTemplate . '"',
                ProjectAuditEvent::TASK_ADDED              => $projectNameTemplate  . ' ' .
                                                                $userNameTemplate  . ' ' .
                                                                'added task "' .
                                                                $taskNameTemplate . '"',
                ProjectAuditEvent::COMMENT_ADDED           => $projectNameTemplate  . ' ' .
                                                                $userNameTemplate  . ' ' .
                                                                'added comment "' .
                                                                $commentTemplate . '"',
                ProjectAuditEvent::CHECKLIST_ITEM_ADDED    => $projectNameTemplate  . ' ' .
                                                                $userNameTemplate  . ' ' .
                                                                'added checklist item "' .
                                                                $taskCheckListItemTemplate . '"'

            );
            return $logMessageTemplates;
        }

        /**
         * Renders grid view data
         * @return array
         */
        public function renderGridViewData()
        {
            return array(
                    'name'  => $this->attribute,
                    'value' => 'ProjectFeedListViewColumnAdapter::getFeedInformationForDashboard($data)',
                    'type'  => 'raw'
                );
        }

        /**
         * Get feed information if projects for user
         * @param array $projectAuditEvent
         * @return string
         */
        public static function getFeedInformationForDashboard(ProjectAuditEvent $projectAuditEvent)
        {
            assert('$projectAuditEvent instanceof ProjectAuditEvent');
            $project = Project::getById(intval($projectAuditEvent->project->id));
            $projectName = ZurmoHtml::link($project->name, Yii::app()->createUrl('projects/default/details', array('id' => $project->id)));
            $content = null;
            $user    = User::getById($projectAuditEvent->user->id);
            $unserializedData  = unserialize($projectAuditEvent->serializedData);

            if($projectAuditEvent->eventName == ProjectAuditEvent::PROJECT_CREATED
                                    || $projectAuditEvent->eventName == ProjectAuditEvent::PROJECT_ARCHIVED)
            {
                $content .= self::getLogMessageByProjectEvent($projectAuditEvent->eventName, $projectName, $user->getFullName());
            }
            else
            {
                $content .= self::getLogMessageByProjectDataEvent($projectAuditEvent->eventName, $projectName, $user->getFullName(), $unserializedData);
            }

            $timeDiff = DateTimeUtil::getTimeDifferenceForLogEvent('ProjectsModule', $projectAuditEvent->dateTime);
            $content .=  '<small> about ' . $timeDiff . ' ago</small>';
            return Zurmo::t('ProjectsModule', $content);
        }

        /**
         * Resolve project name with link to details url
         * @param Project $project
         * @return string
         */
        protected function resolveProjectName($project)
        {
            assert('$project instanceof Project');
            return ZurmoHtml::link($project->name, Yii::app()->createUrl('projects/default/details', array('id' => $project->id)));
        }

        /**
         * Get log messages for project related event
         * @param string $event
         * @param string $projectName
         * @param string $userFullName
         * @return string
         */
        private static function getLogMessageByProjectEvent($event, $projectName, $userFullName)
        {
            $messageTemplates  = self::getMessageTemplates();
            assert('is_string($event)');
            assert('is_string($projectName)');
            assert('is_string($userFullName)');
            return str_replace(array('{projectname}', '{username}'),
                                            array($projectName, $userFullName),
                                            $messageTemplates[$event]);
        }

        /**
         * Get log message on task update, task addition or comment addition
         * @param string $event
         * @param string $data
         * @param string $projectName
         * @param string $userFullName
         * @return string
         */
        private static function getLogMessageByProjectDataEvent($event, $projectName, $userFullName, $data)
        {
            $messageTemplates  = self::getMessageTemplates();
            assert('is_string($data)');
            assert('is_string($event)');
            assert('is_string($projectName)');
            assert('is_string($userFullName)');
            return str_replace(array('{projectname}', '{data}', '{username}'),
                                            array($projectName, $data, $userFullName),
                                            $messageTemplates[$event]);
        }
    }
?>