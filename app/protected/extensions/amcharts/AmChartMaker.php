<?php
class AmChartMaker
{
    public  $type            = 'Column3D';
    public  $data            = null;   
    public  $height          = 300;                        
    public  $valueField      = 'value';        
    public  $categoryField   = 'displayLabel';        
    public  $chartIs3d       = false;            
    public  $chartIsPie      = false;        
    public  $xAxisName       = null;        
    public  $yAxisName       = null;        
    public  $chartTitle      = null;  
    
    private $serial          = array();
    private $chartProperties = array();
    /*
    * Returns the type of chart to be used in AmChar
    */
    private function addChartPropertiesByType() 
    {
        if ($this->type === "Column2D")
        {
        }
        elseif ($this->type === "Column3D")
        {   
            $this->makeChart3d();
        }
        elseif ($this->type === "Bar2D")
        {                    
            $this->addChartProperties('rotate', true);
        } 
        elseif ($this->type === "Donut2D")
        {
            $this->addChartProperties('sequencedAnimation', true);
            $this->addChartProperties('startEffect', "'elastic'");
            $this->addChartProperties('innerRadius', "'30%'");
            $this->addChartProperties('startDuration', 2);
            $this->addChartProperties('labelRadius', 15);
            $this->chartIsPie = true;
        } 
        elseif ($this->type === "Pie2D")
        {
            $this->addChartProperties('outlineColor', "'#FFFFFF'");
            $this->addChartProperties('outlineAlpha', 0.8);
            $this->addChartProperties('outlineThickness', 2); 
            $this->chartIsPie = true;
        }            
        elseif ($this->type === "Pie3D")
        {
            $this->addChartProperties('outlineColor', "'#FFFFFF'");
            $this->addChartProperties('outlineAlpha', 0.8);
            $this->addChartProperties('outlineThickness', 2); 
            $this->makeChart3d();
            $this->chartIsPie = true;
        }
        else
        {   
        }        
    }    
    private function convertDataArrayToJavascriptArray()
    {
        return CJavaScript::encode($this->data);                                           
    }
    /*
     * Add serial to Serial Chart
     * $valuefield: string 
     * $type: string (column, line)
     */
    public function makeChart3d()
    {
        $this->addChartProperties('depth3D', 15);
        $this->addChartProperties('angle', 30);
        $this->chartIs3d = true;
    }
    public function addSerial($valueField, $type)
    {
        array_push($this->serial, array(
                                'valueField'    =>  $valueField,
                                'type'          =>  $type,
                             )
        );
    }
    /*
     * Add properties to chart
     */
    public function addChartProperties($tag, $value)
    {
        array_push($this->chartProperties, array(
                                'tag'           =>  $tag,
                                'value'         =>  $value,
                             )
                
        );
    }
    public function JavascriptChart()
    {
        //Init AmCharts
        $this->addChartPropertiesByType();
        $javascript = "var chartData = ". $this->convertDataArrayToJavascriptArray() . ";";
        $javascript .=" \n AmCharts.ready(function () {     ";
        //Make chart Pie or Serial
        if ($this->chartIsPie)
        {
            $javascript .="
               var chart = new AmCharts.AmPieChart();
                // title of the chart
                chart.addTitle('Title', 16);
                chart.dataProvider = chartData;
                chart.titleField = '{$this->categoryField}';
                chart.valueField = '". $this->valueField . "';";
        }
        else
        {
            //Init the AmSerialGraph
            $javascript .="
                    var chart = new AmCharts.AmSerialChart();
                    chart.dataProvider = chartData;
                    chart.categoryField = '{$this->categoryField}';
            ";            
        }
        //Add chart properties       
        foreach ($this->chartProperties as $chartProperty)
        {
            $javascript .= "chart." . $chartProperty['tag'] . " = " . $chartProperty['value'] . ";";
        }
        
        if (!$this->chartIsPie)
        {
            //Add serial infor as graph            
            foreach ($this->serial as $key => $serial)
            {
                $javascript .= "var graph{$key} = new AmCharts.AmGraph();
                    graph{$key}.valueField = '". $serial['valueField'] ."';
                    graph{$key}.type = '" . $serial['type'] .  "';
                    graph{$key}.lineAlpha = 0;
                    graph{$key}.fillAlphas = 0.8;
                    chart.addGraph(graph{$key});";
                //Add graph properties
            }
                   
        }
        //Write chart       
        $javascript .= "chart.write('chartContainer1111');
                 });";
        return $javascript;   
    }             
}
?>