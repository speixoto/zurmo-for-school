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

    abstract class ReportResultsGridView extends View implements ListViewInterface
    {
        protected $controllerId;

        protected $moduleId;

        protected $dataProvider;

        protected $rowsAreExpandable = false;

        /**
         * Unique identifier of the list view widget. Allows for multiple list view
         * widgets on a single page.
         */
        protected $gridId;

        /**
         * Additional unique identifier.
         * @see $gridId
         */
        protected $gridIdSuffix;

        /**
         * Array containing CGridViewPagerParams
         */
        protected $gridViewPagerParams = array();

        protected $emptyText = null;

        public function __construct(
            $controllerId,
            $moduleId,
            $dataProvider,
            $gridIdSuffix = null,
            $gridViewPagerParams = array()
        )
        {
            assert('$dataProvider instanceof ReportDataProvider');
            assert('is_array($gridViewPagerParams)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->dataProvider           = $dataProvider;
            $this->rowsAreSelectable      = true;
            $this->gridIdSuffix           = $gridIdSuffix;
            $this->gridViewPagerParams    = $gridViewPagerParams;
            $this->gridId                 = 'report-results-grid-view';
        }

        protected function renderContent()
        {
            if(!$this->isDataProviderValid())
            {
                throw new NotSupportedException();
            }
            return $this->renderResultsGridContent();
        }

        protected function renderResultsGridContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ReportResultsGridView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content  = $cClipWidget->getController()->clips['ReportResultsGridView'] . "\n";
            $content .= $this->renderScripts();
            return $content;
        }

        protected function getGridViewWidgetPath()
        {
            return 'application.core.widgets.ExtendedGridView';
        }

        protected function getCGridViewParams()
        {
            $columns = $this->getCGridViewColumns();
            assert('is_array($columns)');

            return array(
                'id' => $this->getGridViewId(),
                'htmlOptions' => array(
                    'class' => 'cgrid-view'
                ),
                'loadingCssClass'      => 'loading',
                'dataProvider'         => $this->dataProvider,
                'pager'                => $this->getCGridViewPagerParams(),
                'beforeAjaxUpdate'     => $this->getCGridViewBeforeAjaxUpdate(),
                'afterAjaxUpdate'      => $this->getCGridViewAfterAjaxUpdate(),
                'columns'              => $columns,
                'nullDisplay'          => '&#160;',
                'pagerCssClass'        => static::getPagerCssClass(),
                'showTableOnEmpty'     => $this->getShowTableOnEmpty(),
                'emptyText'            => $this->getEmptyText(),
                'template'             => static::getGridTemplate(),
                'summaryText'          => static::getSummaryText(),
                'summaryCssClass'      => static::getSummaryCssClass(),
                'enableSorting'        => false,
            );
        }

        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>'; //todo: do we need this , maybe it is for pagination?
            return "{summary}\n{items}\n{pager}" . $preloader;
        }

        protected static function getPagerCssClass()
        {
            return 'pager horizontal';
        }

        protected static function getSummaryText()
        {
            return Yii::t('Default', '{count} result(s)');
        }

        protected static function getSummaryCssClass()
        {
            return 'summary';
        }

        protected function getCGridViewPagerParams()
        {
            $defaultGridViewPagerParams = array(
                'firstPageLabel'   => '<span>first</span>',
                'prevPageLabel'    => '<span>previous</span>',
                'nextPageLabel'    => '<span>next</span>',
                'lastPageLabel'    => '<span>last</span>',
                'class'            => 'SimpleListLinkPager',
                'paginationParams' => GetUtil::getData(),
                'route'            => 'defaultPortlet/details',
            );
            if (empty($this->gridViewPagerParams))
            {
                return $defaultGridViewPagerParams;
            }
            else
            {
                return array_merge($defaultGridViewPagerParams, $this->gridViewPagerParams);
            }
        }

        protected function getShowTableOnEmpty()
        {
            return true;
        }

        protected function getEmptyText()
        {
            return $this->emptyText;
        }

        public function getGridViewId()
        {
            return $this->gridId . $this->gridIdSuffix;
        }

        /**
         * Get the meta data and merge with standard CGridView column elements
         * to create a column array that fits the CGridView columns API
         */
        protected function getCGridViewColumns()
        {
            $columns = array();
            foreach ($this->dataProvider->getReport()->getDisplayAttributes() as $key => $displayAttribute)
            {
                $columnClassName  = $this->resolveColumnClassNameForListViewColumnAdapter($displayAttribute);
                $attributeName    = $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                $params           = $this->resolveParamsForColumnElement($displayAttribute);
                $columnAdapter    = new $columnClassName($attributeName, $this, $params);
                $column           = $columnAdapter->renderGridViewData();
                $column['header'] = $displayAttribute->label;
                if (!isset($column['class']))
                {
                    $column['class'] = 'DataColumn';
                }
                array_push($columns, $column);
            }
            return $columns;
        }

        protected function resolveColumnClassNameForListViewColumnAdapter($displayAttribute)
        {
            $displayElementType = $displayAttribute->getDisplayElementType();
            if(@class_exists($displayElementType . 'ForReportListViewColumnAdapter'))
            {
                return $displayElementType . 'ForReportListViewColumnAdapter';
            }
            else
            {
                return $displayElementType . 'ListViewColumnAdapter';
            }
        }

        protected function resolveParamsForColumnElement($displayAttribute)
        {
            $params  = array();
            if($displayAttribute->isALinkableAttribute() == 'name')
            {
                $params['isLink'] = true;
            }
            elseif($displayAttribute->isATypeOfCurrencyValue())
            {
                $params['currencyValueConversionType'] = $this->dataProvider->getReport()->getCurrencyConversionType();
                $params['spotConversionCurrencyCode']  = $this->dataProvider->getReport()->getSpotConversionCurrencyCode();
            }
            return $params;
        }

        protected function getCGridViewBeforeAjaxUpdate()
        {
            return 'js:function(id, options) { makeSmallLoadingSpinner(id, options); }';
        }

        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, data) {
                        processAjaxSuccessError(id, data);
                        var $data = $(data);
                        jQuery.globalEval($data.filter("script").last().text());
                    }';
            // End Not Coding Standard
        }

        public function getLinkString($attributeString, $attribute)
        {
            $string  = 'ZurmoHtml::link(';
            $string .=  $attributeString . ', ';
            $string .= 'ReportResultsGridUtil::makeUrlForLink("' . $attribute . '", $data)';
            $string .= ', array("target" => "new"))';
            return $string;
        }

        protected function renderScripts()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/ListViewUtils.js');
        }
    }
?>