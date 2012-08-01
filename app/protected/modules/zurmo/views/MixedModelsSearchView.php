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
     * Base view for displaying a global search user interface..
     */
    class MixedModelsSearchView extends View
    {
        protected $moduleNamesAndLabelsAndAll;

        protected $sourceUrl;

        public function __construct($moduleNamesAndLabelsAndAll, $sourceUrl)
        {
            assert('is_array($moduleNamesAndLabelsAndAll)');
            assert('is_string($sourceUrl)');
            $this->moduleNamesAndLabelsAndAll = $moduleNamesAndLabelsAndAll;
            $this->sourceUrl            = $sourceUrl;
        }

        protected function renderContent()
        {            
            $model = new MixedModelsSearchForm;            
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'NoRequiredsActiveForm',
                                                                array('id'                   => '',
                                                                      'action'               => null,
                                                                      'enableAjaxValidation' => '',
                                                                      'clientOptions'        => '',

                                                                )
                                                            );    
            $content = $formStart;
            $content .= $this->renderGlobalSearchContent();                        
            //Input for search
            $input = new TextElement($model, 'term', $form);
            $content .= $input->render();
            //Search button
            $params = array();
            $params['label']       = Yii::t('Default', 'Search');
            $params['htmlOptions'] = array('id' => 'mixed-models-search', 'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $searchElement = new SaveButtonActionElement(null, null, null, $params);
            $content .= $searchElement->render();
            $formEnd  = $clipWidget->renderEndWidget();
            return $content;
        }

        protected function renderGlobalSearchContent()
        {            
            $content = $this->renderGlobalSearchScopingInputContent();         
            return $content;
        }

        protected function renderGlobalSearchScopingInputContent()
        {
            $cClipWidget   = new CClipWidget();
            $cClipWidget->beginClip("MixedModelsScopedJuiMultiSelect");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.ScopedSearchJuiMultiSelect', array(
                'dataAndLabels'  => $this->moduleNamesAndLabelsAndAll,
                'selectedValue'  => 'All',
                'inputId'        => 'mixedModelsSearchScope',
                'inputName'      => 'mixedModelsSearchScope',
                'options'        => array(
                                          'selectedText' => '',
                                          'noneSelectedText' => '', 'header' => false,
                                          'position' => array('my' =>  'right top', 'at' => 'right bottom')),
                'htmlOptions'    => array('class' => 'ignore-style')
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['MixedModelsScopedJuiMultiSelect'];
            return $content;
        }
    }
?>