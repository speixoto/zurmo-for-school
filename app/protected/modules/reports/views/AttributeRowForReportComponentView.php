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

    class AttributeRowForReportComponentView extends View
    {
        protected $rowNumber;

        protected $inputPrefixData;

        protected $attribute;

        public function __construct($rowNumber, $inputPrefixData, $attribute)
        {
            assert('is_int($rowNumber)');
            assert('is_array($inputPrefixData)');
            assert('is_string($attribute)');
            $this->rowNumber                          = $rowNumber;
            $this->inputPrefixData                    = $inputPrefixData;
            $this->attribute                          = $attribute;
        }

        public function render()
        {
            return $this->renderContent();
        }

        protected function renderContent()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                                            array_merge($this->inputPrefixData, array('structurePosition')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                                            array_merge($this->inputPrefixData, array('structurePosition')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId, 'class' => 'structure-position');

            $content  = '<div>';
            $content .= ZurmoHtml::tag('span', array('class' => 'report-attribute-row-number-label'),
                                                     ($this->rowNumber + 1) . '.');
            $content .= $this->renderAttributeContent();
            $content .= ZurmoHtml::hiddenField($hiddenInputName, ($this->rowNumber + 1), $idInputHtmlOptions);
            $content .= '</div>';
            $content .= ZurmoHtml::link('_', '#', array('class' => 'remove-report-attribute-row-link'));
            return ZurmoHtml::tag('div', array('class' => 'report-attribute-row'), $content);
        }

        protected function renderAttributeContent()
        {
            $content = 'input attribute: ' . $this->attribute . "<BR>";
            return $content;
        }
    }
?>