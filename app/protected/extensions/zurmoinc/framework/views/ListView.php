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
     * The base View for a module's list view.
     */
    abstract class ListView extends ModelView
    {
        protected $controllerId;

        protected $moduleId;

        protected $dataProvider;

        /**
         * True/false to decide if each row in the list view widget
         * will have a checkbox.
         */
        protected $rowsAreSelectable = false;

        /**
         * Unique identifier of the list view widget. Allows for multiple list view
         * widgets on a single page.
         * @see $
         */
        protected $gridId;

        /**
         * Additional unique identifier.
         * @see $gridId
         */
        protected $gridIdSuffix;

        /**
         * Array of model ids. Each id is for a different row checked off
         */
        protected $selectedIds;

        private $resolvedMetadata;

        /**
         * Constructs a list view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct(
            $controllerId,
            $moduleId,
            $modelClassName,
            $dataProvider,
            $selectedIds,
            $gridIdSuffix = null
        )
        {
            assert('is_array($selectedIds)');
            assert('is_string($modelClassName)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->modelClassName         = $modelClassName;
            $this->dataProvider           = $dataProvider;
            $this->rowsAreSelectable      = true;
            $this->selectedIds            = $selectedIds;
            $this->gridIdSuffix           = $gridIdSuffix;
            $this->gridId                 = 'list-view';
        }

        /**
         * Renders content for a list view. Utilizes a CActiveDataprovider
         * and a CGridView widget.
         * and form layout.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content = $this->renderViewToolBar();
            $content .= $cClipWidget->getController()->clips['ListView'] . "\n";
            if ($this->rowsAreSelectable)
            {
                $content .= CHtml::hiddenField($this->gridId . $this->gridIdSuffix . '-selectedIds', implode(",", $this->selectedIds)) . "\n"; // Not Coding Standard
            }
            $content .= $this->renderScripts();
            return $content;
        }

        protected function getGridViewWidgetPath()
        {
            return 'ext.zurmoinc.framework.widgets.ExtendedGridView';
        }

        public function getRowsAreSelectable()
        {
            return $this->rowsAreSelectable;
        }

        protected function getCGridViewParams()
        {
            $columns = $this->getCGridViewColumns();
            assert('is_array($columns)');

            $preloader = '<div class="list-preloader"><img src="data:image/svg+xml,%3C%3Fxml%20version%3D%221.0%22%20encoding%3D%22utf-8%22%3F%3E%3Csvg%20version%3D%221.1%22%20width%3D%2230px%22%20height%3D%2230px%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20xmlns%3Axlink%3D%22http%3A%2F%2Fwww.w3.org%2F1999%2Fxlink%22%3E%3Cdefs%3E%3Cg%20id%3D%22pair%22%3E%3Cellipse%20cx%3D%2210%22%20cy%3D%220%22%20rx%3D%225%22%20ry%3D%222%22%20style%3D%22fill%3A%23ccc%3B%20fill-opacity%3A0.5%3B%22%2F%3E%3Cellipse%20cx%3D%22-10%22%20cy%3D%220%22%20rx%3D%225%22%20ry%3D%222%22%20style%3D%22fill%3A%23ccc%3B%20fill-opacity%3A0.5%3B%22%2F%3E%3C%2Fg%3E%3C%2Fdefs%3E%3Cg%20transform%3D%22translate(15%2C15)%22%3E%3Cg%3E%3CanimateTransform%20attributeName%3D%22transform%22%20type%3D%22rotate%22%20from%3D%220%22%20to%3D%22360%22%20dur%3D%222s%22%20repeatDur%3D%22indefinite%22%2F%3E%3Cuse%20xlink%3Ahref%3D%22%23pair%22%2F%3E%3Cuse%20xlink%3Ahref%3D%22%23pair%22%20transform%3D%22rotate(45)%22%2F%3E%3Cuse%20xlink%3Ahref%3D%22%23pair%22%20transform%3D%22rotate(90)%22%2F%3E%3Cuse%20xlink%3Ahref%3D%22%23pair%22%20transform%3D%22rotate(135)%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" /></div>';

            return array(
                'id' => $this->getGridViewId(),
                'htmlOptions' => array(
                    'class' => 'cgrid-view'
                ),
                'loadingCssClass'  => 'loading',
                'dataProvider'     => $this->getDataProvider(),
                'selectableRows'   => $this->getCGridViewSelectableRowsCount(),
                'pager'            => $this->getCGridViewPagerParams(),
                'beforeAjaxUpdate' => $this->getCGridViewBeforeAjaxUpdate(),
                'afterAjaxUpdate'  => $this->getCGridViewAfterAjaxUpdate(),
                'cssFile'          => Yii::app()->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/cgrid-view.css',
                'columns'          => $columns,
                'nullDisplay'      => '&#160;',
                'showTableOnEmpty' => $this->getShowTableOnEmpty(),
                'emptyText'        => $this->getEmptyText(),
                'template'         => "\n{items}\n{pager}".$preloader,
            );
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'cssFile'          => Yii::app()->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/cgrid-view.css',
                    'prevPageLabel'    => '<span>previous</span>',
                    'nextPageLabel'    => '<span>next</span>',
                    'class'            => 'EndlessListLinkPager',
                    'paginationParams' => GetUtil::getData(),
                    'route'            => $this->getGridViewActionRoute('list', $this->moduleId),
                );
        }

        protected function getShowTableOnEmpty()
        {
            return true;
        }

        protected function getEmptyText()
        {
            return null;
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
            if ($this->rowsAreSelectable)
            {
                $checked = 'in_array($data->id, array(' . implode(',', $this->selectedIds) . '))'; // Not Coding Standard
                $checkBoxHtmlOptions = array();
                $firstColumn = array(
                    'class'               => 'CheckBoxColumn',
                    'checked'             => $checked,
                    'id'                  => $this->gridId . $this->gridIdSuffix . '-rowSelector', // Always specify this as -rowSelector.
                    'checkBoxHtmlOptions' => $checkBoxHtmlOptions,
                );
                array_push($columns, $firstColumn);
            }
            $lastColumn = $this->getCGridViewLastColumn();
            if (!empty($lastColumn))
            {
                array_push($columns, $lastColumn);
            }
            $metadata = $this->getResolvedMetadata();
            foreach ($metadata['global']['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    foreach ($row['cells'] as $cell)
                    {
                        foreach ($cell['elements'] as $columnInformation)
                        {
                            $columnClassName = $columnInformation['type'] . 'ListViewColumnAdapter';
                            $columnAdapter  = new $columnClassName($columnInformation['attributeName'], $this, array_slice($columnInformation, 1));
                            $column = $columnAdapter->renderGridViewData();
                            if (!isset($column['class']))
                            {
                                $column['class'] = 'DataColumn';
                            }
                            array_push($columns, $column);
                        }
                    }
                }
            }

            return $columns;
        }

        protected function resolveMetadata()
        {
            return self::getMetadata();
        }

        protected function getResolvedMetadata()
        {
            if ($this->resolvedMetadata != null)
            {
                return $this->resolvedMetadata;
            }
            $this->resolvedMetadata = $this->resolveMetadata();
            return $this->resolvedMetadata;
        }

        protected function getCGridViewBeforeAjaxUpdate()
        {
            if ($this->rowsAreSelectable)
            {
                return 'js:function(id, options) {addListViewSelectedIdsToUrl(id, options);}';
            }
            else
            {
                return null;
            }
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

        /**
         * Returns meta data for use in automatically generating the view.
         * The meta data is comprised of columns. The parameters match the
         * parameters used in CGridView. See link below for more information.
         * http://www.yiiframework.com/doc/api/1.1/CGridView/
         *
         * The below example is a simple listview with the 'id' and 'name' attributes
         * The 'name' column has a hyperlink to the detail view for that record.
         *
         * @code
            <?php
                $metadata = array(
                    array(
                        'class' => 'CDataColumn',
                        'name'  => 'id',
                    ),
                    array(
                        'class'           => 'CLinkColumn',
                        'header'          => Yii::t('Default', 'Name'),
                        'labelExpression' => '$data->name',
                        'urlExpression'   => 'Yii::app()->createUrl("/{$this->grid->getOwner()->getModule()->getId()}/{$this->grid->getOwner()->getId()}/details", array("id" => $data->id))',
                    )
                );
            ?>
         * @endcode
         */
        public static function getDefaultMetadata()
        {
            return array();
        }

        protected function getCGridViewSelectableRowsCount()
        {
            if ($this->rowsAreSelectable)
            {
                return 2;
            }
            else
            {
                return 0;
            }
        }

        protected function getCGridViewLastColumn()
        {
            $url  = 'Yii::app()->createUrl("' . $this->getGridViewActionRoute('edit');
            $url .= '", array("id" => $data->id))';
            return array(
                'class'           => 'ButtonColumn',
                'template'        => '{update}',
                'buttons' => array(
                    'update' => array(
                    'url' => $url,
                    'imageUrl'        => false,
                    'options'         => array('class' => 'pencil', 'title' => 'Update'),
                    'label'           => '!'
                    ),
                ),
            );
        }

        protected function getGridViewActionRoute($action, $moduleId = null)
        {
            if ($moduleId == null)
            {
                $moduleId = $this->moduleId;
            }
            return '/' . $moduleId . '/' . $this->controllerId . '/' . $action;
        }

        public function getLinkString($attributeString)
        {
            $string  = 'CHtml::link(';
            $string .=  $attributeString . ', ';
            $string .= 'Yii::app()->createUrl("' .
                        $this->getGridViewActionRoute('details') . '", array("id" => $data->id))';
            $string .= ')';
            return $string;
        }

        public function getRelatedLinkString($attributeString, $attributeName, $moduleId)
        {
            $string  = 'CHtml::link(';
            $string .=  $attributeString . ', ';
            $string .= 'Yii::app()->createUrl("' .
                        $this->getGridViewActionRoute('details', $moduleId) . '",
                        array("id" => $data->' . $attributeName . '->id))';
            $string .= ')';
            return $string;
        }

        public static function getDesignerRulesType()
        {
            return 'ListView';
        }

        /**
         * Module class name for models linked from rows in the grid view.
         */
        protected function getActionModuleClassName()
        {
            return get_class(Yii::app()->getModule($this->moduleId));
        }

        protected function getDataProvider()
        {
            return $this->dataProvider;
        }

        protected function renderScripts()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets')) . '/ListViewUtils.js');
        }
    }
?>
