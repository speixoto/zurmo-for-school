<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Element for rendering sortable list of contact/lead web form attributes
     */
    class SortableContactWebFormAttributesElement extends Element
    {
        /**
         * @return string
         * @throws NotSupportedException
         */
        protected function renderControlNonEditable()
        {
            if (isset($this->model->serializedData))
            {
                $placedAttributes         = ContactWebFormsUtil::getPlacedAttributes($this->model);
                $content = '';
                foreach ($placedAttributes as $attribute)
                {
                    $content .= $attribute['attributeLabel'].'<br/>';
                }
                return $content;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @return string
         */
        protected function renderControlEditable()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("attributesList");
            $cClipWidget->widget('application.core.widgets.JuiSortable', array(
                'itemTemplate' => $this->renderItemTemplate(),
                'items'        => ContactWebFormsUtil::resolvePlacedAttributesForWebFormAttributesElement($this->model,
                                                                                                          $this->form),
            ));
            $cClipWidget->endClip();
            $clip       = $cClipWidget->getController()->clips['attributesList'];
            $title      = ZurmoHtml::tag('h4', array(), Zurmo::t('ContactWebFormsModule', 'Chosen Fields'));
            $content    = ZurmoHtml::tag('div', array('class' => 'left-column'), $title . $clip );
            $this->registerScript();

            $clip = $this->form->checkBoxList($this->model,
                                              $this->attribute,
                                              ContactWebFormsUtil::getNonPlacedAttributes($this->model),
                                              $this->getEditableHtmlOptions());
            $title       = ZurmoHtml::tag('h4', array(), Zurmo::t('ContactWebFormsModule', 'Available Fields'));
            $fieldsText  = 'Check the fields that you like to add to your form, you can then change their order or remove them';
            $description = ZurmoHtml::tag('span', array('class' => 'row-description'),
                           Zurmo::t('ContactWebFormsModule', $fieldsText));
            $content    .= ZurmoHtml::tag('div', array('class' => 'right-column'), $title . $description . $clip );
            return $content;
        }

        protected function registerScript()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.modules.contactWebForms.views.assets')) . '/ContactWebFormUtils.js');
        }

        /**
         * @return array
         */
        protected function getEditableHtmlOptions()
        {
            return array(
                'template'  => '<div class="multi-select-checkbox-input">{input}{label}<span class="z-spinner"></span></div>',
                'separator' => '');
        }

        /**
         * @return string
         */
        protected function renderItemTemplate()
        {
            $attributeData = array();
            $attributeData['{attributeName}']                   = '{id}';
            $attributeData['{isRequiredElement}']               = '{isRequiredElement}';
            $attributeData['{isHiddenElement}']                 = '{isHiddenElement}';
            $attributeData['{attributeLabelElement}']           = '{attributeLabelElement}';
            $attributeData['{hideHiddenAttributeElementStyle}'] = '{hideHiddenAttributeElementStyle}';
            $attributeData['{renderHiddenAttributeElement}']    = '{renderHiddenAttributeElement}';
            $attributeData['{removePlacedAttributeLink}']       = '{removePlacedAttributeLink}';
            $content                                            = ContactWebFormsUtil::getPlacedAttributeContent(
                                                                  $attributeData);
            return $content;
        }

        protected function renderError()
        {
        }

        /**
         * @return string
         */
        protected function renderLabel()
        {
            return ZurmoHtml::tag('h3', array(), Zurmo::t('ContactWebFormsModule', 'Form Layout'));
        }
    }
?>