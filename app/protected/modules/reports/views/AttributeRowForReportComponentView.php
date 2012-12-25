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
        public    $addWrapper = true;

        protected $elementAdapter;

        protected $rowNumber;

        protected $inputPrefixData;

        protected $attribute;

        protected $hasTrackableStructurePosition;

        protected $showRemoveLink;

        public function __construct($elementAdapter, $rowNumber, $inputPrefixData, $attribute,
                                    $hasTrackableStructurePosition, $showRemoveLink = true)
        {
            assert('$elementAdapter instanceof ReportAttributeToElementAdapter');
            assert('is_int($rowNumber)');
            assert('is_array($inputPrefixData)');
            assert('is_string($attribute)');
            assert('is_bool($hasTrackableStructurePosition)');
            assert(is_bool($showRemoveLink));
            $this->elementAdapter                     = $elementAdapter;
            $this->rowNumber                          = $rowNumber;
            $this->inputPrefixData                    = $inputPrefixData;
            $this->attribute                          = $attribute;
            $this->hasTrackableStructurePosition      = $hasTrackableStructurePosition;
            $this->showRemoveLink                     = $showRemoveLink;
        }

        public function render()
        {
            return $this->renderContent();
        }

        /**
         * Use this method to register dynamically created attributes during an ajax call.  An example is if you
         * add a filter, the inputs need to be added to the yiiactiveform so that validation handling can work
         * properly.  This method replaces the id and model elements with the correctly needed values.
         * Only adds inputs that have not been added already
         * @param ReportActiveForm $form
         */
        public function renderAddAttributeErrorSettingsScript(ReportActiveForm $form, $wizardFormClassName,
                                                              $componentFormClassName, $inputPrefixData)
        {
            assert('is_string($wizardFormClassName)');
            assert('is_string($componentFormClassName)');
            assert('is_array($inputPrefixData)');
            $attributes             = $form->getAttributes();
            $encodedErrorAttributes = CJSON::encode(array_values($attributes));
            $script = "
                var settings = $('#" . ReportWizardView::getFormId() . "').data('settings');
                $.each(" . $encodedErrorAttributes . ", function(i)
                {
                    var newId = this.id;
                    var alreadyInArray = false;
                    $.each(settings.attributes, function (i)
                    {
                        if(newId == this.id)
                        {
                            alreadyInArray = true;
                        }
                    });
                    if(alreadyInArray == false)
                    {
                        settings.attributes.push(this);
                    }
                });
                $('#" . ReportWizardView::getFormId() . "').data('settings', settings);
            ";
            Yii::app()->getClientScript()->registerScript('AddAttributeErrorSettingsScript', $script);
        }

        protected function renderContent()
        {
            $content  = '<div>';
            if($this->hasTrackableStructurePosition)
            {
                $content .= $this->renderReportAttributeRowNumberLabel();
                $content .= $this->renderHiddenStructurePositionInput();
            }
            $content .= $this->renderAttributeContent();
            $content .= '</div>';
            if($this->showRemoveLink)
            {
                $content .= ZurmoHtml::link('—', '#', array('class' => 'remove-report-attribute-row-link'));
            }
            $content  =  ZurmoHtml::tag('div', array('class' => 'report-attribute-row'), $content);
            if($this->addWrapper)
            {
                return ZurmoHtml::tag('li', array(), $content);
            }
            return $content;
        }

        protected function renderReportAttributeRowNumberLabel()
        {
            return ZurmoHtml::tag('span', array('class' => 'report-attribute-row-number-label'),
                                          ($this->rowNumber + 1) . '.');
        }

        protected function renderHiddenStructurePositionInput()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                                            array_merge($this->inputPrefixData, array('structurePosition')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                                            array_merge($this->inputPrefixData, array('structurePosition')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId, 'class' => 'structure-position');
            return ZurmoHtml::hiddenField($hiddenInputName, ($this->rowNumber + 1), $idInputHtmlOptions);
        }

        protected function renderAttributeContent()
        {
            $content = $this->elementAdapter->getContent();
            return $content;
        }
    }
?>