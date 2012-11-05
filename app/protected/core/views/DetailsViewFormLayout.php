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

    class DetailsViewFormLayout extends FormLayout
    {
        /**
         * Used by the render of the form layout when the panels are to be displayed in a tabbed format.
         * @see FormLayout::PANELS_DISPLAY_TYPE_TABBED
         */
        protected $tabsContent;

        /**
         * Label to used for the link to show more panels.
         * @see FormLayout::PANELS_DISPLAY_TYPE_FIRST
         * @var string
         */
        protected $morePanelsLinkLabel;

        /**
         * Label to used for the link to show less panels.
         * @see FormLayout::PANELS_DISPLAY_TYPE_FIRST
         * @var string
         */
        protected $lessPanelsLinkLabel;

        protected $labelsHaveOwnCells = true;

        /**
         * Set the labels to have their own cells or not.
         * @param boolean $hasOwnCells
         */
        public function labelsHaveOwnCells($hasOwnCells)
        {
            assert('is_bool($hasOwnCells)');
            $this->labelsHaveOwnCells = $hasOwnCells;
        }

        /**
         * Render a form layout.
         *  Gets appropriate meta data and loops through it. Builds form content
         *  as it loops through. For each element in the form it calls the appropriate
         *  Element class.
         * @return A string containing the element's content.
         */
        public function render()
        {
            $content        = '';
            if ($this->shouldRenderTabbedPanels())
            {
                $content .= $this->errorSummaryContent;
            }
            $tabsContent    = '';
            foreach ($this->metadata['global']['panels'] as $panelNumber => $panel)
            {
                $content .= $this->renderDivTagByPanelNumber($panelNumber);
                $content .= $this->renderPanelHeaderByPanelNumberAndPanel($panelNumber, $panel);
                $content .= $this->resolveStartingTableTagAndColumnQuantityClass($panel);
                $content .= TableUtil::getColGroupContent(static::getMaximumColumnCountForAllPanels($this->metadata), $this->labelsHaveOwnCells);
                $content .= '<tbody>';

                foreach ($panel['rows'] as $row)
                {
                    $cellsContent = null;
                    foreach ($row['cells'] as $cell)
                    {
                        if (is_array($cell['elements']))
                        {
                            foreach ($cell['elements'] as $renderedElement)
                            {
                                $cellsContent .= $renderedElement;
                            }
                        }
                    }
                    if (!empty($cellsContent))
                    {
                        $this->resolveRowWrapperTag($content, $cellsContent);
                    }
                }
                $content .= $this->renderLastPanelRowsByPanelNumber($panelNumber);
                $content .= '</tbody>';
                $content .= '</table>';
                if ($this->shouldRenderTabbedPanels())
                {
                    $content .= '</div>';
                }
                $content .= '</div>';
            }
            $this->renderScripts();
            return $this->resolveFormLayoutContent($content);
        }

        protected function resolveStartingTableTagAndColumnQuantityClass($panel)
        {
            assert('is_array($panel)');
            if (static::getMaximumColumnCountForSpecificPanels($panel) == 2)
            {
                return '<table class="double-column">';
            }
            return '<table>';
        }

        /**
         * If the cell content contains a <tr at the beginning, then assume we do not
         * need to wrap or end with a tr
         */
        protected function resolveRowWrapperTag(& $content, $cellsContent)
        {
            assert('is_string($content) || $content == null');
            assert('is_string($cellsContent)');
            if (strpos($cellsContent, '<tr') === 0)
            {
                $content .= $cellsContent;
            }
            else
            {
                $content .= '<tr>';
                $content .= $cellsContent;
                $content .= '</tr>';
            }
        }

        protected function renderPanelHeaderByPanelNumberAndPanel($panelNumber, $panel)
        {
            if ($this->shouldRenderTabbedPanels())
            {
                $tabId = $this->uniqueId . '-panel-tab-' . $panelNumber;
                $content = '<div id="' . $tabId . '">';
                if (!empty($panel['title']))
                {
                    $tabTitle = Yii::t('Default', $panel['title']); //Attempt a final translation if available.
                }
                else
                {
                    $tabTitle = Yii::t('Default', 'Tab'). ' ' . ($panelNumber + 1);
                }
               $this->addTabsContent('<li><a href="#' . $tabId . '">' . $tabTitle . '</a></li>');
               return $content;
            }
            else
            {
                if (!empty($panel['title']))
                {
                    return '<div class="panelTitle">' . Yii::t('Default', $panel['title']) . '</div>'; //Attempt a final translation if available.
                }
            }
        }

        protected function renderDivTagByPanelNumber($panelNumber)
        {
            if ($panelNumber > 0 && $this->shouldHidePanelsAfterFirstPanel())
            {
                return '<div class="panel more-view-panel-' . $this->uniqueId . '" style="display:none;">';
            }
            else
            {
                return '<div class="panel">';
            }
        }

        protected function renderLastPanelRowsByPanelNumber($panelNumber)
        {
            $content = null;
            if ($panelNumber == 0 && $this->shouldHidePanelsAfterFirstPanel())
            {
                $content .= '<tr>';
                $content .= '<td  colspan = "' . $this->maxCellsPerRow . '">';
                $content .= ZurmoHtml::link($this->getMorePanelsLinkLabel(),
                                        $this->uniqueId, array('class' => 'more-panels-link', 'id' => 'show-more-panels-link-' . $this->uniqueId . ''));
                $content .= ZurmoHtml::link($this->getLessPanelsLinkLabel(),
                                        $this->uniqueId,
                                        array('class' => 'more-panels-link', 'id' => 'show-less-panels-link-' . $this->uniqueId . '',
                                              'style' => 'display:none;'));
                $content .= '</td>';
                $content .= '</tr>';
            }
            return $content;
        }

        protected function renderScripts()
        {
            if ($this->shouldHidePanelsAfterFirstPanel())
            {
                Yii::app()->clientScript->registerScript('showMorePanels' . $this->uniqueId, "
                    $('#show-more-panels-link-" . $this->uniqueId . "').click( function()
                        {
                            $('.more-view-panel-' + $(this).attr('href')).show();
                            $('#show-more-panels-link-" . $this->uniqueId . "').hide();
                            $('#show-less-panels-link-" . $this->uniqueId . "').show();
                            return false;
                        }
                    );
                    $('#show-less-panels-link-" . $this->uniqueId . "').click( function()
                        {
                            $('.more-view-panel-' + $(this).attr('href')).hide();
                            $('#show-more-panels-link-" . $this->uniqueId . "').show();
                            $('#show-less-panels-link-" . $this->uniqueId . "').hide();
                            return false;
                        }
                    );");
            }
        }

        protected function resolveFormLayoutContent($content)
        {
            if ($this->shouldRenderTabbedPanels())
            {
                $content = '<div id="' . $this->uniqueId . '-panel-tabs"><ul>' . $this->getTabsContent() . '</ul>' . $content . '</div>';
                // Begin Not Coding Standard
                Yii::app()->clientScript->registerScript('initializeTabs' . $this->uniqueId, "
                    $(function() {
                        $( '#" . $this->uniqueId . "-panel-tabs' ).tabs({selected: 0});
                    });");
                // End Not Coding Standard
            }
            return $content;
        }

        protected function shouldHidePanelsAfterFirstPanel()
        {
            if (isset($this->metadata['global']['panelsDisplayType']) &&
            $this->metadata['global']['panelsDisplayType'] == FormLayout::PANELS_DISPLAY_TYPE_FIRST)
            {
                return true;
            }
            return false;
        }

        protected function shouldRenderTabbedPanels()
        {
            if (isset($this->metadata['global']['panelsDisplayType']) &&
            $this->metadata['global']['panelsDisplayType'] == FormLayout::PANELS_DISPLAY_TYPE_TABBED &&
            count($this->metadata['global']['panels']) > 1)
            {
                return true;
            }
            return false;
        }

        protected function addTabsContent($content)
        {
            $this->tabsContent .= $content;
        }

        protected function getTabsContent()
        {
            return $this->tabsContent;
        }

        public function setMorePanelsLinkLabel($label)
        {
            $this->morePanelsLinkLabel = $label;
        }

        public function setLessPanelsLinkLabel($label)
        {
            $this->lessPanelsLinkLabel = $label;
        }

        protected function getMorePanelsLinkLabel()
        {
            if ($this->morePanelsLinkLabel == null)
            {
                Yii::t('Default', 'More Options');
            }
            else
            {
                return $this->morePanelsLinkLabel;
            }
        }

        protected function getLessPanelsLinkLabel()
        {
            if ($this->lessPanelsLinkLabel == null)
            {
                Yii::t('Default', 'Fewer Options');
            }
            else
            {
                return $this->lessPanelsLinkLabel;
            }
        }
    }
?>