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
     * Base class for working with action attributes.
     */
    abstract class WorkflowActionAttributeForm extends ConfigurableMetadataModel
    {
        const TYPE_STATIC = 'Static';

        /**
         * @var string Static for example, Can also be Dynamic as well as other types specified by children
         */
        public $type;

        /**
         * @var mixed
         */
        public $value;

        /**
         * owner__User for example uses this property to define the owner's name which can then be used in the user
         * interface
         * @var string
         */
        public $stringifiedModelForValue;

        /**
         * @var boolean if the attribute should have a value whether static or dynamic. In the user interface this surfaces
         * as a checkbox next to each attribute in the workflow wizard
         */
        public $shouldSetValue;

        /**
         * Refers to the model that is associated with the action attribute. If your action attribut is on accounts, then
         * this is going to be the Account model class name. However this could also be a relation model class name
         * if the AttributeIndex is referencing a related attribute.
         * @var string
         */
        protected $modelClassName;

        /**
         * Mapped attribute name or related attribute name if the attributeIndex is for a relation attribute.
         * @see $modelClassName
         * @var string
         */
        protected $modelAttributeName;

        /**
         * @return string - If the class name is BooleanWorkflowActionAttributeForm,
         * then 'Boolean' will be returned.
         */
        public static function getFormType()
        {
            $type = get_called_class();
            $type = substr($type, 0, strlen($type) - strlen('WorkflowActionAttributeForm'));
            return $type;
        }

        public function __construct($modelClassName, $modelAttributeName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($modelAttributeName)');
            $this->modelClassName     = $modelClassName;
            $this->modelAttributeName = $modelAttributeName;
        }

        /**
         * Override to properly handle retrieving rule information from the model for the attribute name.
         */
        public function rules()
        {
            $rules = array_merge(parent::rules(), array(
                array('type',                     'type', 'type' => 'string'),
                array('type',                     'required'),
                array('value',                    'safe'),
                array('value',                    'validateValue'),
                array('stringifiedModelForValue', 'type', 'type' => 'string'),
                array('shouldSetValue',           'boolean'),
            ));
            $applicableRules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                getApplicableRulesByModelClassNameAndAttributeName(
                $this->modelClassName,
                $this->modelAttributeName,
                'value');
            return array_merge($rules, $applicableRules);
        }

        public function attributeLabels()
        {
            return array();
        }

        /**
         * Value is required based on the type. Override in children as needed to add more scenarios.
         * @return bool
         */
        public function validateValue()
        {
            if($this->type == self::TYPE_STATIC && empty($this->value) && $this->shouldSetValue)
            {
                $this->addError('value', Zurmo::t('WorkflowsModule', 'Value cannot be blank.'));
                return false;
            }
            if($this->value != null && !$this->shouldSetValue)
            {
                $this->addError('value', Zurmo::t('WorkflowsModule', 'Value should not be set.'));
                return false;
            }
            return true;
        }
    }
?>