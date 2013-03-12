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
     * Form to work with dynamic triggered model relation users for an email alert recipient
     */
    class DynamicTriggeredModelRelationUserWorkflowEmailAlertRecipientForm extends DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm
    {
        /**
         * When sending email alerts on related models, if there are MANY related models RELATION_FILTER_ALL means the
         * action will be performed on all related models
         */
        const RELATION_FILTER_ALL   = 'RelationFilterAll';

        /**
         * @var string
         */
        public $relation;

        /**
         * self::RELATION_FILTER_ALL is the only supported value.  Eventually additional support will be added to filter
         * related models.  An example is if you are creating a workflow on Account.  And you want to modify related opportunities.
         * Currently you can only modify all opportunities.
         * @var relationFilter
         */
        public $relationFilter = self::RELATION_FILTER_ALL;

        public static function getTypeLabel()
        {
            return Zurmo::t('WorkflowModule', 'A person associated with a related record');
        }

        /**
         * Override to add relation attribute
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(
                      array('relation',         'type', 'type' =>  'string'),
                      array('relation',         'required'),
                      array('relationFilter',  	'type', 'type' => 'string'),
                      array('relationFilter',   'validateRelationFilter')));
        }

        /**
         * @return bool
         */
        public function validateRelationFilter()
        {
            if($this->relationFilter == self::RELATION_FILTER_ALL)
            {
                return true;
            }
            $this->addError('relationFilter', Zurmo::t('WorkflowsModule', 'Invalid Relation Filter'));
            return false;
            return true;
        }

        public function getRelationValuesAndLabels()
        {
            $modelClassName = $this->modelClassName;
            $adapter        = ModelRelationsAndAttributesToWorkflowAdapter::make($modelClassName::getModuleClassName(),
                                                                                 $modelClassName, $this->workflowType);
            $valueAndLabels = array();
            foreach($adapter->getSelectableRelationsDataForEmailAlertRecipientModelRelation() as $relation => $data)
            {
                $valueAndLabels[$relation] = $data['label'];
            }
            return $valueAndLabels;
        }
    }
?>