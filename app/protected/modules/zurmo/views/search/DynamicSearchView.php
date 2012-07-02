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
     * Supports dyanmic advanced search.  This is where the user can decide the fields to filter on.
     */
    abstract class DynamicSearchView extends SearchView
    {
        const ADVANCED_SEARCH_TYPE_STATIC  = 'Static';

        const ADVANCED_SEARCH_TYPE_DYNAMIC = 'Dynamic';

            /**
         * Constructs a detail view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct($model, $listModelClassName, $gridIdSuffix = null, $hideAllSearchPanelsToStart = false)
        {
            assert('$model instanceof DynamicSearchForm');
            parent::__construct($model, $listModelClassName, $gridIdSuffix, $hideAllSearchPanelsToStart);
        }

        protected function renderAdvancedSearchForFormLayout($panel, $maxCellsPerRow, $form = null)
        {
            if(isset($panel['advancedSearchType']) &&
               $panel['advancedSearchType'] == self::ADVANCED_SEARCH_TYPE_DYNAMIC)
            {
                return $this->renderDynamicAdvancedSearchRows($panel, $maxCellsPerRow, $form);
            }
            else
            {
                return $this->renderStaticSearchRows($panel, $maxCellsPerRow, $form);
            }
        }

        protected function renderDynamicAdvancedSearchRows($panel, $maxCellsPerRow, $form = null)
        {
            assert('$form != null');
            $content = null;
            $content .= 'dynamic rows';
            $rowCount = 1;
            if(($panel['rows']) > 0)
            {
                foreach ($panel['rows'] as $row)
                {
                    $content .= '<div>';
                    foreach ($row['cells'] as $cell)
                    {
                        if (!empty($cell['elements']))
                        {
                            foreach ($cell['elements'] as $elementInformation)
                            {
                                $elementclassname          = $elementInformation['type'] . 'Element';
                                $element                   = new $elementclassname($this->model,
                                                                                   $elementInformation['attributeName'],
                                                                                   $form,
                                                                                   array_slice($elementInformation, 2));
                                $element->editableTemplate = '{content}{error}';
                                $content .= $element->render();
                            }
                        }
                    }
                    $content .= '</div>';
                    $rowCount ++;
                }
            }
            $content .= $this->renderAddExtraRowContent($rowCount);
            $content .= $this->renderDynamicSearchStructureContent();
           //we could have getMetadata be changed to resolveMetadata, non-static. that way saved
           //search we can pull in and show rows by default.
           ///we need a hidden array to post so if we have 1,3,5,6  which will show as 1,2,3,4 that hidden array should translate that.
             ///think about this abit.
           //we have to deal with saved search but this might require an override in DynamicSearchView...
           return $content;
        }

        protected function renderAddExtraRowContent($rowCount)
        {
            assert('is_int($rowCount)');
            $idInputHtmlOptions  = array('id' => 'rowCounter-' . $this->getSearchFormId());
            $hiddenInputName     = 'rowCounter';
            $ajaxOnChangeUrl     = Yii::app()->createUrl("zurmo/default/dynamicSearchAddExtraRow",
                                   array('viewClassName' => get_class($this),
                                         'modelClassName' => get_class($this->model->getModel()),
                                         'formModelClassName' => get_class($this->model),
                                         'suffix' => $this->getSearchFormId()));
            $content             = CHtml::hiddenField($hiddenInputName, $rowCount, $idInputHtmlOptions);
            // Begin Not Coding Standard
            $content            .= CHtml::ajaxButton(Yii::t('Default', 'Add Field'), $ajaxOnChangeUrl,
                                    array('type' => 'GET',
                                          'data' => 'js:\'rowNumber=\' + $(\'#rowCounter-' . $this->getSearchFormId(). '\').val()',
                                          'success' => 'js:function(data){
                                            $(\'#rowCounter-' . $this->getSearchFormId(). '\').val(parseInt($(\'#rowCounter-' . $this->getSearchFormId() . '\').val()) + 1)
                                            $(\'#addExtraAdvancedSearchRowButton-' . $this->getSearchFormId() . '\').parent().before(data);
                                            rebuildDynamicSearchRowNumbersAndStructureInput("' . $this->getSearchFormId() . '");
                                          }'),
                                    array('id' => 'addExtraAdvancedSearchRowButton-' . $this->getSearchFormId()));
            // End Not Coding Standard
            return CHtml::tag('div', array(), $content);
        }

        protected function renderDynamicSearchStructureContent()
        {
            if($this->shouldHideDynamicSearchStructureByDefault())
            {
                $style1 = '';
                $style2 = 'display:none;';
            }
            else
            {
                $style1 = 'display:none;';
                $style2 = '';
            }
            $content  = CHtml::link(Yii::t('Default', 'More Options'), '#',
                            array('id' => 'show-dynamic-search-structure-div-link-' . $this->getSearchFormId() . '',
                                          'style' => $style1));
            $content .= CHtml::link(Yii::t('Default', 'Less Options'), '#',
                            array('id' => 'hide-dynamic-search-structure-div-link-' . $this->getSearchFormId() . '',
                                          'style' => $style2));
            $content .= CHtml::tag('div',
                            array('id' => 'show-dynamic-search-structure-div-' . $this->getSearchFormId(),
                                          'style' => $style2),
                            $this->renderStructureInputContent());
            return $content;
        }

        protected function renderStructureInputContent()
        {
            $name                = get_class($this->model) . '[dynamic][structure]';
            $id                  = get_class($this->model) . '_dynamic_structure';
            $idInputHtmlOptions  = array('id' => $id, 'class' => 'dynamic-search-structure-input');
            return CHtml::textField($name, $this->getStructureValue(), $idInputHtmlOptions);
        }

        protected function shouldHideDynamicSearchStructureByDefault()
        {
            //todo: expand once we have saved search
            return true;
        }

        protected function getStructureValue()
        {
            //todo: expand once we have saved search
            return null;
        }

        protected function renderAfterFormLayout($form)
        {
           parent::renderAfterFormLayout($form);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets')) . '/dynamicSearchViewUtils.js');
            Yii::app()->clientScript->registerScript('showStructurePanels' . $this->getSearchFormId(), "
                $('#show-dynamic-search-structure-div-link-" . $this->getSearchFormId() . "').click( function()
                    {
                        $('#show-dynamic-search-structure-div-" . $this->getSearchFormId() . "').show();
                        $('#show-dynamic-search-structure-div-link-" . $this->getSearchFormId() . "').hide();
                        $('#hide-dynamic-search-structure-div-link-" . $this->getSearchFormId() . "').show();
                        return false;
                    }
                );
                $('#hide-dynamic-search-structure-div-link-" . $this->getSearchFormId() . "').click( function()
                    {
                        $('#show-dynamic-search-structure-div-" . $this->getSearchFormId() . "').hide();
                        $('#show-dynamic-search-structure-div-link-" . $this->getSearchFormId() . "').show();
                        $('#hide-dynamic-search-structure-div-link-" . $this->getSearchFormId() . "').hide();
                        return false;
                    }
                );");
        }
    }
?>