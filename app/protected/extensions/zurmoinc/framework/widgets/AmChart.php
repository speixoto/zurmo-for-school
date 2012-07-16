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
     * Render a fusion chart that can be formatted.
     */
    class AmChart extends ZurmoWidget
    {
        public $scriptFile = 'amcharts.js';

        public $assetFolderName = 'amChart';

        public $type          = 'column';

        public $data       = null;
      
        public $height        = 300;

        /**
         * Initializes the widget.
         * This method will publish JUI assets if necessary.
         * It will also register jquery and JUI JavaScript files and the theme CSS file.
         * If you override this method, make sure you call the parent implementation first.
         */
        public function init()
        {
            /*
             * What are this assert for?????
             */
            assert('!empty($this->type)');
            assert('!empty($this->dataUrl)');
            assert('$this->dataFormat == "URIData" || $this->dataFormat == "XMLData"');
            assert('is_int($this->height) && $this->height > 0');
            parent::init();
        }

        public function run()
        {
            $id = $this->getId();                      
            $dataString = CJavaScript::encode($this->data);                                  
            $javascript = "
                    var chartData = {$dataString}
                                        
                    AmCharts.ready(function () {
                        // PIE CHART
                    
                     // SERIAL CHART
                var chart = new AmCharts.AmSerialChart();
                chart.dataProvider = chartData;
                chart.categoryField = 'displayLabel';
                chart.startDuration = 2;
                // change balloon text color                
                chart.balloon.color = '#000000';

                // AXES
                // category
                var categoryAxis = chart.categoryAxis;
                categoryAxis.gridAlpha = 0;
                categoryAxis.axisAlpha = 0;
                categoryAxis.labelsEnabled = false;

                // value
                var valueAxis = new AmCharts.ValueAxis();
                valueAxis.gridAlpha = 0;
                valueAxis.axisAlpha = 0;
                valueAxis.labelsEnabled = false;
                valueAxis.minimum = 0;
                chart.addValueAxis(valueAxis);

                // GRAPH
                var graph = new AmCharts.AmGraph();
                graph.balloonText = '[[category]]: [[value]]';
                graph.valueField = 'value';                
                graph.type = 'column';
                graph.lineAlpha = 0;
                graph.fillAlphas = 1;
                graph.fillColors = ['#ffe78e', '#bf1c25'];
                graph.labelText = '[[description]]';
                graph.balloonText = '[[category]]: [[value]]';
                chart.addGraph(graph);
                    
                    
                    
                        // WRITE
                        chart.write('chartContainer{$id}');
                    });
            ";
    Yii::app()->getClientScript()->registerScript('chart' . $id,$javascript);
    echo "<div id='chartContainer{$id}' style='width: 100%; height: 400px;'></div>";                                                                                                       
            /*
            $id = $this->getId();
            $options = array(
                'swfPath'     => $this->scriptUrl . '/charts/',
                'type'        => $this->type,
                'data'        => $this->dataUrl,
                'dataFormat'  => $this->dataFormat,
                'width'       => "js:$(\"#chartContainer{$id}\").width() - 10",
                'height'      => $this->height,
                //wMode ensures the chart is behind the modal dialogs
                'wMode'       => 'transparent',
            );
            $javaScript  = "$(document).ready(function () { ";
            $javaScript .= "$('#chartContainer{$id}').insertFusionCharts( ";
            $javaScript .= CJavaScript::encode($options);
            $javaScript .= ");";
            $javaScript .= "});";
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, $javaScript);
            echo '<div id = "chartContainer' . $id . '"></div>';            
            */
        }
    }
?>