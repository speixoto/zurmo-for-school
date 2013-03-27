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
     * Helper class using during workflow action processing. When processing an action, each action has a collection
     * of attributes to set values on against the model.  Sometimes the triggered model is different than the model
     * being updated and is needed for reference.  This adapter will also provide the information regarding the triggered
     * user as well since this can be different than Yii::app()->user->userModel, since actions need to be processed
     * with elevated privileges.
     */
    class WorkflowActionProcessingModelAdapter
    {
        protected $model;

        protected $triggeredByUser;

        protected $triggeredModel;

        public function __construct(RedBeanModel $model, User $triggeredByUser, RedBeanModel $triggeredModel = null)
        {
            $this->model = $model;
            $this->triggeredByUser = $triggeredByUser;
            if($triggeredModel == null)
            {
                $this->triggeredModel = $model;
            }
            else
            {
                $this->triggeredModel = $triggeredModel;
            }
        }

        public function getModel()
        {
            return $this->model;
        }

        public function getTriggeredModel()
        {
            return $this->triggeredModel;
        }

        public function getTriggeredByUser()
        {
            return $this->triggeredByUser;
        }
    }
?>