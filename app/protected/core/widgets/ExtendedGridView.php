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

    Yii::import('zii.widgets.grid.CGridView');

    /**
     * Extends the yii CGridView to provide additional functionality.
     * @see CGridView class
     */
    class ExtendedGridView extends CGridView
    {
        const CLONE_SUMMARY_CLASS = 'list-view-items-summary-clone';

        public $template = "{selectRowsSelectors}{summary}\n{items}\n{pager}";

        /**
         * Override to have proper XHTML compliant space value
         */
        public $nullDisplay = '&#160;';

        /**
         * Override to have proper XHTML compliant space value
         */
        public $blankDisplay = '&#160;';

        public $cssFile = false;

        public $summaryCloneId;

        public $renderSpanOnEmptyText = true;

        /**
         * Populate with array of colgroup widths. An array of array(10,80,10) would create
         * <colgroup>
         * <col style="width:10%" /><col style="width:80%" /><col style="width:10%" />
         * </colgroup>
         * @var array
         */
        public $tableColumnGroup = array();

        public function init()
        {
            $this->baseScriptUrl = Yii::app()->getAssetManager()->publish(
                                        Yii::getPathOfAlias('application.core.widgets.assets'))
                                        . '/extendedGridView';
            parent::init();
        }

        /**
         * Renders the top pager content
         */
        public function renderTopPager()
        {
            if (!$this->enablePagination)
            {
                return;
            }
            $pager = array();
            $class = 'TopLinkPager';
            if (is_array($this->pager))
            {
                $pager = $this->pager;
                if (isset($pager['class']))
                {
                    throw new NotSupportedException();
                }
            }
            else
            {
                throw new NotSupportedException();
            }
            $pager['pages'] = $this->dataProvider->getPagination();
            if ($pager['pages']->getPageCount() > 1)
            {
                echo '<div class="' . $this->pagerCssClass . '">';
                $this->widget($class, $pager);
                echo '</div>';
            }
        }

        /**
         * Renders the bottom pager content
         */
        public function renderBottomPager()
        {
            if (!$this->enablePagination)
            {
                return;
            }
            $pager = array();
            $class = 'BottomLinkPager';
            if (is_array($this->pager))
            {
                $pager = $this->pager;
                if (isset($pager['class']))
                {
                    throw new NotSupportedException();
                }
            }
            else
            {
                throw new NotSupportedException();
            }
            $pager['pages'] = $this->dataProvider->getPagination();
            if ($pager['pages']->getPageCount() > 1)
            {
                echo '<div class="' . $this->pagerCssClass . '">';
                $this->widget($class, $pager);
                echo '</div>';
            }
        }

        /**
         * Override to always render pager div if paging is enabled.
         * (non-PHPdoc)
         * @see CBaseListView::renderPager()
         */
        public function renderPager()
        {
            if (!$this->enablePagination)
            {
                return;
            }
            $pager = array();
            $class = 'CLinkPager';
            if (is_string($this->pager))
            {
                $class = $this->pager;
            }
            elseif (is_array($this->pager))
            {
                $pager = $this->pager;
                if (isset($pager['class']))
                {
                    $class = $pager['class'];
                    unset($pager['class']);
                }
            }
            $pager['pages'] = $this->dataProvider->getPagination();
            echo '<div class="' . $this->pagerCssClass . '">';
            $this->widget($class, $pager);
            echo '</div>';
        }

        /**
         * Renders the summary-clone changer. When the summary changes, it should update the summary-clone in the
         * searchview if it is available.  The ModalListView does not rely on this because it does not run
         * jquery.globalEval on ajax changes such as pagination.  It instead will call processListViewSummaryClone which
         * is decleared @see ModalListView->getCGridViewAfterAjaxUpdate()
         *
         */
        public function renderSummary()
        {
            parent::renderSummary();
            Yii::app()->clientScript->registerScript($this->id . '_listViewSummaryChangeScript', '
                processListViewSummaryClone("' . $this->id . '", "' . $this->summaryCssClass . '", "' . $this->summaryCloneId . '");
            ');
        }

        /**
         * Renders the empty message when there is no data.
         */
        public function renderEmptyText()
        {
            if ($this->emptyText === null)
            {
                $emptyText = Zurmo::t('Core', 'No results found');
            }
            else
            {
                $emptyText = $this->emptyText;
            }

            if ($this->renderSpanOnEmptyText)
            {
                $icon = ZurmoHtml::tag('span', array('class' => 'icon-empty'), '');
                echo CHtml::tag('span', array('class' => 'empty'), $icon . $emptyText);
            }
            else
            {
                echo $emptyText;
            }
        }

        /**
         * Overridden CBaseListView::renderKeys()
         * Sets absolute url for contextive external requests
         */
        public function renderKeys()
        {
            echo CHtml::openTag('div', array(
                'class' => 'keys',
                'style' => 'display:none',
                'title' => Yii::app()->getRequest()->resolveAndGetUrl(),
            ));
            foreach ($this->dataProvider->getKeys() as $key)
            {
                echo "<span>" . CHtml::encode($key) . "</span>";
            }
            echo "</div>\n";
        }

        /**
         * Renders the table header.
         */
        public function renderTableHeader()
        {
            if (!$this->hideHeader)
            {
                echo "<thead>\n";
                $this->renderTableColumnGroup();
                if ($this->filterPosition === self::FILTER_POS_HEADER)
                {
                    $this->renderFilter();
                }
                echo "<tr>\n";
                foreach ($this->columns as $column)
                {
                    $column->renderHeaderCell();
                }
                echo "</tr>\n";
                if ($this->filterPosition === self::FILTER_POS_BODY)
                {
                    $this->renderFilter();
                }
                echo "</thead>\n";
            }
            elseif ($this->filter !== null && ($this->filterPosition === self::FILTER_POS_HEADER ||
                   $this->filterPosition === self::FILTER_POS_BODY))
            {
                echo "<thead>\n";
                $this->renderFilter();
                echo "</thead>\n";
            }
        }

        protected function renderTableColumnGroup()
        {
            if (!empty($this->tableColumnGroup))
            {
                echo '<colgroup>';
                foreach ($this->tableColumnGroup as $width)
                {
                    echo '<col style="width:' . $width . '" />';
                }
                echo '</colgroup>';
            }
        }
    }
?>