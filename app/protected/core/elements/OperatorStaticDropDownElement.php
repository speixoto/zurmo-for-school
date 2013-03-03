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
     * Class used by reporting or workflow to show available operator types in a dropdown.
     */
    class OperatorStaticDropDownElement extends DataFromFormStaticDropDownFormElement
    {
        protected function getEditableHtmlOptions()
        {
            $htmlOptions = parent::getEditableHtmlOptions();
            if(isset($htmlOptions['class']))
            {
                $htmlOptions['class'] .= ' operatorType';
            }
            else
            {
                $htmlOptions['class']  = 'operatorType';
            }
            return $htmlOptions;
        }

        protected function renderControlEditable()
        {
            $content = parent::renderControlEditable();
            return $content;
        }

        protected function getDataAndLabelsModelPropertyName()
        {
            return 'getOperatorValuesAndLabels';
        }

        public static function getValueTypesRequiringFirstInput()
        {
            return array(OperatorRules::TYPE_EQUALS,
                         OperatorRules::TYPE_DOES_NOT_EQUAL,
                         OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO,
                         OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO,
                         OperatorRules::TYPE_GREATER_THAN,
                         OperatorRules::TYPE_LESS_THAN,
                         OperatorRules::TYPE_ONE_OF,
                         OperatorRules::TYPE_BETWEEN,
                         OperatorRules::TYPE_STARTS_WITH,
                         OperatorRules::TYPE_ENDS_WITH,
                         OperatorRules::TYPE_CONTAINS,
                         OperatorRules::TYPE_BECOMES,
                         OperatorRules::TYPE_WAS,
                         OperatorRules::TYPE_BECOMES_ONE_OF,
                         OperatorRules::TYPE_WAS_ONE_OF,
                        );
        }

        public static function getValueTypesRequiringSecondInput()
        {
            return array(OperatorRules::TYPE_BETWEEN);
        }
    }
?>