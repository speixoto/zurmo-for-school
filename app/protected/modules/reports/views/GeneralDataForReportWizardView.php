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

    class GeneralDataForReportWizardView extends ComponentForReportWizardView
    {
        protected function renderFormContent()
        {
            $content           = '<div class="attributesContainer">';
            $element           = new TextElement($this->model, 'name', $this->form);
            $leftSideContent   = '<table><colgroup><col class="col-0"><col class="col-1">' .
                                 '</colgroup><tr>' . $element->render() . '</tr>';
            $element           = new TextAreaElement(
                                 $this->model, 'description', $this->form, array('rows' => 2));
            $leftSideContent  .= '<tr>' . $element->render() . '</tr>';
            $element           = new CurrencyConversionTypeStaticDropDownElement(
                                 $this->model, 'currencyConversionType', $this->form);
            $leftSideContent  .= '<tr>' . $element->render() . '</tr>';
            $element           = new CurrencyStaticDropDownFormElement($this->model, 'spotConversionCurrencyCode',
                                 $this->form, array('addBlank' => true));
            $leftSideContent  .= '<tr>' . $element->render() . '</tr></table>';
            $content          .= ZurmoHtml::tag('div', array('class' => 'panel'), $leftSideContent);
            $rightSideContent  = ZurmoHtml::tag('div', array(), $this->renderRightSideFormLayout());
            $rightSideContent  = ZurmoHtml::tag('div', array('class' => 'buffer'), $rightSideContent);
            $content          .= ZurmoHtml::tag('div', array('class' => 'right-side-edit-view-panel'), $rightSideContent);
            $content          .= '</div>';
            return $content;
        }

        protected function renderRightSideFormLayout()
        {
            $content  = '<h3>' . Zurmo::t('ReportsModule', 'Rights and Permissions') . '</h3><div id="owner-box">';
            $element  = new OwnerNameIdElement($this->model, 'null', $this->form);
            $element->editableTemplate = '{label}{content}{error}';
            $content .= $element->render().'</div>';
            $element  = new ExplicitReadWriteModelPermissionsElement($this->model,
                                             'explicitReadWriteModelPermissions', $this->form);
            $element->editableTemplate = '{label}{content}{error}';
            $content .= $element->render();
            return $content;
        }

        public static function getWizardStepTitle()
        {
            return Zurmo::t('ReportsModule', 'Save Report');
        }

        protected function renderNextPageLinkContent()
        {
            $params = array();
            $params['label']       = Zurmo::t('ReportsModule', 'Save and Run');
            $params['htmlOptions'] = array('id' => static::getNextPageLinkId(), 'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $searchElement = new SaveButtonActionElement(null, null, null, $params);
            return $searchElement->render();
        }

        public static function getPreviousPageLinkId()
        {
            return 'generalDataPreviousLink';
        }

        public static function getNextPageLinkId()
        {
            return 'generalDataSaveAndRunLink';
        }

        protected function registerScripts()
        {
            $currencyConversionTypeSelectId     = CurrencyConversionTypeStaticDropDownElement::
                                                  resolveInputIdPrefixIntoString(
                                                  array(get_class($this->model), 'currencyConversionType'));
            $spotConversionCurrencyCodeSelectId = CurrencyStaticDropDownFormElement::
                                                  resolveInputIdPrefixIntoString(
                                                  array(get_class($this->model), 'spotConversionCurrencyCode'));
            Yii::app()->clientScript->registerScript('currencyConversionTypeHelper', "
                if($('#" . $currencyConversionTypeSelectId . "').val() != " . Report::CURRENCY_CONVERSION_TYPE_SPOT . ")
                {
                    $('#" . $spotConversionCurrencyCodeSelectId . "').parentsUntil('tr').hide();
                }
                $('#" . $currencyConversionTypeSelectId . "').change( function()
                    {
                        if($(this).val() == " . Report::CURRENCY_CONVERSION_TYPE_SPOT . ")
                        {
                            $('#" . $spotConversionCurrencyCodeSelectId . "').parentsUntil('tr').show();
                        }
                        else
                        {
                            $('#" . $spotConversionCurrencyCodeSelectId . "').val('');
                            $('#" . $spotConversionCurrencyCodeSelectId . "').parentsUntil('tr').hide();
                        }
                    }
                );
            ");
        }
    }
?>