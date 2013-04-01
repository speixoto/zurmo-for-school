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
     * Used to observe when a model that can be an activityItem is deleted so that the activity_item can be properly removed.
     */
    class WorkflowsObserver extends CComponent
    {
        /**
         * @var bool
         */
        public $enabled = true;

        /**
         * Maintained as a counter that if it gets to large (above 10) will stop workflow processing. This is to avoid
         * an infinite loop when there are misconfigured workflow rules
         * @var int
         */
        protected $depth = 0;

        public function init()
        {
            if ($this->enabled)
            {
                $observedModels = array();
                $modules = Module::getModuleObjects();
                foreach ($modules as $module)
                {
                    try
                    {
                        $modelClassName = $module->getPrimaryModelName();
                        if($modelClassName != null && $module::canHaveWorkflow() &&
                            is_subclass_of($modelClassName, 'Item') &&
                            !in_array($modelClassName, $observedModels))
                        {
                            $observedModels[]           = $modelClassName;
                            $modelClassName::model()->attachEventHandler('onBeforeSave', array($this, 'processWorkflowBeforeSave'));
                            $modelClassName::model()->attachEventHandler('onAfterSave', array($this, 'processWorkflowAfterSave'));
                        }
                    }
                    catch (NotSupportedException $e)
                    {
                    }
                }
            }
        }

        /**
         * Given a event, process any workflow rules
         * @param CEvent $event
         */
        public function processWorkflowBeforeSave(CEvent $event)
        {
            $model                   = $event->sender;
            if($model->getScenario() != 'autoBuildDatabase' && $model->shouldProcessWorkflowOnSave())
            {
                $triggeredByUser              = Yii::app()->user->userModel;
                Yii::app()->user->userModel = WorkflowUtil::getUserToRunWorkflowsAs();
                $model->setDoNotProcessWorkflowOnSave();
                SavedWorkflowsUtil::resolveBeforeSaveByModel($model, $triggeredByUser);
                $model->setProcessWorkflowOnSave();
                Yii::app()->user->userModel = $triggeredByUser;
            }
        }

        /**
         * Given a event, process any workflow rules
         * @param CEvent $event
         */
        public function processWorkflowAfterSave(CEvent $event)
        {
            $model                   = $event->sender;
            if($model->getScenario() != 'autoBuildDatabase' && $this->depth < 11 && $model->shouldProcessWorkflowOnSave())
            {
                $this->depth                = $this->depth + 1;
                $triggeredByUser            = Yii::app()->user->userModel;
                Yii::app()->user->userModel = WorkflowUtil::getUserToRunWorkflowsAs();
                $model->setDoNotProcessWorkflowOnSave();
                SavedWorkflowsUtil::resolveAfterSaveByModel($model, $triggeredByUser);
                $model->setProcessWorkflowOnSave();
                Yii::app()->user->userModel = $triggeredByUser;
                $this->depth                = $this->depth - 1;
            }
            elseif($this->depth > 10)
            {
                $message                      = new NotificationMessage();
                $message->htmlContent         = Zurmo::t('WorkflowsModule', 'The combination of workflow rules setup caused ' .
                                                'an infinite loop and processing was stopped prematurely while saving the ' .
                                                'following record: {modelName}', array('{modelName}' => strval($model)));
                $url                          = Yii::app()->createAbsoluteUrl('workflows/default/list');
                $message->htmlContent        .= "<br/>" . ZurmoHtml::link(Zurmo::t('WorkflowsModule', 'Manage Workflows'), $url);
                $rules                        = new WorkflowMaximumDepthNotificationRules();
                NotificationsUtil::submit($message, $rules);
            }
        }

        /**
         * Function for testing only. Do not use otherwise.
         * @param $depth
         */
        public function setDepth($depth)
        {
            assert('is_int($depth)');
            $this->depth = $depth;
        }
    }
?>