<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * Helper class for working with WorkflowMessageInQueue models
     */
    class WorkflowMessageInQueueUtil
    {
        /**
         * @param WorkflowMessageInQueue $model
         * @return string
         */
        public static function renderSummaryContent(WorkflowMessageInQueue $model)
        {
            $params          = array('label' => strval($model->savedWorkflow), 'wrapLabel' => false);
            $moduleClassName = $model->getModuleClassName();
            $moduleId        = $moduleClassName::getDirectoryName();
            $element         = new DetailsLinkActionElement('default', $moduleId, $model->savedWorkflow->id, $params);
            $relatedModel    = self::resolveModel($model);
            return $element->render() . ' &mdash; <span class="less-pronounced-text">' . self::resolveModelContent($relatedModel) . '</span>';
        }

        /**
         * @param WorkflowMessageInQueue $workflowMessageInQueue
         * @return An|RedBeanModel
         */
        protected static function resolveModel(WorkflowMessageInQueue $workflowMessageInQueue)
        {
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($workflowMessageInQueue->modelClassName);
            return $workflowMessageInQueue->modelItem->castDown(array($modelDerivationPathToItem));
        }

        /**
         * @param RedBeanModel $model
         * @return string
         */
        protected static function resolveModelContent(RedBeanModel $model)
        {
            $security = new DetailsActionSecurity(Yii::app()->user->userModel, $model);
            if($security->canUserPerformAction())
            {
                $params              = array('label' => strval($model), 'wrapLabel' => false);
                $moduleClassName     = $model->getModuleClassName();
                $moduleId            = $moduleClassName::getDirectoryName();
                $relatedModelElement = new DetailsLinkActionElement('default', $moduleId, $model->id, $params);
                return $relatedModelElement->render();
            }
        }
    }
?>