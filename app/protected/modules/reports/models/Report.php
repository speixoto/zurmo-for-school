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

    class Report
    {
        const TYPE_ROWS_AND_COLUMNS = 'RowsAndColumns';

        const TYPE_SUMMATION        = 'Summation';

        const TYPE_MATRIX           = 'Matrix';

        private $description;

        private $explicitReadWriteModelPermissions;

        /**
         * Id of the saved report if it has already been saved
         * @var integer
         */
        private $id;

        private $moduleClassName;

        private $name;

        private $owner;

        private $type;

        public static function getTypeDropDownArray()
        {
            return array(self::TYPE_ROWS_AND_COLUMNS  => Yii::t('Default', 'Rows and Columns'),
                         self::TYPE_SUMMATION         => Yii::t('Default', 'Summation'),
                         self::TYPE_MATRIX            => Yii::t('Default', 'Matrix'),);
        }

        /**
         * Based on the current user, return the reportable modules and thier display labels.  Only include modules
         * that the user has a right to access.
         * @return array of module class names and display labels.
         */
        public static function getReportableModulesAndLabelsForCurrentUser()
        {
            $moduleClassNamesAndLabels = array();
            $modules = Module::getModuleObjects();
            foreach (self::getReportableModulesClassNamesCurrentUserHasAccessTo() as $moduleClassName)
            {
                $moduleClassNamesAndLabels[$moduleClassName] = $moduleClassName::getModuleLabelByTypeAndLanguage('Plural');
            }
            return $moduleClassNamesAndLabels;
        }

        public static function getReportableModulesClassNamesCurrentUserHasAccessTo()
        {
            $moduleClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                if($module::isReportable())
                {
                    if (RightsUtil::canUserAccessModule(get_class($module), Yii::app()->user->userModel))
                    {
                        $moduleClassNames[] = get_class($module);
                    }
                }
            }
            return $moduleClassNames;
        }

        public function getModuleClassName()
        {
            return $this->moduleClassName;
        }

        public function setModuleClassName($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $this->moduleClassName = $moduleClassName;
        }

        public function getDescription()
        {
            return $this->description;
        }

        public function setDescription($description)
        {
            assert('is_string($description)');
            $this->description = $description;
        }

        public function getId()
        {
            return $this->id;
        }

        public function setId($id)
        {
            assert('is_int($id)');
            $this->id = $id;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setName($name)
        {
            assert('is_string($name)');
            $this->name = $name;
        }

        public function getType()
        {
            return $this->type;
        }

        public function setType($type)
        {
            assert('is_string($type)');
            $this->type = $type;
        }

        public function isNew()
        {
            //todo:
            return true;
        }

        public function getOwner()
        {
            if($this->owner == null)
            {
                $this->owner = Yii::app()->user->userModel;
            }
            return $this->owner;
        }

        public function setOwner(User $owner)
        {
            $this->owner = $owner;
        }

        public function getExplicitReadWriteModelPermissions()
        {
            if($this->explicitReadWriteModelPermissions == null)
            {
                $this->explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            }
            return $this->explicitReadWriteModelPermissions;
        }

        public function setExplicitReadWriteModelPermissions(ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            $this->explicitReadWriteModelPermissions = $explicitReadWriteModelPermissions;
        }
    }
?>