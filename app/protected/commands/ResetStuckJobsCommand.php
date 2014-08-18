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
     * Reset stuck jobs
     */
    class ResetStuckJobsCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc resetStuckJobs <username> <jobType>

    DESCRIPTION
      This command will reset all stuck jobs(remove them from jobsInProgress table)

    PARAMETERS
     * username: username which run command.
     * jobType: jobType to reset, or 'All' to reset all jobs in JobInProcess table
EOD;
        }

        /**
         * Execute the action
         * @param array $args - command line parameters specific for this command
         * @return int|void
         */
        public function run($args)
        {
            if (!isset($args[0]))
            {
                $this->usageError('A username must be specified.');
            }
            try
            {
                Yii::app()->user->userModel = User::getByUsername($args[0]);
            }
            catch (NotFoundException $e)
            {
                $this->usageError('The specified username does not exist.');
            }
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if (!$group->users->contains(Yii::app()->user->userModel))
            {
                $this->usageError('The specified user is not a super administrator.');
            }

            if (!isset($args[1]))
            {
                $this->usageError('JobType must be provided and must be existing jobType!');
            }
            else
            {
                $jobType = $args[1];
            }

            $template        = "{message}\n";
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->setExtraRenderBytes(0);
            $messageStreamer->add('');

            if ($jobType == 'All')
            {
                $messageStreamer->add("Reset all jobs.");
                $jobsInProcess = JobInProcess::getAll();
                if (is_array($jobsInProcess) && count($jobsInProcess) > 0)
                {
                    foreach ($jobsInProcess as $jobInProcess)
                    {
                        $jobInProcess->delete();
                        $messageStreamer->add("The job {$jobInProcess->type} has been reset.");
                    }
                }
                else
                {
                    $messageStreamer->add("There are no jobs in process to be reset.");
                }
            }
            else
            {
                $jobClassName = $jobType . 'Job';
                if (!@class_exists($jobClassName))
                {
                    $messageStreamer->add("Error! The {$jobClassName} does not exist.");
                }
                else
                {
                    try
                    {
                        $jobInProcess      = JobInProcess::getByType($jobType);
                        $jobInProcess->delete();
                        $messageStreamer->add("The job {$jobClassName} has been reset.");
                    }
                    catch (NotFoundException $e)
                    {
                        $messageStreamer->add("The job {$jobClassName} was not found to be stuck and therefore was not reset.");
                    }
                }
            }
        }
    }
?>