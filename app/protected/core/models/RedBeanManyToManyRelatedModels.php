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

    $basePath = Yii::app()->getBasePath();
    require_once("$basePath/../../redbean/rb.php");

    /**
     * Relates models as RedBean associations, so that
     * the relationship is M:N via a join table.
     */
    class RedBeanManyToManyRelatedModels extends RedBeanMutableRelatedModels
    {
        protected $inside = false;

        protected $linkName;

        /**
         * Constructs a new RedBeanModels which is a collection of classes extending model.
         * The models are created lazily.
         * Models are only constructed with beans by the model. Beans are
         * never used by the application directly.
         */
        public function __construct(RedBean_OODBBean $bean, $modelClassName, $linkType, $linkName = null)
        {
            assert('is_string($modelClassName)');
            assert('$modelClassName != ""');
            assert('is_int($linkType)');
            assert('is_string($linkName) || $linkName == null');
            assert('($linkType == RedBeanModel::LINK_TYPE_ASSUMPTIVE && $linkName == null) ||
                    ($linkType == RedBeanModel::LINK_TYPE_SPECIFIC && $linkName != null)');
            $this->modelClassName        = $modelClassName;
            $tableName                   = $modelClassName::getTableName();
            $this->bean                  = $bean;
            $this->linkName              = $linkName;
            if ($this->bean->id > 0)
            {
                $this->relatedBeansAndModels = array_values(ZurmoRedBean::related($this->bean, $tableName, null, array(),
                    $this->getTableName(ZurmoRedBean::dispense($tableName))));
            }
            else
            {
                $this->relatedBeansAndModels = array();
            }
        }

        public function getModelClassName()
        {
            return $this->modelClassName;
        }

        public function getErrors($attributeNameOrNames = null)
        {
            if (!$this->inside)
            {
                $this->inside = true;
                $errors = parent::getErrors($attributeNameOrNames);
                $this->inside = false;
            }
            else
            {
                $errors = array();
            }
            return $errors;
        }

        public function validate(array $attributeNames = null)
        {
            // Many many relations do not validation the related models.
            return true;
        }

        public function save($runValidation = true)
        {
            foreach ($this->deferredRelateBeans as $bean)
            {
                $tableName = $this->getTableName($bean);
                ZurmoRedBean::associate($this->bean, $bean, null, $tableName);
            }
            $this->deferredRelateBeans = array();
            foreach ($this->deferredUnrelateBeans as $bean)
            {
                $tableName = $this->getTableName($bean);
                ZurmoRedBean::unassociate($this->bean, $bean, false, $tableName);
            }
            $this->deferredUnrelateBeans = array();
            return true;
        }

        public function getTableName(RedBean_OODBBean $bean = null)
        {
            if ($bean == null)
            {
                $modelClassName = $this->modelClassName;
                $bean = ZurmoRedBean::dispense($modelClassName::getTableName());
            }
            $types = array($this->bean->getMeta("type"), $bean->getMeta("type"));
            return static::resolveTableNamesWithLinkName($types, $this->linkName);
        }

        public static function getTableNameByModelClassNames($modelClassName, $anotherModelClassName, $linkName = null)
        {
            $modelTableName         = $modelClassName::getTableName();
            $anotherModelTableName  = $anotherModelClassName::getTableName();
            $tableNames = array($modelTableName, $anotherModelTableName);
            return static::resolveTableNamesWithLinkName($tableNames, $linkName);
        }

        protected static function resolveTableNamesWithLinkName(array $tableNames, $linkName = null)
        {
            sort($tableNames);
            $tableName = implode("_", $tableNames);
            if ($linkName != null)
            {
                $tableName = strtolower($linkName) . '_' . $tableName;
            }
            return $tableName;
        }
    }
?>