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
     * A job for processing expired By-Time workflow objects
     */
    class WorkflowValidityCheckJob extends BaseJob
    {
        /**
         * @var int
         */
        protected static $pageSize = 200;

        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('WorkflowsModule', 'Check that workflows are valid');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'WorkflowValidityCheck';
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('JobsManagerModule', 'Once a day, early in the morning.');
        }

        /**
         * @see BaseJob::run()
         */
        public function run()
        {
            $workflows = WorkflowActionsUtil::getWorkflowsMissingRequiredActionAttributes();
            if(count($workflows) > 0)
            {
                $message                      = new NotificationMessage();
                $message->htmlContent         = Zurmo::t('WorkflowsModule', 'As a result of a field or fields recently ' .
                                                'becoming required, at least 1 workflow rule will no longer work properly.');
                $message->htmlContent        .= "<div><ul>";
                foreach($workflows as $workflow)
                {
                    $message->htmlContent      .= "<li>";
                    $url                        = Yii::app()->createUrl('workflows/default/details',
                                                  array('id' => $workflow->getId()));
                    $message->htmlContent      .= ZurmoHtml::link(strval($workflow) , $url);
                    $message->htmlContent      .= "</li>";
                }
                $message->htmlContent      .= "</ul></div>";
                $rules                        = new WorkflowValidityCheckNotificationRules();
                NotificationsUtil::submit($message, $rules);
            }
            return true;
        }
    }
?>