<?php
class AmChartMaker
{
    public $type            = 'Column3D';
    public $data            = null;   
    public $height          = 300;                        
    public $valueField      = 'value';        
    public $categoryField   = 'displayLabel';        
    public $chartIs3d       = false;        
    public $chartRotate     = false;        
    public $chartIsPie      = false;        
    public $xAxisName       = null;        
    public $yAxisName       = null;        
    public $chartTitle      = null;  
    /*
    * Returns the type of chart to be used in AmChar
    */
    private function getChartPropertiesByType() 
    {
        if ($this->type === "Column2D")
        {
            $graph = "
                var graph = new AmCharts.AmGraph();
                graph.valueField = 'value';
                graph.balloonText = '[[category]]: [[value]]';
                graph.type = 'column';
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.8;
                chart.addGraph(graph);   ";   
        }
        elseif ($this->type === "Column3D")
        {
            $graph = "
            var graph = new AmCharts.AmGraph();
            graph.valueField = '" . $this->valueField . "';
            graph.balloonText = '[[category]]: [[value]]';
            graph.type = 'column';
            graph.lineAlpha = 0;
            graph.fillAlphas = 1;
            chart.addGraph(graph);  ";
            $this->chartIs3d = true;
        }
        elseif ($this->type === "Bar2D")
        {
            $graph = "
            var graph = new AmCharts.AmGraph();
            graph.valueField = '" . $this->valueField . "';
            graph.colorField = 'color';
            graph.balloonText = '[[category]]: [[value]]';
            graph.type = 'column';
            graph.lineAlpha = 0;
            graph.fillAlphas = 1;
            chart.addGraph(graph);  ";
            $this->chartRotate = true;
        } 
        elseif ($this->type === "Donut2D")
        {
            $graph = "chart.sequencedAnimation = true;
            chart.startEffect = 'elastic';
            chart.innerRadius = '30%';
            chart.startDuration = 2;
            chart.labelRadius = 15;
            ";
            $this->chartIsPie = true;
        } 
        elseif ($this->type === "Pie2D")
        {
            $graph = "
            chart.outlineColor = '#FFFFFF';
            chart.outlineAlpha = 0.8;
            chart.outlineThickness = 2; ";
            $this->chartIsPie = true;
        }            
        elseif ($this->type === "Pie3D")
        {
            $graph = "
            chart.outlineColor = '#FFFFFF';
            chart.outlineAlpha = 0.8;
            chart.outlineThickness = 2; ";
            $this->chartIs3d = true;
            $this->chartIsPie = true;
        }
        else
        {
            $graph = "
            var graph = new AmCharts.AmGraph();
            graph.valueField = '" . $this->valueField . "';
            graph.colorField = 'color';
            graph.balloonText = '[[category]]: [[value]]';
            graph.type = 'column';
            graph.lineAlpha = 0;
            graph.fillAlphas = 1;
            chart.addGraph(graph);  ";               
        }
        return $graph;
    }    
    private function convertDataArrayToJavascriptArray()
    {
        return CJavaScript::encode($this->data);                                           
    }
    public function printJavascriptChart()
    {
        $graph = $this->getChartPropertiesByType();
            $javascript = "var chartData = ". $this->convertDataArrayToJavascriptArray();
            $javascript .=" \n AmCharts.ready(function () {     ";
           
            if ($this->chartIsPie)
            {
                $javascript .="
                chart = new AmCharts.AmPieChart();
                // title of the chart
                chart.addTitle('Title', 16);
                chart.dataProvider = chartData;
                chart.titleField = 'displayLabel';
                chart.valueField = '". $this->valueField . "';
                " . $graph;

            }
            else
            {
            
                $javascript .="
                    var chart = new AmCharts.AmSerialChart();
                    chart.dataProvider = chartData;
                    chart.categoryField = 'displayLabel';
                    chart.startDuration = 2;";
                              
                if ($this->chartRotate){
                    $javascript .= "chart.rotate = ". $this->chartRotate;
                }
                
            
           
            
            
                $javascript .="
                               
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
                    {$graph}";
            }
            if ($this->chartIs3d) 
            { 
                $javascript .= "chart.depth3D = 20;chart.angle = 30;";             
            }
                    $javascript .="
                    
                    
                    
                        // WRITE
                        chart.write('chartContainer1111');
                    });
            ";    
            return $javascript;
    }
}
?>