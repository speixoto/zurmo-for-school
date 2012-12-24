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

    class ReportResultsRowData extends CComponent
    {
        const ATTRIBUTE_NAME_PREFIX = 'attribute';

        protected $displayAttributes;

        protected $modelsByAliases  = array();

        protected $selectedColumnNamesAndValues = array();

        public static function resolveAttributeNameByKey($key)
        {
            assert('is_numeric($key) || is_string($key)');
            return self::ATTRIBUTE_NAME_PREFIX . $key;
        }

        public function __construct(array $displayAttributes)
        {
            $this->displayAttributes = $displayAttributes;
        }

        public function __get($name)
        {
            $parts = explode(self::ATTRIBUTE_NAME_PREFIX, $name);
            if(count($parts) == 2 && $parts[1] != null)
            {
                return $this->resolveValueFromModel($parts[1]);
            }
            if(isset($this->selectedColumnNamesAndValues[$name]))
            {
                return $this->selectedColumnNamesAndValues[$name];
            }
            return parent::__get($name);
        }

        public function addModelAndAlias(RedBeanModel $model, $alias)
        {
            assert('is_string($alias)');
            if(isset($this->modelsByAliases[$alias]))
            {
                throw new NotSupportedException();
            }
            $this->modelsByAliases[$alias] = $model;
        }

        public function addSelectedColumnNameAndValue($columnName, $value)
        {
            if(isset($this->selectedColumnNamesAndValues[$columnName]))
            {
                throw new NotSupportedException();
            }
            $this->selectedColumnNamesAndValues[$columnName] = $value;
        }

        public function getModel($attribute)
        {
            list($notUsed, $displayAttributeKey) = explode(self::ATTRIBUTE_NAME_PREFIX, $attribute);
            if($displayAttributeKey != null)
            {
                return $this->resolveModel($displayAttributeKey);
            }
            throw new NotSupportedException();
        }

        protected function resolveModel($displayAttributeKey)
        {
            if(!isset($this->displayAttributes[$displayAttributeKey]))
            {
                throw new NotSupportedException();
            }
            $displayAttribute = $this->displayAttributes[$displayAttributeKey];
            $modelAlias       = $displayAttribute->getModelAliasUsingTableAliasName();
            if(!isset($this->modelsByAliases[$modelAlias]))
            {
                return null;
            }
            return $this->getModelByAlias($modelAlias);
        }

        protected function resolveValueFromModel($displayAttributeKey)
        {
            if(!isset($this->displayAttributes[$displayAttributeKey]))
            {
                throw new NotSupportedException();
            }
            $displayAttribute = $this->displayAttributes[$displayAttributeKey];
            $modelAlias       = $displayAttribute->getModelAliasUsingTableAliasName();
            if(!isset($this->modelsByAliases[$modelAlias]))
            {
                return null;
            }
            $attribute        = $displayAttribute->getResolvedAttribute();
            $model            = $this->getModelByAlias($modelAlias);
            return $model->$attribute;
        }

        protected function getModelByAlias($alias)
        {
            if(!isset($this->modelsByAliases[$alias]))
            {
                throw new NotSupportedException();
            }
            return $this->modelsByAliases[$alias];
        }
    }
?>