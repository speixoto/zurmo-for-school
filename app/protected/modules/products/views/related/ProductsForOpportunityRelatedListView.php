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

    class ProductsForOpportunityRelatedListView extends ProductsRelatedListView
    {
        protected function getRelationAttributeName()
        {
            return 'opportunity';
        }

        public static function getDisplayDescription()
        {
            return Zurmo::t('ProductsModule', 'ProductsModulePluralLabel For OpportunitiesModuleSingularLabel',
                        LabelUtil::getTranslationParamsForAllModules());
        }

        protected static function resolveAjaxOptionsForSelectList()
        {
            $title = Zurmo::t('ProductsModule', 'ProductsModuleSingularLabel Search',
                            LabelUtil::getTranslationParamsForAllModules());
            return ModalView::getAjaxOptionsForModalLink($title);
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
                $firstColumn = $this->getCGridViewFirstColumn();
                array_push($columns, $firstColumn);
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
                            $columnClassName = 'Product' . ucfirst($columnInformation['attributeName']) . 'RelatedListViewColumnAdapter';
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
            $menuColumn = $this->getGridViewMenuColumn();
            if ($menuColumn == null)
            {
                $lastColumn = $this->getCGridViewLastColumn();
                if (!empty($lastColumn))
                {
                    array_push($columns, $lastColumn);
                }
            }
            else
            {
                array_push($columns, $menuColumn);
            }
            return $columns;
        }

	protected function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content	= $this->renderViewToolBar();
            $content	.= $this->renderAddProductLink();
	    $content	.= $this->renderConfigurationForm();
	    $content	.= $cClipWidget->getController()->clips['ListView'] . "\n";
            if ($this->rowsAreSelectable)
            {
                $content .= ZurmoHtml::hiddenField($this->gridId . $this->gridIdSuffix . '-selectedIds', implode(",", $this->selectedIds)) . "\n"; // Not Coding Standard
            }
            $content .= $this->renderScripts();
            return $content;
        }

	protected function renderAddProductLink()
	{
	    $title = Zurmo::t('ProductsModule', 'Add ProductsModuleSingularLabel',
                            LabelUtil::getTranslationParamsForAllModules());
	    $string  = "<p>" . ZurmoHtml::link($title, "#", array('id' => 'addProductPortletLink')) . "</p>";
            return $string;
	}

	protected function renderAddProductContent($form)
	{
	    $routeParams = $this->getCreateLinkRouteParameters();
	    $productElement = new ProductElement(new Product(), $this->getRelationAttributeName(), $form, array('inputIdPrefix' => 'product', 'htmlOptions' => array('display' => 'none')), $routeParams['relationModelId']);
	    $content = $productElement->render();
	    return $content;
        }

	protected function renderScripts()
	{
	    parent::renderScripts();
	    Yii::app()->clientScript->registerScript("AddProductElementToggleDisplay",
		    "$(function () {
		    $('#addProductPortletLink').click(function (e) {
			    e.preventDefault();
			    if($('#product-configuration-form').css('display') == 'none')
			    {
				$('#product-configuration-form').show('slow');
				//$('#product-portlet-grid-view').hide('slow');
			    }
			    else
			    {
				$('#product-configuration-form').hide('slow');
				//$('#product-portlet-grid-view').show('slow');
			    }
			})
			})
		    ");
	}

	public function getGridViewId()
        {
            return 'product-portlet-grid-view';
        }
    }
?>