<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Display a collection of address fields
     * Collection includes street1, street2,
     * city, state, postal code, and country.
     */
    class AddressElement extends Element
    {
        public $breakLines = true;

        /**
         * Renders the noneditable address content.
         * Takes the model attribute value and converts it into
         * at most 6 items which form the collection.
         * @return A string containing the element's content.
         */
        protected function renderControlNonEditable()
        {
            assert('$this->model->{$this->attribute} instanceof Address');
            $addressModel = $this->model->{$this->attribute};
            $id           = $addressModel->id;
            $street1      = $addressModel->street1;
            $street2      = $addressModel->street2;
            $city         = $addressModel->city;
            $state        = $addressModel->state;
            $postalCode   = $addressModel->postalCode;
            $country      = $addressModel->country;
            $latitude     = $addressModel->latitude;
            $longitude    = $addressModel->longitude;
            $invalid      = $addressModel->invalid;
            $content = null;
            if (!empty($street1))
            {
                $content  .= Yii::app()->format->text($street1);
                $content  .= $this->resolveHtmlAndTextBreakLine();
            }
            if (!empty($street2))
            {
                $content .= Yii::app()->format->text($street2);
                $content  .= $this->resolveHtmlAndTextBreakLine();
            }
            if (!empty($city))
            {
                $content .= Yii::app()->format->text($city) . ' ';
            }
            if (!empty($state))
            {
                $content .= Yii::app()->format->text($state);
            }
            if (!empty($state) && !empty($postalCode))
            {
                $content .= ',&#160;';
            }
            if (!empty($postalCode))
            {
                $content .= Yii::app()->format->text($postalCode);
            }
            if (!empty($country))
            {
                $content .= $this->resolveHtmlAndTextBreakLine() . Yii::app()->format->text($country);
            }
            if (!$invalid && $addressModel->makeAddress() != '')
            {
                $content = $this->renderMapLink($addressModel, $content);
            }
            return $content;
        }

        /**
         * Renders the editable address content.
         * Takes the model attribute value and converts it into
         * at most 6 items.
         * @return A string containing the element's content
         */
        protected function renderControlEditable()
        {
            assert('$this->model->{$this->attribute} instanceof Address');
            $addressModel = $this->model->{$this->attribute};
            $content  = $this->renderEditableAddressTextField($addressModel, $this->form, $this->attribute, 'street1') .
                        $this->resolveTextBreakLine();
            $content .= $this->renderEditableAddressTextField($addressModel, $this->form, $this->attribute, 'street2') .
                        $this->resolveTextBreakLine();
            $content .= $this->renderEditableAddressTextField($addressModel, $this->form, $this->attribute, 'city') .
                        $this->resolveTextBreakLine();
            $content .= '<div class="hasHalfs">';
            $content .= $this->renderEditableAddressTextField($addressModel, $this->form, $this->attribute, 'state', true) .
                        $this->resolveTextBreakLine();;
            $content .= $this->renderEditableAddressTextField($addressModel, $this->form, $this->attribute, 'postalCode', true) .
                        $this->resolveTextBreakLine();
            $content .= '</div>';
            $content .= $this->renderEditableAddressTextField($addressModel, $this->form, $this->attribute, 'country') .
                        $this->resolveTextBreakLine();
            return '<div class="address-fields">' . $content . '</div>';
        }

        protected function renderEditableAddressTextField($model, $form, $inputNameIdPrefix, $attribute,
                                                          $renderAsHalfSize = false)
        {
            $id          = $this->getEditableInputId($inputNameIdPrefix, $attribute);
            $htmlOptions = array(
                'name'   => $this->getEditableInputName($inputNameIdPrefix, $attribute),
                'id'     => $id,
                'encode' => false,
            );
            $label       = $form->labelEx  ($model, $attribute, array('for'   => $id));
            $textField   = $form->textField($model, $attribute, $htmlOptions);
            $error       = $form->error    ($model, $attribute, array('inputID' => $id), true, true,
                                            $this->renderScopedErrorId($inputNameIdPrefix, $attribute));
            if ($model->$attribute != null)
            {
                 $label = null;
            }
            $halfClassString = null;
            if ($renderAsHalfSize)
            {
                $halfClassString = ' half';
            }
            return ZurmoHtml::tag('div', array('class' => 'overlay-label-field' . $halfClassString), $label . $textField . $error);
        }

         /**
         * Render a map link. This link calls a modal
         * popup.
         * @return The element's content as a string.
         */
        protected function renderMapLink($addressModel, $addressLine)
        {
            assert('$addressModel instanceof Address');
            Yii::app()->getClientScript()->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.elements.assets')
                    ) . '/Modal.js',
                CClientScript::POS_END
            );
            $mapRenderUrl = Yii::app()->mappingHelper->resolveMappingLinkUrl(array(
                                                                         'addressString' => $addressModel->makeAddress(),
                                                                         'latitude'      => $addressModel->latitude,
                                                                         'longitude'     => $addressModel->longitude));
            $id           = $this->getEditableInputId($this->attribute, 'MapLink');
            $content      = ZurmoHtml::ajaxLink($addressLine, $mapRenderUrl,
                                $this->resolveAjaxOptionsForMapLink(),
                                array('id' => $id, 'class' => 'icon-map')
            );
            return $content;
        }

        protected function resolveAjaxOptionsForMapLink()
        {
            return ModalView::getAjaxOptionsForModalLink(strval($this->model));
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            return $this->resolveNonActiveFormFormattedLabel($this->getFormattedAttributeLabel());
        }

        protected function resolveHtmlAndTextBreakLine()
        {
            if ($this->breakLines)
            {
                return "<br/>\n";
            }
            return ' ';
        }

        protected function resolveTextBreakLine()
        {
            if ($this->breakLines)
            {
                return "\n";
            }
            return ' ';
        }
    }
?>