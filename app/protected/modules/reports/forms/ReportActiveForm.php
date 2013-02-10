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
     * ActiveForm used for report forms.  This is needed because report forms are dynamic and can have different
     * quantity of rows and have similar inputs for different components.  The report wizard posts the all definitions
     * for a report in a single POST and this class helps manage the names/ids effectively of those form inputs
     */
    class ReportActiveForm extends ZurmoActiveForm
    {
        /**
         * @var array array
         */
        protected $inputPrefixData;

        /**
         * @param array $inputPrefixData
         */
        public function setInputPrefixData(Array $inputPrefixData)
        {
            $this->inputPrefixData = $inputPrefixData;
        }

        public function clearInputPrefixData()
        {
            $this->inputPrefixData = null;
        }

        protected function resolveId($model, $attribute)
        {
            $id = CHtml::activeId($model, $attribute);
            if($this->inputPrefixData == null)
            {
                return $id;
            }
            $inputIdPrefix  = Element::resolveInputIdPrefixIntoString($this->inputPrefixData);
            return str_replace(get_class($model), $inputIdPrefix, $id);
        }
    }
?>