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
     * Base class for mapping rule forms that are not derived but have a specific attribute on a model.
     */
    abstract class ModelAttributeMappingRuleForm extends MappingRuleForm
    {
        /**
         * Refers to the model that is associated with the import rules. If your import rules are for accounts, then
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

        public function __construct($modelClassName, $modelAttributeName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($modelAttributeName)');
            $this->modelClassName     = $modelClassName;
            $this->modelAttributeName = $modelAttributeName;
        }
    }
?>