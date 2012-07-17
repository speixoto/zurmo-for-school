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
    
    private $serial          = array();
    private $chartProperties = array();
    /*
    * Returns the type of chart to be used in AmChar
    */
    private function addChartPropertiesByType() 
    {
        if ($this->type === "Column2D")
        {
            $this->addChartProperties('usePrefixes', true);
        }
        elseif ($this->type === "Column3D")
        {   
            $this->makeChart3d();
            $this->addChartProperties('usePrefixes', true);
        }
        elseif ($this->type === "Bar2D")
        {                    
            $this->addChartProperties('rotate', true);
            $this->addChartProperties('usePrefixes', true);
        } 
        elseif ($this->type === "Donut2D")
        {
            $this->addChartProperties('sequencedAnimation', true);
            $this->addChartProperties('startEffect', "'elastic'");
            $this->addChartProperties('innerRadius', "'30%'");
            $this->addChartProperties('startDuration', 2);
            $this->addChartProperties('labelRadius', 15);    
            $this->addChartProperties('usePrefixes', true);
            $this->chartIsPie = true;
        } 
        elseif ($this->type === "Pie2D")
        {
            $this->addChartProperties('outlineColor', "'#FFFFFF'");
            $this->addChartProperties('outlineAlpha', 0.8);
            $this->addChartProperties('outlineThickness', 2); 
            $this->addChartProperties('usePrefixes', true);
            $this->chartIsPie = true;
        }            
        elseif ($this->type === "Pie3D")
        {
            $this->addChartProperties('outlineColor', "'#FFFFFF'");
            $this->addChartProperties('outlineAlpha', 0.8);
            $this->addChartProperties('outlineThickness', 2); 
            $this->addChartProperties('usePrefixes', true);
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
    public function addSerial($valueField, $type, $options)
    {
        array_push($this->serial, array(
                                'valueField'    =>  $valueField,
                                'type'          =>  $type,
                                'options'       =>  $options,
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
            //Add serial as graph            
            foreach ($this->serial as $key => $serial)
            {
                $javascript .= "var graph{$key} = new AmCharts.AmGraph();
                    graph{$key}.valueField = '". $serial['valueField'] ."';
                    graph{$key}.type = '" . $serial['type'] .  "';";
                //Add graph properties
                foreach($serial['options'] as $graphTag => $graphOption)
                {
                    $javascript .= "graph{$key}." . $graphTag . " = " . $graphOption . ";";
                }                   
                $javascript .= "chart.addGraph(graph{$key});";                
            }
            //Add Axis
            $currencySymbol = Yii::app()->locale->getCurrencySymbol(Yii::app()->currencyHelper->getCodeForCurrentUserForDisplay());
            $javascript .= "
                // categoryAxis
                var categoryAxis = chart.categoryAxis;                
                categoryAxis.gridPosition = 'start';
                categoryAxis.title = '{$this->xAxisName}'

                // valueAxis
                var valueAxis = new AmCharts.ValueAxis();
                valueAxis.title = '{$this->yAxisName}';
                valueAxis.usePrefixes = true;
                valueAxis.unitPosition = 'left';
                valueAxis.unit = '{$currencySymbol}';
                valueAxis.minimum = 0;
                chart.addValueAxis(valueAxis);                


                ";
                   
        }
        //Write chart       
        $javascript .= "chart.write('chartContainer{$this->id}');
                 });";
        return $javascript;   
    }             
}
?>