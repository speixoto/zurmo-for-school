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

    class BorderPositionElement extends Element
    {
        protected function renderControlEditable()
        {
            $this->registerScripts();
            $content                = $this->renderAllCheckbox();
            $content                .= "<br />";
            $content                .= $this->renderNoneCheckbox();
            return $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function renderAllCheckbox()
        {
            $attribute              = 'border-all';
            $label                  = Zurmo::t('Core', 'All');
            $all                    = $this->renderCheckboxWithHiddenInput($label, $attribute);
            $directionalCheckBoxes  = $this->renderDirectionalCheckboxes();
            $all                    = $all . $directionalCheckBoxes;
            return $all;
        }

        protected function renderNoneCheckbox()
        {
            $attribute              = 'border-none';
            $label                  = Zurmo::t('Core', 'None');
            $none                   = $this->renderCheckboxWithHiddenInput($label, $attribute);
            return $none;
        }

        protected function renderDirectionalCheckboxes()
        {
            $checkboxConfigurations = array(
                'border-top'    => Zurmo::t('Core', 'Top'),
                'border-right'  => Zurmo::t('Core', 'Right'),
                'border-bottom' => Zurmo::t('Core', 'Bottom'),
                'border-left'   => Zurmo::t('Core', 'Left'),
            );
            $content        = null;
            foreach ($checkboxConfigurations as $attribute => $label)
            {
                $content            .= $this->renderCheckboxWithHiddenInput($label, $attribute, true);
            }
            return $content;
        }

        protected function renderCheckboxWithHiddenInput($label, $attribute, $renderHidden = false)
        {
            $checkboxLabelFor           = $attribute;
            if ($renderHidden)
            {
                $attribute              = $this->resolveCheckboxInputAttributeName($attribute);
                $checkboxLabelFor       = ZurmoHtml::activeName($this->model, $attribute);
            }
            $checkboxLabelHtmlOptions   = array();
            $checkboxLabel              = ZurmoHtml::label($label, $checkboxLabelFor, $checkboxLabelHtmlOptions);
            $checkboxHtmlOptions        = $this->resolveCheckBoxHtmlOptions($renderHidden);
            $content                    = null;
            if ($renderHidden)
            {
                $content                .= $this->form->checkBox($this->model, $attribute, $checkboxHtmlOptions);
            }
            else
            {
                $content                .= ZurmoHtml::checkBox($attribute, false, $checkboxHtmlOptions);
            }
            $content                    .= $checkboxLabel;
            return $content;
        }

        protected function resolveCheckboxInputAttributeName($attribute)
        {
            return $this->params['checkboxInputPrefix'] . "[${attribute}]";
        }

        protected function resolveCheckBoxHtmlOptions($renderUncheckValue)
        {
            $options                    = array('value' => 1);
            $options['class']           = 'border-checkbox';
            if ($renderUncheckValue)
            {
                $options['class']           = 'directional-border-checkbox';
                $options['uncheckValue']    = 'none';
            }
            return $options;
        }

        protected function registerScripts()
        {
            $this->registerDirectionalCheckboxScript();
            $this->registerAllOrNoneCheckboxScript();
        }

        protected function registerDirectionalCheckboxScript()
        {
            $script = '
                        var directionalCheckBoxSelector = ".directional-border-checkbox:checkbox";
                        var noneCheckboxSelector        = ".border-checkbox#border-none:checkbox";
                        var allCheckboxSelector         = ".border-checkbox#border-all:checkbox";
                        function toggleCheckBoxState(checkboxSelector, state)
                        {
                            if ($(checkboxSelector).prop("checked") != state)
                            {
                                $(checkboxSelector).trigger("click");
                            }
                        }

                        function checkAllIfAllDirectionalCheckboxesChecked()
                        {
                            allCount        = $(directionalCheckBoxSelector).length;
                            checkedCount    = $(directionalCheckBoxSelector + ":checked").length;
                            if (allCount == checkedCount)
                            {
                                toggleCheckBoxState(allCheckboxSelector, true);
                                return true;
                            }
                        }

                        function checkNoneIfAllDirectionalCheckboxesUnchecked()
                        {
                            checkedCount    = $(directionalCheckBoxSelector + ":checked").length;
                            if (checkedCount == 0)
                            {
                                toggleCheckBoxState(noneCheckboxSelector, true);
                                return true;
                            }
                        }

                        $(directionalCheckBoxSelector).on("change", function()
                        {
                            if ($(this).is(":checked"))
                            {
                                toggleCheckBoxState(noneCheckboxSelector, false);
                                checkAllIfAllDirectionalCheckboxesChecked();
                            }
                            else
                            {
                                toggleCheckBoxState(allCheckboxSelector, false);
                                checkNoneIfAllDirectionalCheckboxesUnchecked();
                            }
                        });
                        checkNoneIfAllDirectionalCheckboxesUnchecked() || checkAllIfAllDirectionalCheckboxesChecked();
                        ';
            Yii::app()->clientScript->registerScript('directionalCheckboxClickScript', $script);
        }

        protected function registerAllOrNoneCheckboxScript()
        {
            $script = '
                        function changeAllDirectionalCheckboxesAndRaiseEvents(checked)
                        {
                            $(directionalCheckBoxSelector)
                                .each(function()
                                    {
                                        if ($(this).prop("checked") != checked)
                                        {
                                            $(this).trigger("click");
                                            $(this).trigger("change");
                                        }
                                    });
                        }
                        function checkAllDirectionalCheckboxesAndRaiseEvents()
                        {
                            changeAllDirectionalCheckboxesAndRaiseEvents(true);
                        }

                        function uncheckAllDirectionalCheckboxesAndRaiseEvents()
                        {
                            changeAllDirectionalCheckboxesAndRaiseEvents(false);
                        }

                        $(".border-checkbox:checkbox").on("change", function()
                        {
                            if ($(this).is(":checked"))
                            {
                                if ($(this).attr("id") == "border-all")
                                {
                                    checkAllDirectionalCheckboxesAndRaiseEvents();
                                }
                                else
                                {
                                     uncheckAllDirectionalCheckboxesAndRaiseEvents();
                                }
                            }
                            else
                            {
                            }
                        });';
            Yii::app()->clientScript->registerScript('allOrNoneChangeCheckboxScript', $script);
        }
    }
?>