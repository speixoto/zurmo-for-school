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
     * Class for rendering an extra row when 'Add Field' is clicked in the advanced search.
     */
    class DynamicSearchExtraRowView extends View
    {
        protected $searchableAttributeIndicesAndDerivedTypes;

        protected $rowNumber;

        protected $suffix;

        protected $formModelClassName;

        protected $ajaxOnChangeUrl;

        public function __construct($searchableAttributeIndicesAndDerivedTypes, $rowNumber,
                                    $suffix, $formModelClassName, $ajaxOnChangeUrl)
        {
            assert('is_array($searchableAttributeIndicesAndDerivedTypes)');
            assert('is_int($rowNumber)');
            assert('is_string($suffix)');
            assert('is_string($formModelClassName)');
            assert('is_string($ajaxOnChangeUrl)');
            $this->searchableAttributeIndicesAndDerivedTypes    = $searchableAttributeIndicesAndDerivedTypes;
            $this->rowNumber                                    = $rowNumber;
            $this->suffix                                      = $suffix;
            $this->formModelClassName                           = $formModelClassName;
            $this->ajaxOnChangeUrl                              = $ajaxOnChangeUrl;
        }

        public function render()
        {
            return $this->renderContent();
        }

        protected function renderContent()
        {
            $this->renderScripts();
            $content  = CHtml::tag('span', array('class' => 'dynamic-search-row-number-label'), $this->rowNumber . '. ');
            $content .= $this->renderAttributeDropDownContent();
            $content .= CHtml::tag('div', array('id' => $this->getInputsDivId()), null);
            $content .= '&#160;' . CHtml::link(Yii::t('Default', 'Remove Field'),
                        '#', array('class' => 'remove-extra-dynamic-search-row-link'));
            return $content;
        }

        /**
         * Renders special scripts required for displaying the view.  Renders scripts for dropdown styling and interaction.
         */
        protected function renderScripts()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets')) . '/dropDownInteractions.js', CClientScript::POS_END);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets')) . '/jquery.dropkick-1.0.0.js', CClientScript::POS_END);
        }

        protected function renderAttributeDropDownContent()
        {
            $name        = $this->formModelClassName . '[dynamic][' . $this->rowNumber . '][attributeIndexOrDerivedType]';
            $id          = $this->formModelClassName . '_dynamic_' . $this->rowNumber . '_attributeIndexOrDerivedType';
            $htmlOptions = array('id' => $id,
                'empty' => Yii::t('Default', 'Select a field')
            );
            Yii::app()->clientScript->registerScript('AttributeDropDown' . $id,
                                                     $this->renderAttributeDropDownOnChangeScript($id,
                                                     $this->getInputsDivId(),
                                                     $this->ajaxOnChangeUrl));
            $content = CHtml::dropDownList($name,
                                           null,
                                           $this->searchableAttributeIndicesAndDerivedTypes,
                                           $htmlOptions);
            Yii::app()->clientScript->registerScript('mappingExtraColumnRemoveLink', "
            $('.remove-extra-dynamic-search-row-link').click( function()
                {
                    $(this).parent().remove();
                    //todo: rework visible count and counter.
                }
            );");
            return $content;
        }

        protected function renderAttributeDropDownOnChangeScript($id, $inputDivId, $ajaxOnChangeUrl)
        {
            $ajaxSubmitScript  = CHtml::ajax(array(
                    'type'    => 'GET',
                    'data'    => 'js:\'suffix=' . $this->suffix .
                                 '&attributeIndexOrDerivedType=\' + $(this).val()',
                    'url'     =>  $ajaxOnChangeUrl,
                    'replace' => '#' . $inputDivId,
            ));
            return "$('#" . $id . "').unbind('change'); $('#" . $id . "').bind('change', function()
            {
                $ajaxSubmitScript
            }
            );";
        }

        protected function getInputsDivId()
        {
            return $this->formModelClassName . '-dynamic-search-inputs-for-' . $this->suffix;
        }
    }
?>