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
     * Base class for working with import temp table data specifically for showing analysis results
     */
    class AnalysisResultsImportTempTableListView extends ImportTempTableListView
    {
        /**
         * @var ImportAnalysisResultsConfigurationForm
         */
        protected $configurationForm;

        public function __construct( $controllerId, $moduleId, ImportDataProvider $dataProvider, $mappingData, $importRulesType,
                                     ImportAnalysisResultsConfigurationForm $configurationForm, $gridIdSuffix = null)
        {
            parent::__construct($controllerId, $moduleId, $dataProvider, $mappingData, $importRulesType, $gridIdSuffix);
            $this->configurationForm = $configurationForm;
        }

        protected function resolveSecondColumn()
        {
            return $secondColumn = array(
                'class' => 'DataColumn',
                'type'  => 'raw',
                'value' => 'ImportTempTableListView::resolveAnalysisStatusLabel($data)'
            );
        }

        protected function getDefaultRoute()
        {
            return 'default/step5';
        }

        /**
         * @return array
         */
        protected function getCGridViewParams()
        {
            return array_merge(parent::getCGridViewParams(), array('expandableContentType' => self::EXPANDABLE_ANALYSIS_CONTENT_TYPE));
        }

        protected function renderConfigurationForm()
        {
            $formName   = 'import-analysis-results-configuration-form';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id' => $formName,
                )
            );
            $content  = $formStart;
            $content .= $this->renderConfigurationFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $this->registerConfigurationFormLayoutScripts($form);
            return $content;
        }

        protected function renderConfigurationFormLayout($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $content      = null;
            $content .= '<div class="horizontal-line filter-portlet-model-bar import-analysis-results-toolbar">';
            $element = new ImportAnalysisResultsFilterRadioElement($this->configurationForm, 'filteredByStatus', $form);
            $element->editableTemplate =  '<div id="ImportAnalysisResultsConfigurationForm_filteredByStatus_area">{content}</div>';
            $content .= $element->render();
            $content .= '</div>' . "\n";
            return $content;
        }

        protected function registerConfigurationFormLayoutScripts($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $uniquePageId= 'xxxx'; //todo: figure this out. since this is wrong and the update wont work. look at mashable since we need to trigger similarlly.
            $url       = Yii::app()->createUrl($this->getDefaultRoute());
            $urlScript = 'js:$.param.querystring("' . $url . '", "' .
                         $this->dataProvider->getPagination()->pageVar . '=1")'; // Not Coding Standard
            $ajaxSubmitScript = ZurmoHtml::ajax(array(
                    'type'       => 'GET',
                    'data'       => 'js:$("#' . $form->getId() . '").serialize()',
                    'url'        =>  $urlScript,
                    'update'     => '#' . $uniquePageId,
                    'beforeSend' => 'js:function(){$(this).makeSmallLoadingSpinner(true, "#' .
                                    $this->getGridViewId() . '"); $("#' .
                                    $form->getId() . '").parent().children(".cgrid-view").addClass("loading");}',
                    'complete'   => 'js:function()
                    {               $("#' . $form->getId() . '").parent().children(".cgrid-view").removeClass("loading");
                    }'
            ));
            Yii::app()->clientScript->registerScript($uniquePageId, "
            $('#ImportAnalysisResultsConfigurationForm_filteredByStatus_area').buttonset();
            $('#ImportAnalysisResultsConfigurationForm_filteredByStatus_area').change(function()
                {
                    " . $ajaxSubmitScript . "
                }
            );
            ");
        }
    }
?>