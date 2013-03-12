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
     * Base class for working with email alert recipients.
     */
    abstract class WorkflowEmailAlertRecipientForm extends ConfigurableMetadataModel
    {
        const TYPE_DYNAMIC_TRIGGERED_MODEL_USER             = 'DynamicTriggeredModelUser';

        const TYPE_DYNAMIC_TRIGGERED_MODEL_RELATION_USER    = 'DynamicTriggeredModelRelationUser';

        const TYPE_STATIC_ROLE                              = 'StaticRole';

        const TYPE_DYNAMIC_TRIGGERED_USER                   = 'DynamicTriggeredUser';

        const TYPE_STATIC_USER                              = 'StaticUser';

        const TYPE_STATIC_ADDRESS                           = 'StaticAddress';

        const TYPE_STATIC_GROUP                             = 'StaticGroup';

        /**
         * @var string Type of recipient
         */
        public $type;

        /**
         * @var string type of recipient, to, cc, or bcc
         */
        public $recipientType;

        /**
         * static user for example would populate this with the stringified name of the user.
         * interface
         * @var string
         */
        protected $stringifiedModelForValue;

        /**
         * Refers to the model that is associated with the workflow rule.
         * @var string
         */
        protected $modelClassName;

        /**
         * @var string
         */
        protected $workflowType;

        /**
         * @throws NotImplementedException if not implemented by a child class
         * @return string label content
         */
        public static function getTypeLabel()
        {
            throw new NotImplementedException();
        }
        /**
         * @return string - If the class name is DynamicTriggeredModelRelationUserWorkflowEmailAlertRecipientForm,
         * then 'DynamicTriggeredModelRelationUser' will be returned.
         */
        public static function getFormType()
        {
            $type = get_called_class();
            $type = substr($type, 0, strlen($type) - strlen('WorkflowEmailAlertRecipientForm'));
            return $type;
        }

        public function __construct($modelClassName, $workflowType)
        {
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            $this->modelClassName     = $modelClassName;
            $this->workflowType       = $workflowType;
        }

        /**
         * Override to properly handle retrieving rule information from the model for the attribute name.
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('type',                     'type', 'type' => 'string'),
                array('type',                     'required'),
                array('recipientType',            'type', 'type' => 'string'),
                array('recipientType',            'required'),
            ));
        }

        /**
         * @return array
         */
        public static function getTypeValuesAndLabels()
        {
            $data = array();
            $data[static::TYPE_DYNAMIC_TRIGGERED_MODEL_USER]             =
                DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm::getTypeLabel();
            $data[static::TYPE_DYNAMIC_TRIGGERED_MODEL_RELATION_USER]    =
                DynamicTriggeredModelRelationUserWorkflowEmailAlertRecipientForm::getTypeLabel();
            $data[static::TYPE_STATIC_ROLE]                              =
                StaticRoleWorkflowEmailAlertRecipientForm::getTypeLabel();
            $data[static::TYPE_DYNAMIC_TRIGGERED_USER]                   =
                DynamicTriggeredUserWorkflowEmailAlertRecipientForm::getTypeLabel();
            $data[static::TYPE_STATIC_USER]                              =
                StaticUserWorkflowEmailAlertRecipientForm::getTypeLabel();
            $data[static::TYPE_STATIC_ADDRESS]                            =
                StaticAddressWorkflowEmailAlertRecipientForm::getTypeLabel();
            $data[static::TYPE_STATIC_GROUP]                             =
                StaticGroupWorkflowEmailAlertRecipientForm::getTypeLabel();
            return $data;
        }
    }
?>