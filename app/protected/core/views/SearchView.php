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
     * The base View for a module's search view.
     */
    abstract class SearchView extends ModelView
    {
        protected $gridIdSuffix;

        protected $hideAllSearchPanelsToStart;

        protected $showAdvancedSearch = true;

        /**
         * Constructs a detail view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct($model,
            $listModelClassName,
            $gridIdSuffix = null,
            $hideAllSearchPanelsToStart = false
            )
        {
            assert('$model != null');
            assert('is_string($listModelClassName)');
            assert('is_bool($hideAllSearchPanelsToStart)');
            $this->model                      = $model;
            $this->listModelClassName         = $listModelClassName;
            $this->gridIdSuffix               = $gridIdSuffix;
            $this->gridId                     = 'list-view';
            $this->hideAllSearchPanelsToStart = $hideAllSearchPanelsToStart;
        }

        /**
         * Renders content for a view including search form including
         * two panels, the second of which is hidden on default, and
         * bottom panel with a search buttom and 'advanced search' link
         * and form layout.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $content = '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'NoRequiredsActiveForm',
                                                                array('id'                   => $this->getSearchFormId(),
                                                                      'action'               => $this->getFormActionUrl(),
                                                                      'enableAjaxValidation' => $this->getEnableAjaxValidationValue(),
                                                                      'clientOptions'        => $this->getClientOptions(),

                                                                )
                                                            );
            $content .= $formStart;
            $content .= $this->renderFormLayout($form);
            $content .= $this->renderAfterFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= $this->renderModalContainer();
            $content .= '</div>';
            return $content;
        }

        protected function getEnableAjaxValidationValue()
        {
            return false;
        }

        protected function getClientOptions()
        {
            return array();
        }

        protected function getFormActionUrl()
        {
            return null;
        }

        protected function renderAfterFormLayout($form)
        {
            $this->registerScripts();
        }

        /**
         * Renders the bottom panel of the layout. Includes the search button
         * and the advanced search link that opens/closes the second panel. Using click.clear namespace to
         * avoid collision with the binding from clearform.
         * @return A string containing the element's content.
         */
        protected function renderFormBottomPanel()
        {
            $moreSearchOptionsLink        = $this->resolveMoreSearchOptionsLinkContent();
            $selectListAttributesLink     = $this->getSelectListAttributesLinkContent();
            $kanbanBoardOptionsLink       = $this->getKanbanBoardOptionsLinkContent();
            $clearSearchLabelPrefix       = $this->getClearSearchLabelPrefixContent();
            $clearSearchLabel             = $this->getClearSearchLabelContent();
            $clearSearchLinkStartingStyle = $this->getClearSearchLinkStartingStyle();
            $clearSearchLink              = ZurmoHtml::link($clearSearchLabelPrefix . $clearSearchLabel, '#',
                                                        array('id'    => 'clear-search-link' . $this->gridIdSuffix,
                                                              'style' => $clearSearchLinkStartingStyle));
            $startingDivStyle = null;
            if ($this->hideAllSearchPanelsToStart)
            {
                $startingDivStyle = "style='display:none;'";
            }
            $content  = '<div class="search-form-tools">';
            $content .= $moreSearchOptionsLink;
            $content .= $selectListAttributesLink;
            $content .= $kanbanBoardOptionsLink;
            $content .= $clearSearchLink;
            $content .= $this->renderFormBottomPanelExtraLinks();
            $content .= $this->renderClearingSearchInputContent();
            $content .= '</div>';
            return $content;
        }

        protected function resolveMoreSearchOptionsLinkContent()
        {
            if ($this->showAdvancedSearch)
            {
                return ZurmoHtml::link(Zurmo::t('Core', 'Advanced'), '#', array('id' => 'more-search-link' . $this->gridIdSuffix));
            }
        }

        protected function getClearSearchLabelPrefixContent()
        {
        }

        protected function getClearSearchLabelContent()
        {
            return Zurmo::t('Core', 'Clear');
        }

        protected function getClearSearchLinkStartingStyle()
        {
            if ($this->model->anyMixedAttributes == null)
            {
                return "display:none;";
            }
        }

        protected function getExtraRenderForClearSearchLinkScript()
        {
        }

        protected function renderClearingSearchInputContent()
        {
            $idInputHtmlOptions  = array('id' => $this->getClearingSearchInputId());
            $hiddenInputName     = 'clearingSearch';
            return ZurmoHtml::hiddenField($hiddenInputName, null, $idInputHtmlOptions);
        }

        protected function getClearingSearchInputId()
        {
            return 'clearingSearch-' . $this->getSearchFormId();
        }

        protected function registerScripts()
        {
            DropDownUtil::registerScripts();
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('search' . $this->getSearchFormId(), "
                $('#clear-search-link" . $this->gridIdSuffix . "').removeAttr('clearForm');
                $('#clear-search-link" . $this->gridIdSuffix . "').clearform({
                        form: '#" . $this->getSearchFormId() . "'
                    }
                );
                $('#clear-search-link" . $this->gridIdSuffix . "').unbind('click.clear');
                $('#clear-search-link" . $this->gridIdSuffix . "').bind('click.clear', function(){
                        $('#" . $this->getClearingSearchInputId() . "').val('1');
                        $('#clear-search-link" . $this->gridIdSuffix . "').hide();
                        " . $this->getExtraRenderForClearSearchLinkScript() . "
                        $(this).closest('form').submit();
                        $('#" . $this->getClearingSearchInputId() . "').val('');
                        return false;
                    }
                );
                $('#more-search-link" . $this->gridIdSuffix . "').unbind('click.more');
                $('#more-search-link" . $this->gridIdSuffix . "').bind('click.more',  function(event){
                        $('.select-list-attributes-view').hide();
                        $('.kanban-board-options-view').hide();
                        $(this).closest('form').find('.search-view-1').toggle();
                        return false;
                    }
                );
                $('#cancel-advanced-search').unbind('click');
                $('#cancel-advanced-search').live('click', function(event){
                    $(this).closest('form').find('.search-view-1').hide();
                });
            " . $this->getExtraRenderFormBottomPanelScriptPart());
            $this->renderAdvancedSearchScripts();
            // End Not Coding Standard
        }

        protected function renderAdvancedSearchScripts()
        {
            Yii::app()->clientScript->registerScript('advancedSearch' . $this->getSearchFormId(), "
                $('#" . $this->getSearchFormId() . "').unbind('submit');
                $('#" . $this->getSearchFormId() . "').bind('submit', function(event)
                    {
                        $(this).closest('form').find('.search-view-1').hide();
                        " . $this->getHideOrShowClearSearchLinkScript() . "
                        $('.select-list-attributes-view').hide();
                        $('.kanban-board-options-view').hide();
                        $('#" . $this->gridId . $this->gridIdSuffix . "-selectedIds').val(null);
                        $.fn.yiiGridView.update('" . $this->gridId . $this->gridIdSuffix . "',
                        {
                            data: $(this).serialize() + '&" . $this->listModelClassName . "_page=&" . // Not Coding Standard
                            $this->listModelClassName . "_sort=" .
                            $this->getExtraQueryPartForSearchFormScriptSubmitFunction() ."' // Not Coding Standard
                         }
                        );
                        return false;
                    }
                );");
        }

        /**
         * Override as needed.
         * @return string the script to show/hide the clear link if depending on if
         * there is any search condition
         */
        protected function getHideOrShowClearSearchLinkScript()
        {
            $script = "
                var empty = $('#" . $this->getSearchFormId(). "').find('.anyMixedAttributes-input').val() == '';
                $(this).closest('form').find('.search-view-1').find(':input').each(function()
                {
                    if ($(this).val() != '')
                    {
                        empty = false;
                    }
                });
                if (!empty)
                {
                    $('#clear-search-link" . $this->gridIdSuffix . "').show();
                }
                else
                {
                    $('#clear-search-link" . $this->gridIdSuffix . "').hide();
                }
            ";
            return $script;
        }

        /**
         * Override as needed.
         */
        protected function renderFormBottomPanelExtraLinks()
        {
            return null;
        }

        /**
         * Override as needed.
         */
        protected function getExtraQueryPartForSearchFormScriptSubmitFunction()
        {
            return null;
        }

        /**
         * Override as needed.
         */
        protected function getExtraRenderFormBottomPanelScriptPart()
        {
            $script = "
                $('#" . $this->getSearchFormId(). "').find('.anyMixedAttributes-input').unbind('input.clear propertychange.clear keyup.clear');
                $('#" . $this->getSearchFormId(). "').find('.anyMixedAttributes-input').bind('input.clear propertychange.clear keyup.clear', function(event)
                {
                    $('#clear-search-link" . $this->gridIdSuffix . "').show();
                });
            ";
            return $script;
        }

        /**
         * Render a search form that has two panels. The
         * second panel is hidden by default in the user interface.
         * @return A string containing the element's content.
         */
        protected function renderFormLayout(ZurmoActiveForm $form)
        {
            $metadata        = self::getMetadata();
            $maxCellsPerRow  = $this->getMaxCellsPerRow();
            $content         = "";
            $content        .= $this->renderSummaryCloneContent();
            assert('count($metadata["global"]["panels"]) == 2 || count($metadata["global"]["panels"]) == 1');
            foreach ($metadata['global']['panels'] as $key => $panel)
            {
                $startingDivStyle = "";
                if ($key == 1 || $this->hideAllSearchPanelsToStart)
                {
                    $startingDivStyle = "style='display:none;'";
                }
                $content .= '<div class="search-view-' . $key . '" ' . $startingDivStyle . '>';
                if ($key == 1)
                {
                   $content .= $this->renderAdvancedSearchForFormLayout($panel, $maxCellsPerRow, $form);
                }
                else
                {
                    $content .= $this->renderStaticSearchRows($panel, $maxCellsPerRow, $form);
                    $content .= $this->renderStarredFilterHidenField($form);
                }
                if ($key == 1)
                {
                    $content .= $this->renderViewToolBarContainerForAdvancedSearch($form);
                }
                $content .= '</div>';
            }
            $content .= $this->renderListAttributesSelectionContent($form);
            $content .= $this->renderKanbanBoardOptionsContent($form);
            $content .= $this->renderFormBottomPanel();

            return $content;
        }

        protected function renderSummaryCloneContent()
        {
            return ZurmoHtml::tag('div',
                                  array(
                                      'id'    => $this->getListViewId() . '-summary-clone',
                                      'class' => ExtendedGridView::CLONE_SUMMARY_CLASS,
                                  ),
                                  '');
        }

        protected function getSelectListAttributesLinkContent()
        {
            if ($this->model->getListAttributesSelector() != null)
            {
                return ZurmoHtml::link(Zurmo::t('Core', 'Columns'), '#', array('id' => 'select-list-attributes-link' . $this->gridIdSuffix));
            }
        }

        protected function renderListAttributesSelectionContent(ZurmoActiveForm $form)
        {
            if ($this->model->getListAttributesSelector() == null)
            {
                return;
            }
            Yii::app()->clientScript->registerScript('listAttributes' . $this->getSearchFormId(), "
                $('#select-list-attributes-link" . $this->gridIdSuffix . "').unbind('click.more');
                $('#select-list-attributes-link" . $this->gridIdSuffix . "').bind('click.more',  function(event)
                    {
                        $(this).closest('form').find('.search-view-1').hide();
                        $(this).closest('form').find('.kanban-board-options-view').hide();
                        $('.select-list-attributes-view').toggle();
                        return false;
                    }
                );
                $('#list-attributes-reset').unbind('click.close');
                $('#list-attributes-reset').bind('click.close', function()
                    {
                        $('.select-list-attributes-view').hide();
                    }
                );
                $('#list-attributes-apply').unbind('click.close');
                $('#list-attributes-apply').bind('click.close', function()
                    {
                        $('.select-list-attributes-view').hide();
                    }
                );
                ");
            $element = new ListAttributesSelectionElement($this->model, null, $form, array());
            $element->editableTemplate = '{content}';
            $content = $element->render();
            return ZurmoHtml::tag('div', array('class' => 'select-list-attributes-view',
                                            'style'    => 'display:none'), $content);
        }

        /**
         * @return string
         */
        protected function getKanbanBoardOptionsLinkContent()
        {
            if ($this->model->getKanbanBoard() != null && $this->model->getKanbanBoard()->getIsActive())
            {
                return ZurmoHtml::link(Zurmo::t('Core', 'Options'), '#', array('id' => 'kanban-board-options-link' . $this->gridIdSuffix));
            }
        }

        /**
         * @param ZurmoActiveForm $form
         * @return string
         */
        protected function renderKanbanBoardOptionsContent(ZurmoActiveForm $form)
        {
            if ($this->model->getKanbanBoard() == null || !$this->model->getKanbanBoard()->getIsActive())
            {
                return;
            }
            Yii::app()->clientScript->registerScript('kanbanBoardOptions' . $this->getSearchFormId(), "
                $('#kanban-board-options-link" . $this->gridIdSuffix . "').unbind('click.more');
                $('#kanban-board-options-link" . $this->gridIdSuffix . "').bind('click.more',  function(event)
                    {
                        $(this).closest('form').find('.search-view-1').hide();
                        $('.select-list-attributes-view').hide();
                        $('.kanban-board-options-view').toggle();
                        return false;
                    }
                );
                $('#kanban-board-options-reset').unbind('click.close');
                $('#kanban-board-options-reset').bind('click.close', function()
                    {
                        $('.kanban-board-options-view').hide();
                    }
                );
                $('#kanban-board-options-apply').unbind('click.close');
                $('#kanban-board-options-apply').bind('click.close', function()
                    {
                        $('.kanban-board-options-view').hide();
                    }
                );
                ");
            $element = new KanbanBoardOptionsElement($this->model, null, $form, array());
            $element->editableTemplate = '{content}';
            $content = $element->render();
            return ZurmoHtml::tag('div', array('class' => 'kanban-board-options-view', 'style' => 'display:none'), $content);
        }

        protected function renderViewToolBarContainerForAdvancedSearch($form)
        {
            $content  = '<div class="view-toolbar-container clearfix">';
            $content .= '<div class="form-toolbar">';
            $content .= $this->renderViewToolBarLinksForAdvancedSearch($form);
            $content .= '</div></div>';
            return $content;
        }

        protected function renderViewToolBarLinksForAdvancedSearch($form)
        {
            $params                = array();
            $params['label']       = Zurmo::t('Core', 'Search');
            $params['htmlOptions'] = array('id' => 'search-advanced-search', 'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $searchElement         = new SaveButtonActionElement(null, null, null, $params);
            $content               = $searchElement->render();
            $closeButton           = ZurmoHtml::link(ZurmoHtml::wrapLabel(Zurmo::t('Core', 'Close')),
                                     '#', array('id' => 'cancel-advanced-search', 'class' => 'z-button'));
            return $closeButton . $content;
        }

        protected function renderSaveInputAndSaveButtonContentForAdvancedSearch($form)
        {
        }

        protected function renderAdvancedSearchForFormLayout($panel, $maxCellsPerRow, $form)
        {
            return $this->renderStaticSearchRows($panel, $maxCellsPerRow, $form);
        }

        protected function renderStaticSearchRows($panel, $maxCellsPerRow, $form = null)
        {
            $content = null;
            foreach ($panel['rows'] as $row)
            {
                $innerContent = null;
                foreach ($row['cells'] as $cell)
                {
                    if (!empty($cell['elements']))
                    {
                        foreach ($cell['elements'] as $elementInformation)
                        {
                            if (count($row['cells']) == 1 && count($row['cells']) < $maxCellsPerRow)
                            {
                                $elementInformation['wide'] = true;
                            }
                            $elementclassname = $elementInformation['type'] . 'Element';
                            $element = new $elementclassname($this->model, $elementInformation['attributeName'], $form, array_slice($elementInformation, 2));
                            $innerContent .= $element->render();
                        }
                    }
                    $content .= ZurmoHtml::tag('tr', array(), $innerContent);
                }
            }
            return ZurmoHtml::tag('table', array(), $content);
        }

        /**
         * Returns meta data for use in automatically generating the view.
         * The meta data is comprised of two panels, n rows, and then n cells. Each
         * cell can have 1 or more elements.
         *
         * For search view, there should only be two panels.
         * The second panel is hidden by default in the user interface and is where the 'advanced search'
         * inputs are placed.
         *
         * The element takes 3 parameters.
         * The first parameter is 'attributeName' The
         * second parameter is 'type' and refers to the element type. Using a
         * type of 'Text' would utilize the TextElement class. The third parameter
         * is 'wide' and refers to how many cells the field should span. An example
         * of the 'wide' => true usage would be for a text description field.
         * Here is an example meta data that
         * defines a search layout with two panels. Each panel has 1 row with 2 cells each
         *
         * @code
            <?php
                $metadata = array(
                    'global' => array(
                        'panels' => array(
                            array(
                                'title' => 'Basic Search',
                                'rows' => array(
                                    array('cells' =>
                                        array(
                                            array(
                                                'elements' => array(
                                                    array('attributeName' => 'name', 'type' => 'Text'),
                                                ),
                                            ),
                                            array(
                                                'elements' => array(
                                                    array('attributeName' => 'officePhone', 'type' => 'Text'),
                                                ),
                                            ),
                                        )
                                    ),
                                ),
                            ),
                            array(
                                'title' => 'Advanced Search',
                                'rows' => array(
                                    array('cells' =>
                                        array(
                                            array(
                                                'elements' => array(
                                                    array('attributeName' => 'industry', 'type' => 'DropDown'),
                                                ),
                                            ),
                                            array(
                                                'elements' => array(
                                                    array('attributeName' => 'officeFax', 'type' => 'Text'),
                                                ),
                                            ),
                                        )
                                    ),
                                ),
                            ),
                        ),
                    ),
                );
            ?>
         * @endcode
         *
         */
        public static function getDefaultMetadata()
        {
            return array();
        }

        protected function getColumnCount($metadata)
        {
            $columnCount = 1;
            foreach ($metadata['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    $tempCount = 0;
                    foreach ($row['cells'] as $cell)
                    {
                        $tempCount++;
                    }
                    if ($tempCount > $columnCount)
                    {
                        $columnCount = $tempCount;
                    }
                }
            }
            return $columnCount;
        }

        public static function getDesignerRulesType()
        {
            return 'SearchView';
        }

        protected function getSearchFormId()
        {
            return 'search-form' . $this->gridIdSuffix;
        }

        protected function getListViewId()
        {
            return $this->gridId . $this->gridIdSuffix;
        }

        protected function getMaxCellsPerRow()
        {
            $designerRulesType      = static::getDesignerRulesType();
            $designerRulesClassName = $designerRulesType . 'DesignerRules';
            $designerRules          = new $designerRulesClassName();
            return $designerRules->maxCellsPerRow();
        }

        protected function renderModalContainer()
        {
            return ZurmoHtml::tag('div', array(
                        'id' => ModelElement::MODAL_CONTAINER_PREFIX . '-' . $this->getSearchFormId()
                   ), '');
        }

        /**
         * Render a hidden field to filter models by starred only
         * @param  ZurmoActiveForm $form
         * @return string
         */
        protected function renderStarredFilterHidenField($form)
        {
            $content = null;
            if (in_array('StarredInterface', class_implements($this->model->getModel())))
            {
                $content .= $form->hiddenField($this->model, 'filterByStarred',
                                               array('class' => $form->id . '_filterByStarred'));
            }
            return $content;
        }
    }
?>
