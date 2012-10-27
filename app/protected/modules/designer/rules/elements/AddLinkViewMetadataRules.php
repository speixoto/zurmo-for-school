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
     * Autopopulate a link element if available. Tries to find a
     * name field or fullName field and attaches a link property to it.
     *
     */
    class AddLinkViewMetadataRules
    {
        /**
         * This method relies on some assumptions that might not always be true. It assumes if you are using a related
         * link, the type is exactly named as the model.  This might not be true, but for now this is the best way to
         * handle this.
         * @param array $elementInformation
         * @param array $elementMetadata
         */
        public static function resolveElementMetadata($elementInformation, & $elementMetadata)
        {
            $modelNames = static::getAcceptableModelsForAttributeNames();
            if ($elementInformation['attributeName'] == 'name' ||
            $elementInformation['type'] == 'FullName' || in_array($elementInformation['type'], $modelNames))
            {
                $elementMetadata['isLink'] = true;
            }
        }

        protected static function getAcceptableModelsForAttributeNames()
        {
            $modules = Module::getModuleObjects();
            $modelNames  = array();
            foreach ($modules as $module)
            {
                if (get_class($module) != 'UsersModule')
                {
                    try
                    {
                        $modelNames[] = $module::getPrimaryModelName();
                    }
                    catch (NotSupportedException $e)
                    {
                    }
                }
            }
            return $modelNames;
        }
    }
?>