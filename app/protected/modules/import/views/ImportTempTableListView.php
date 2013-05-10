<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Base class for working with import temp table data
     */
    class ImportTempTableListView extends ListView
    {
        /**
         * @var ReportDataProvider
         */
        protected $dataProvider;

        /**
         * @var bool
         */
        protected $rowsAreExpandable = false;

        /**
         * @var null|array
         */
        protected $mappingData;

        /**
         * @param $controllerId
         * @param $moduleId
         * @param ImportDataProvider $dataProvider
         * @param array $mappingData
         * @param null $gridIdSuffix
         */
        public function __construct( $controllerId, $moduleId, ImportDataProvider $dataProvider, $mappingData, $gridIdSuffix = null)
        {
            parent::__construct($controllerId, $moduleId, 'NotUsed', $dataProvider, array(), false, $gridIdSuffix);
            $this->rowsAreSelectable = false;
            $this->mappingData       = $mappingData;
            $this->gridId            = 'import-temp-table-list-view';
        }

        public function getLinkString($attributeString, $attribute)
        {
            throw new NotSupportedException();
        }

        /**
         * @return string
         */
        protected static function getPagerCssClass()
        {
            return 'pager horizontal';
        }

        /**
         * @return string
         */
        protected function getGridViewWidgetPath()
        {
            return 'application.modules.import.widgets.ImportTempTableExtendedGridView';
        }

        /**
         * @return array
         */
        protected function getCGridViewParams()
        {
            return array_merge(parent::getCGridViewParams(), array('enableSorting' => false));
            //'expandableRows'       => $this->rowsAreExpandable(), //todo: turn on or remove
        }

        /**
         * @return array
         */
        protected function getCGridViewPagerParams()
        {
            return array(
                'firstPageLabel'   => '<span>first</span>',
                'prevPageLabel'    => '<span>previous</span>',
                'nextPageLabel'    => '<span>next</span>',
                'lastPageLabel'    => '<span>last</span>',
                'class'            => 'SimpleListLinkPager',
                'paginationParams' => GetUtil::getData(),
                'route'            => 'defaultPortlet/details',
            );
        }

        /**
         * Get the meta data and merge with standard CGridView column elements
         * to create a column array that fits the CGridView columns API
         * @return array
         */
        protected function getCGridViewColumns()
        {
            $columns = array();
/**
            if ($this->rowsAreExpandable())
            {
                $firstColumn = array(
                    'class'               => 'DrillDownColumn',
                    'id'                  => $this->gridId . $this->gridIdSuffix . '-rowDrillDown',
                    'htmlOptions'         => array('class' => 'hasDrillDownLink')
                );
                array_push($columns, $firstColumn);
            }
 * **/
            $headerRow = ImportDatabaseUtil::getFirstRowByTableName($this->dataProvider->getTableName());
            foreach ($headerRow as $columnName => $label)
            {
                if (!in_array($columnName, ImportDatabaseUtil::getReservedColumnNames()) &&
                    $this->mappingData[$columnName]['type'] == 'importColumn' &&
                    $this->mappingData[$columnName]['attributeIndexOrDerivedType'] != null)
                {
                    $params           = array();
                    $columnAdapter    = new BeanStringListViewColumnAdapter($columnName, $this, $params);
                    $column           = $columnAdapter->renderGridViewData();
                    $column['header'] = $this->resolveHeaderLabelByColumnNameAndLabel($columnName, $label);
                    if (!isset($column['class']))
                    {
                        $column['class'] = 'DataColumn';
                    }
                    array_push($columns, $column);
                }
            }
            return $columns;
        }

        protected function resolveHeaderLabelByColumnNameAndLabel($columnName, $label)
        {
            if($label == null)
            {
                $headerLabel = $columnName;
            }
            else
            {
                $headerLabel = $label;
            }
            return $headerLabel;
        }

        /**
         * @return string
         */
        protected function getCGridViewBeforeAjaxUpdate()
        {
            return 'js:function(id, options) {makeSmallLoadingSpinner(true, "#"+id + " > .list-preloader"); }'; // Not Coding Standard
        }

        /**
         * Do not run global eval, since it causes doubling up of ajax requests on the pager.
         * (non-PHPdoc)
         * @see ListView::getCGridViewAfterAjaxUpdate()
         */
        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, data) {
                        processAjaxSuccessError(id, data);
                    }';
            // End Not Coding Standard
        }

        /**
         * @return bool
         */
        //todo: can remove maybe, we don't need this depending on how we do this.
        protected function rowsAreExpandable()
        {
            if (count($this->dataProvider->getReport()->getDrillDownDisplayAttributes()) > 0)
            {
                return true;
            }
            return false;
        }
    }
?>