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
     * Class that defines the email alerts used for a workflow
     */
    class EmailAlertForWorkflowForm extends ConfigurableMetadataModel
    {
        const SEND_FROM_TYPE_DEFAULT      = 'Default';

        const SEND_FROM_TYPE_CUSTOM       = 'Custom';

        /**
         * Similar to the types defined in ComponentForWorkflowForm like TYPE_EMAIL_ALERTS.
         */
        const TYPE_EMAIL_ALERT_RECIPIENTS = 'EmailAlertRecipients';

        /**
         * Utilized by arrays to define the element that is for the actionAttributes
         */
        const EMAIL_ALERT_RECIPIENTS     = 'EmailAlertRecipients';

        public $emailTemplateId;

        public $sendAfterDurationSeconds;

        public $sendFromType;

        public $sendFromName;

        public $sendFromAddress;

        public $logEmail;

        /**
         * @var string
         */
        private $_workflowType;

        /**
         * @var array of WorkflowActionAttributeForms indexed by attributeNames
         */
        private $_emailAlertRecipients = array();

        /**
         * @var string string references the modelClassName of the workflow itself
         */
        private $_modelClassName;

        /**
         * @param string $modelClassName
         * @param string $workflowType
         */
        public function __construct($modelClassName, $workflowType)
        {
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            $this->_modelClassName = $modelClassName;
            $this->_workflowType   = $workflowType;
        }

        /**
         * @return int
         */
        public function getEmailAlertRecipientFormsCount()
        {
            return count($this->_emailAlertRecipients);
        }

        /**
         * @return array
         */
        public function getEmailAlertRecipients()
        {
            return $this->_emailAlertRecipients;
        }

        public function getWorkflowType()
        {
            return $this->_workflowType;
        }

        /**
         * @return array
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('emailTemplateId',          'required'),
                array('sendAfterDurationSeconds', 'type', 'type' => 'integer'),
                array('sendFromType',             'type',  'type' => 'string'),
                array('sendFromType',             'validateSendFromType'),
                array('sendFromName',             'type',  'type' => 'string'),
                array('sendFromAddress',          'type',  'type' => 'string'),
                array('logEmail',                 'boolean'),
            ));
        }

        /**
         * @return array
         */
        public function attributeLabels()
        {
            return array('emailTemplateId'          => Zurmo::t('WorkflowsModule', 'Email Template'),
                         'sendAfterDurationSeconds' => Zurmo::t('WorkflowsModule', 'Send'),
                         'sendFromType'             => Zurmo::t('WorkflowsModule', 'Send From'),
                         'sendFromName'             => Zurmo::t('WorkflowsModule', 'From Name'),
                         'sendFromAddress'          => Zurmo::t('WorkflowsModule', 'From Address'),
                         'logEmail'                 => Zurmo::t('WorkflowsModule', 'Log Email'),
            );
        }

        /**
         * Process all attributes except 'emailAlertRecipients' first
         * @param $values
         * @param bool $safeOnly
         * @throws NotSupportedException if the post values data is malformed
         */
        public function setAttributes($values, $safeOnly = true)
        {
            $recipients = null;
            if(isset($values[self::EMAIL_ALERT_RECIPIENTS]))
            {
                $recipients = $values[self::EMAIL_ALERT_RECIPIENTS];
                unset($values[self::EMAIL_ALERT_RECIPIENTS]);
                $this->_emailAlertRecipients = array();
            }
            parent::setAttributes($values, $safeOnly);
            if($recipients != null)
            {
                foreach($recipients as $recipientData)
                {
                    if(!isset($recipientData['type']))
                    {
                        throw new NotSupportedException();
                    }
                    $form = WorkflowEmailAlertRecipientFormFactory::make($recipientData['type'], $this->_modelClassName);
                    $form->setAttributes($recipientData);
                    $this->_emailAlertRecipients[] = $form;
                }
            }
        }

        /**
         * @return bool
         */
        public function validateSendFromType()
        {
            if($this->type == self::SEND_FROM_TYPE_CUSTOM)
            {
                $validated = true;
                if($this->sendFromName == null)
                {
                    $this->addError('sendFromName', Zurmo::t('WorkflowsModule', 'From Name cannot be blank.'));
                    $validated = false;
                }
                if($this->sendFromAddress == null)
                {
                    $this->addError('sendFromName', Zurmo::t('WorkflowsModule', 'From Email Address cannot be blank.'));
                    $validated = false;
                }
                return $validated;
            }
            $this->addError('type', Zurmo::t('WorkflowsModule', 'Invalid Send From Type'));
            return false;
        }

        /**
         * @return bool
         */
        public function beforeValidate()
        {
            if(!$this->validateRecipients())
            {
                return false;
            }
            return parent::beforeValidate();
        }

        /**
         * @return bool
         */
        public function validateRecipients()
        {
            $passedValidation = true;
            if(count($this->_emailAlertRecipients) == 0)
            {
                $this->addError( 'displayAttributes', Zurmo::t('WorkflowsModule', 'At least one recipient must be added'));
                return false;
            }
            foreach($this->_emailAlertRecipients as $key => $workflowEmailAlertRecipientForm)
            {
                if(!$workflowEmailAlertRecipientForm->validate())
                {
                    foreach($workflowEmailAlertRecipientForm->getErrors() as $attribute => $errorArray)
                    {
                        assert('is_array($errorArray)');
                        $attributePrefix = static::resolveErrorAttributePrefix($key);
                        $this->addError( $attributePrefix . $attribute, $errorArray[0]);
                    }
                    $passedValidation = false;
                }
            }
            return $passedValidation;
        }

        public function getSendFromTypeValuesAndLabels()
        {
            $data                               = array();
            $data[self::SEND_FROM_TYPE_DEFAULT] = Zurmo::t('WorkflowsModule', 'Default System From Name/Address'); //todo: relabel since we don't define this persay anywhere
            $data[self::SEND_FROM_TYPE_CUSTOM]  = Zurmo::t('WorkflowsModule', 'Custom From Name/Address');
            return $data;
        }

        public function getSendAfterDurationValuesAndLabels()
        {
            $data = array();
            WorkflowUtil::resolveSendAfterDurationData($data);
            return $data;
        }

        /**
         * @param $attributeName string
         * @return string
         */
        protected static function resolveErrorAttributePrefix($attributeName)
        {
            assert('is_int($attributeName)');
            return self::EMAIL_ALERT_RECIPIENTS . '_' .  $attributeName . '_';
        }
    }
?>