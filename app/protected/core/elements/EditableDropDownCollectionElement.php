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
     * Displays the editable dropdown interface
     * which is used by the Designer module
     * to allow values in a drop down list to be
     * modified, added, or removed.
     */
    class EditableDropDownCollectionElement extends Element
    {
        protected $collectionCountData;

        protected function renderEditable()
        {
            return $this->renderControlEditable();
        }

        protected function renderControlEditable()
        {
            assert('in_array("CollectionAttributeFormInterface", class_implements($this->model))');
            assert('$this->model->{$this->attribute} == null || is_array($this->model->{$this->attribute})');
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("EditableDropDownSortable");
            $cClipWidget->widget('application.core.widgets.JuiSortable', array(
                'itemTemplate' => $this->renderItemTemplate(),
                'items' => $this->getItems(),
                'options' => array(
                    'placeholder' => 'ui-state-highlight',
                    'stop'      => 'js:function(event, ui){' .
                                        $this->renderScriptCallToRebuildSelectInputFromInputs() . '}',
                ),
                'baseInputNameForSortableCollection' => $this->resolveInputNamePrefix() . '[' . $this->attribute . ']',
                'htmlOptions' =>
                array(
                    'id'    => $this->attribute . '_ul',
                    'class' => 'sortable',
                )
            ));
            $cClipWidget->endClip();
            $this->registerScripts();
            $content  = $this->renderError();
            $content .= $this->renderMultipleAttributesUsingCollectionContent();
            $content .= $this->renderRemovalNoticeContent();
            $content .= '<div id="sortable-editable-dropdown-collection">';
            $content .= $cClipWidget->getController()->clips['EditableDropDownSortable'];
            $content .= '</div>';
            $content .= $this->renderAddInputAndAddButton();

            return $content;
        }

        protected function getItems()
        {
            $items = array();
            $dropDownCountData = $this->getCollectionCountData();
            foreach ($this->getDropDownArray() as $order => $name)
            {
                if (isset($dropDownCountData[$name]))
                {
                    $removalContent = null;
                }
                else
                {
                    $removalContent = $this->renderRemoveLink();
                }
                $items[$order] = array('{content}'           => $name,
                                       '{removalContent}'    => $removalContent,
                                       '{dataLengthPlusOne}' => $order + 1);

                $activeLanguagesData   = $this->getActiveLanguagesData();
                $labelsAttributeName   = $this->getLabelsAttributeName();
                $labelsData            = $this->model->$labelsAttributeName;
                foreach ($activeLanguagesData as $language => $notUsed)
                {
                    if ($labelsAttributeName !== null &&
                       isset($labelsData[$language]) &&
                       isset($labelsData[$language][$order]))
                    {
                        $items[$order]['{' . $language . 'Label}'] = $labelsData[$language][$order];
                    }
                    else
                    {
                        $items[$order]['{' . $language . 'Label}'] = Zurmo::t('Core', $name, array(), null, $language);
                    }
                }
            }
            return $items;
        }

        protected function canAllItemsBeRemoved()
        {
            $dropDownCountData = $this->getCollectionCountData();
            foreach ($this->getDropDownArray() as $order => $name)
            {
                if (isset($dropDownCountData[$name]))
                {
                    return false;
                }
            }
            return true;
        }

        protected function getCollectionCountData()
        {
            if ($this->collectionCountData == null)
            {
                $this->collectionCountData = $this->model->getCollectionCountData();
            }
            return $this->collectionCountData;
        }

        protected function renderItemTemplate()
        {
            $activeLanguagesData   = $this->getActiveLanguagesData();
            $baseLanguage           = $this->getBaseLanguage();
            return '<li class="ui-state-default" id="editableDropDown_{id}">
                        <span class="ui-icon ui-icon-arrowthick-2-n-s">&#160;</span>
                        <div class="has-lang-label">
                        <input name = "' . $this->getNameForInputField() .
                        '" id = "' . $this->getIdForInputField('{id}') .
                        '" type = "text" value = "{content}" size="50"/>
                        <input name = "' . $this->getNameForExistingValueHiddenField() . '" type = "hidden" value = "{content}"/>
                        ' . static::renderLanguageLabelHtmlContent($activeLanguagesData[$baseLanguage]['name']) .
                        '</div>{removalContent}' .
                        $this->renderSortableLanguageLabelInputsForAddingNewValuesJuiSortableContent() .
                        '</li>';
        }

        protected function renderRemoveLink()
        {
            return ZurmoHtml::link( ZurmoHtml::tag('span', array(), Zurmo::t('Core', 'Remove')), '#',
                                    array('class' => 'remove-sortable-item-link'));
        }

        protected function renderAddInputAndAddButton()
        {
            $activeLanguagesData = $this->getActiveLanguagesData();
            $baseLanguage           = $this->getBaseLanguage();
            $content  = '<table id="picklist-values">';
            $content .= '<colgroup><col style="width:50%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr><td>';
            $content .= '<div class="has-lang-label">' . ZurmoHtml::textField( $this->attribute . '_AddInput', '', array('size' => 50));
            $content .= static::renderLanguageLabelHtmlContent($activeLanguagesData[$baseLanguage]['name']);
            $content .= '</div>';
            $content .= '<div id="' . $this->attribute . '_AddInput_em_" class="errorMessage" style="display:none"></div>';
            $content .= '</td></tr>';
            $content .= '<tr><td>';
            $content .= ZurmoHtml::link(ZurmoHtml::wrapLabel(Zurmo::t('Core', 'Add Item')), '#', array('id' => $this->attribute . '_AddInputButton'));
            $content .= '</td></tr>';
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }

        protected function registerScripts()
        {
            $supportedLanguagesData = $this->getActiveLanguagesData();
            $baseLanguage           = $this->getBaseLanguage();
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.elements.assets')
                    ) . '/SelectInputUtils.js',
                CClientScript::POS_END
            );
            $inputIdPrefix = $this->getInputIdPrefix();
            Yii::app()->clientScript->registerScript('editableDropDown', "
                " . $this->renderItemsOnChangeScript() . "
                $('.remove-sortable-item-link').live('click', function()
                    {
                        $(this).parent().remove();
                            " . $this->renderScriptCallToRebuildSelectInputFromInputs() . "
                        return false;
                    }
                );
                $('#" . $this->attribute . "_AddInputButton').click(function()
                    {
                        if ($('#" . $this->attribute . "_AddInput').val()=='')
                        {
                            $('#" . $this->attribute . "_AddInput').addClass($.fn.yiiactiveform.getSettings('#edit-form').errorCssClass);
                            $('#" . $this->attribute . "_AddInput_em_').html('" . Zurmo::t('Core', 'You must enter a value') . "').show();
                            return false;
                        }
                        else
                        {
                            $('#" . $this->attribute . "_AddInput').removeClass($.fn.yiiactiveform.getSettings('#edit-form').errorCssClass);
                            $('#" . $this->attribute . "_AddInput_em_').html('').hide();
                        }
                        var currenInputCollectionLength = $('input[name=\"" . $this->getNameForInputField() . "\"]').length;
                        var newLi = $('<li class=\"ui-state-default\" id=\"{id}\">' +
                        '" . $this->renderRemoveLink() . "' +
                        '<span class=\"ui-icon ui-icon-arrowthick-2-n-s\">&#160;</span>' +
                        '<div class=\"has-lang-label\">' +
                        '<input name=\"" . $this->getNameForInputField() . "\" id=\"" . $inputIdPrefix .
                        "' + ($('input[name=\"" . $this->getNameForInputField() . "\"]').length + 1) +'\" type=\"text\" value=\"' +
                        $('#" . $this->attribute . "_AddInput').val()
                         + '\" size=\"50\"/>" . static::renderLanguageLabelHtmlContent($supportedLanguagesData[$baseLanguage]['name']) . "</div>' +
                        '<input name=\"" . $this->getNameForExistingValueHiddenField() . "\" type=\"hidden\" value=\"' +
                        $('#" . $this->attribute . "_AddInput').val() + '\" />" . "' +
                        '" . $this->renderSortableLanguageLabelInputsForAddingNewValuesJavaScriptContent(). "' +
                        '</li>');
                        newLi.appendTo($('#" . $this->attribute . "_ul'));
                        $('#" . $this->attribute . "_AddInput').val('');
                        $('#" . $inputIdPrefix . "' + (currenInputCollectionLength + 1)).change(function()
                        {
                            " . $this->renderScriptCallToRebuildSelectInputFromInputs() . "
                        }
                        );
                            " . $this->renderScriptCallToRebuildSelectInputFromInputs() . "
                        $('.has-lang-label', newLi).each(
                            function()
                            {
                                $(this).resolvePaddingForHasLangLabel( $(this) );
                            }
                        );
                        return false;
                    }
                );
            ");
        }

        protected function renderSortableLanguageLabelInputsForAddingNewValuesJavaScriptContent()
        {
            $activeLanguagesData = $this->getActiveLanguagesData();
            $baseLanguage           = $this->getBaseLanguage();
            $content                = null;
            foreach ($activeLanguagesData as $language => $languageData)
            {
                if ($language != $baseLanguage)
                {
                    $content .= "<div class=\"has-lang-label\"><input name=\"" . $this->getNameForLabelInputField($language) . "\" id=\"" . $this->getLabelInputIdPrefix($language) .
                                "' + ($('input[name=\"" . $this->getNameForLabelInputField($language) . "\"]').length + 1) +'\"" .
                                " type=\"text\" value=\"' + $('#" . $this->attribute . "_AddInput').val() + '\" size=\"50\"/>";
                    $content .= static::renderLanguageLabelHtmlContent($languageData['name']) . "</div>";
                }
            }
            return $content;
        }

        protected function renderSortableLanguageLabelInputsForAddingNewValuesJuiSortableContent()
        {
            $activeLanguagesData = $this->getActiveLanguagesData();
            $baseLanguage           = $this->getBaseLanguage();
            $content                = null;
            foreach ($activeLanguagesData as $language => $languageData)
            {
                if ($language != $baseLanguage)
                {
                    $content .= "<div class=\"has-lang-label\"><input name=\"" . $this->getNameForLabelInputField($language) . "\" id=\"" . $this->getLabelInputIdPrefix($language) .
                                "{dataLengthPlusOne}\"" .
                                " type=\"text\" value=\"{" . $language . "Label}\" size=\"50\"/>";
                    $content .= static::renderLanguageLabelHtmlContent($languageData['name']) . "</div>";
                }
            }
            return $content;
        }

        protected function renderScriptCallToRebuildSelectInputFromInputs()
        {
            assert('$this->getSpecificValueFromDropDownAttributeName() != null');
            return "rebuildSelectInputFromInputs(
                        '" . $this->resolveInputIdPrefix() . "_" .
                        $this->getSpecificValueFromDropDownAttributeName() . "', '" .
                        $this->getNameForInputField() . "');";
        }

        protected function renderItemsOnChangeScript()
        {
            $content = null;
            foreach ($this->getDropDownArray() as $key => $item)
            {
                $content .= "$('#" . $this->getIdForInputField($key) . "').change(function()
                {
                    " . $this->renderScriptCallToRebuildSelectInputFromInputs() . "
                }
                );";
            }
            return $content;
        }

        protected function renderControlNonEditable()
        {
            return Yii::app()->format->text($this->model->{$this->attribute});
        }

        protected function getSpecificValueFromDropDownAttributeName()
        {
            if (isset($this->params['specificValueFromDropDownAttributeName']))
            {
                return $this->params['specificValueFromDropDownAttributeName'];
            }
            return null;
        }

        /**
         * The base language must be specified.
         */
        protected function getBaseLanguage()
        {
            if (isset($this->params['baseLanguage']))
            {
                return $this->params['baseLanguage'];
            }
            throw new NotSupportedException();
        }

        /**
         * There must be at least one active language.
         */
        protected function getActiveLanguagesData()
        {
            if (isset($this->params['activeLanguagesData']))
            {
                if (count($this->params['activeLanguagesData']) == 0)
                {
                    throw new NotSupportedException();
                }
                return $this->params['activeLanguagesData'];
            }
            throw new NotSupportedException();
        }

        /**
         * The labels attribute name must be specified
         */
        protected function getLabelsAttributeName()
        {
            if (isset($this->params['labelsAttributeName']))
            {
                return $this->params['labelsAttributeName'];
            }
            throw new NotSupportedException();
        }

        protected function getIdForInputField($suffix)
        {
            return $this->resolveInputIdPrefix() . '_' . $this->attribute . '_'. $suffix;
        }

        protected function getNameForInputField()
        {
            return $this->resolveInputNamePrefix() . '[' . $this->attribute . '][]';
        }

        protected function getNameForLabelInputField($language)
        {
            assert('is_string($language)');
            return $this->resolveInputNamePrefix() . '[' . $this->getLabelsAttributeName() . '][' . $language . '][]';
        }

        protected function getNameForExistingValueHiddenField()
        {
            return $this->resolveInputNamePrefix() . '[' . $this->attribute . 'ExistingValues][]';
        }

        protected function getInputIdPrefix()
        {
            return $this->resolveInputIdPrefix() . '_' . $this->attribute . '_';
        }

        protected function getLabelInputIdPrefix($language)
        {
            assert('is_string($language)');
            return $this->resolveInputIdPrefix() . '_' . $this->getLabelsAttributeName() . '_' . $language . '_';
        }

        protected function getDropDownArray()
        {
            $dropDownArray = $this->model->{$this->attribute};
            if ($dropDownArray == null)
            {
                return array();
            }
            return $dropDownArray;
        }

        protected function renderMultipleAttributesUsingCollectionContent()
        {
            $content                       = '';
            $modelLabelAttributeLabelsData =  $this->model->getModelPluralNameAndAttributeLabelsThatUseCollectionData();
            if (count($modelLabelAttributeLabelsData) > 1)
            {
                $message = Zurmo::t('Core', 'This pick-list is used by more than one module.');
                foreach ($modelLabelAttributeLabelsData as $modelLabel => $attributeLabel)
                {
                    $message .= '<br/>' . $modelLabel . '&#160;-&#160;' . $attributeLabel;
                }
                $content .= HtmlNotifyUtil::renderHighlightBoxByMessage($message);
            }
            return $content;
        }

        protected function renderRemovalNoticeContent()
        {
            if ($this->canAllItemsBeRemoved())
            {
                return;
            }
            $message = Zurmo::t('Core', 'Some values cannot be removed because they are currently in use. Try changing the records that use them first.');
            $content  = HtmlNotifyUtil::renderHighlightBoxByMessage($message);
            return $content;
        }

        protected static function renderLanguageLabelHtmlContent($label)
        {
            return ZurmoHtml::tag('span', array(), $label);
        }
    }
?>
