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
        
        protected $gridIdSuffix;
                                               
        public function __construct($moduleNamesAndLabelsAndAll, $sourceUrl, $gridSuffix = null)
        {
            assert('is_array($moduleNamesAndLabelsAndAll)');
            assert('is_string($sourceUrl)');           
            $this->moduleNamesAndLabelsAndAll   = $moduleNamesAndLabelsAndAll;
            $this->sourceUrl                    = $sourceUrl;
            $this->gridIdSuffix                 = $gridSuffix;
        }

        protected function renderContent()
        {            
            $model = new MixedModelsSearchForm();    
            $model->setGlobalSearchAttributeNamesAndLabelsAndAll($this->moduleNamesAndLabelsAndAll);            
            $content = "<div class='wide form'>";
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'NoRequiredsActiveForm',
                                                                array('id'                   => $this->getSearchFormId(),                                                                      
                                                                      'enableAjaxValidation' => false,
                                                                      'clientOptions'        => array(),
                                                                      'focus'                => array($model,'term'),
                                                                      'method'               => 'get',
                                                                )
                                                            );    
            $content .= $formStart;           
            $content .= "<div class=search-view-0>";
            $scope = new MixedModelsSearchElement($model, 'term', $form, array( 'htmlOptions' => array ('id' => 'term')));
            $content .= $scope->render();           
            //Search button
            $params = array();
            $params['label']       = Yii::t('Default', 'Search');
            $params['htmlOptions'] = array('id' => $this->getSearchFormId() . '-search', 'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $searchElement = new SaveButtonActionElement(null, null, null, $params);
            $content .= $searchElement->render();
            $content .= "</div>";
            $formEnd  = $clipWidget->renderEndWidget(); 
            $content .= "</div>";
            return $content;
        }
        /*
        protected function renderGlobalSearchContent()
        {            
            $content = $this->renderGlobalSearchScopingInputContent();         
            return $content;
        }*/

        /*
        protected function renderGlobalSearchScopingInputContent()
        {
            $cClipWidget   = new CClipWidget();
            $cClipWidget->beginClip("MixedModelsScopedJuiMultiSelect");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.ScopedSearchJuiMultiSelect', array(
                'dataAndLabels'  => $this->moduleNamesAndLabelsAndAll,
                'selectedValue'  => 'All',
                'inputId'        => 'scope',
                'inputName'      => 'scope',
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
        */    
        protected function getSearchFormId()
        {         
            return 'mixed-models-form' . $this->gridIdSuffix;        
        }        
    }
?>