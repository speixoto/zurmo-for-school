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
     * Helper class for adapting an attribute to an Element for Wizard driven modules
     */
    abstract class WizardModelAttributeToElementAdapter
    {
        /**
         * @var array
         */
        protected $inputPrefixData;

        /**
         * @var ConfigurableMetadataModel
         */
        protected $model;

        /**
         * @var WizardActiveForm
         */
        protected $form;

        /**
         * @var string
         */
        protected $treeType;

        /**
         * @param array $inputPrefixData
         * @param ConfigurableMetadataModel $model
         * @param WizardActiveForm $form
         * @param string $treeType
         */
        public function __construct(Array $inputPrefixData, $model, $form, $treeType)
        {
            assert('count($inputPrefixData) > 1');
            assert('$model instanceof ConfigurableMetadataModel');
            assert('$form instanceof WizardActiveForm');
            assert('is_string($treeType)');
            $this->inputPrefixData      = $inputPrefixData;
            $this->model                = $model;
            $this->form                 = $form;
            $this->treeType             = $treeType;
        }

        /**
         * @param string $innerContent
         * @param string $content
         * @param null|string $class
         */
        protected static function resolveDivWrapperForContent($innerContent, & $content, $class = null)
        {
            ZurmoHtml::resolveDivWrapperForContent($innerContent, $content, $class);
        }

        /**
         * @return string
         */
        protected function renderAttributeIndexOrDerivedType()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                                            array_merge($this->inputPrefixData, array('attributeIndexOrDerivedType')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                                            array_merge($this->inputPrefixData, array('attributeIndexOrDerivedType')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId);
            return ZurmoHtml::hiddenField($hiddenInputName, $this->model->getAttributeIndexOrDerivedType(),
                                          $idInputHtmlOptions);
        }
    }
?>