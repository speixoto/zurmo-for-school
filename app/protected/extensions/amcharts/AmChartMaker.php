<?php
//TODO: Error using false and some string in adding atributes
class AmChartMaker
{
    public  $type                = null;
    public  $data                = null;
    public  $height              = 300;
    public  $valueField          = 'value';
    public  $categoryField       = 'displayLabel';
    public  $chartIs3d           = false;
    public  $chartIsPie          = false;
    public  $xAxisName           = null;
    public  $yAxisName           = null;

    private $serial              = array();
    private $chartProperties     = array();
    private $graphProperties     = array();
    private $valueAxisProperties = array();
    private $categoryAxisProperties = array();

    /**
     * Returns the type of chart to be used in AmChar
     */
    private function addChartPropertiesByType()
    {
        $this->addChartProperties('fontFamily', '"Arial"');
        $this->addChartProperties('color', '"#545454"');
        $colorTheme = array(
                        1 => '["#262877", "#6625A7", "#BC9DDA", "#817149", "#A77425"]',
                        2 => '["#262877", "#7BB730"]',
                        3 => '["#262877", "#3E44C3", "#585A8E", "#777AC1", "#151741", "#7BB730"]',
                        4 => '["#262877", "#121337", "#3E42C3", "#3E44C3", "#1E205D", "#7BB730"]',
            );
        $this->addChartProperties('colors', $colorTheme[4]);

        if ($this->type === "Column2D")
        {
            $currencySymbol = Yii::app()->locale->getCurrencySymbol(Yii::app()->currencyHelper->getCodeForCurrentUserForDisplay());
            /**
             * Chart properties
             * More info on http://www.amcharts.com/docs/v.2/javascript_reference/amchart
             */
            $this->addChartProperties('usePrefixes', true);
            $this->addChartProperties('autoMargins', 'false');
            $this->addChartProperties('marginRight', 50);
            $this->addChartProperties('marginLeft', 100);
            $this->addChartProperties('marginBottom', 50);
            $this->addChartProperties('marginTop', 50);
            $this->addChartProperties('plotAreaBorderColor', "'#000000'");
            $this->addChartProperties('plotAreaBorderAlpha', 1);
            /**
             * Columns or bar properties
             * More info on http://www.amcharts.com/docs/v.2/javascript_reference/amgraph
             */
            $this->addGraphProperties('fillAlphas', 0.8);
            $this->addGraphProperties('cornerRadiusTop', 8);
            $this->addGraphProperties('lineAlpha', 0);
            $this->addGraphProperties('lineAlpha', 0);
            /**
             * categoryAxis properties
             * More info on http://www.amcharts.com/docs/v.2/javascript_reference/axisbase
             */
            $this->addCategoryAxisProperties('title', "'$this->xAxisName'");
            $this->addCategoryAxisProperties('inside', 0);
            $this->addCategoryAxisProperties('axisAlpha', 0);
            $this->addCategoryAxisProperties('gridAlpha', 0);
            $this->addCategoryAxisProperties('fillColors', '["#000000", "#FF6600"]');
            /**
             * valueAxis properties
             * More info on http://www.amcharts.com/docs/v.2/javascript_reference/axisbase
             */
            $this->addValueAxisProperties('title', "'$this->yAxisName'");
            $this->addValueAxisProperties('minimum', 0);
            $this->addValueAxisProperties('axisAlpha', 0);
            $this->addValueAxisProperties('dashLength', 4);
            $this->addValueAxisProperties('usePrefixes', 1);
            $this->addValueAxisProperties('unitPosition', '"left"');
            $this->addValueAxisProperties('unit', "'$currencySymbol'");
        }
        elseif ($this->type === "Column3D")
        {
            /**
             * Columns or bar properties
             * More info on http://www.amcharts.com/docs/v.2/javascript_reference/amgraph
             */
            $this->addGraphProperties('balloonText', "'[[category]]:[[value]]'");
            $this->addGraphProperties('lineAlpha', 8);
            $this->addGraphProperties('fillColors', "'#bf1c25'");
            $this->addGraphProperties('fillAlphas', 1);
            //Make the graph3d - to chage defs go to method
            $this->makeChart3d();
        }
        elseif ($this->type === "Bar2D")
        {
            /**
             * Chart properties
             * More info on http://www.amcharts.com/docs/v.2/javascript_reference/amchart
             */
            $this->addChartProperties('rotate', true);
            $this->addChartProperties('usePrefixes', true);
            /**
             * Columns or bar properties
             * More info on http://www.amcharts.com/docs/v.2/javascript_reference/amgraph
             */
            $this->addGraphProperties('lineAlpha', 0);
            $this->addGraphProperties('fillAlphas', 0.5);
            $this->addGraphProperties('fillColors', '["#000000", "#FF6600"]');
            $this->addGraphProperties('gradientOrientation', '"vertical"'); //Possible values are "vertical" and "horizontal".
            $this->addGraphProperties('labelPosition', '"inside"'); //Possible values are: "bottom", "top", "right", "left", "inside", "middle".
            $this->addGraphProperties('labelText', '"[[category]]: [[value]]"'); //You can use tags like [[value]], [[description]], [[percents]], [[open]], [[category]]
            $this->addGraphProperties('balloonText', '"[[category]]: [[value]]"');
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

    public function makeChart3d()
    {
        $this->addChartProperties('depth3D', 15);
        $this->addChartProperties('angle', 30);
        $this->chartIs3d = true;
    }

    /**
     * Add Serial Graph to SerialChart
     * $valuefield: string
     * $type: string (column, line)
     */
    public function addSerialGraph($valueField, $type, $options = array())
    {
        array_push($this->serial, array(
                                'valueField'    =>  $valueField,
                                'type'          =>  $type,
                                'options'       =>  $options,
                             )
        );
    }

    /**
     *  Add properties to chart
     */
    public function addChartProperties($tag, $value)
    {
        $this->chartProperties[$tag] = $value;
    }

    /**
     * Add properties to valueAxis
     */
    public function addValueAxisProperties($tag, $value)
    {
        $this->valueAxisProperties[$tag] = $value;
    }

    /**
     * Add properties to categoryAxis
     */
    public function addCategoryAxisProperties($tag, $value)
    {
        $this->categoryAxisProperties[$tag] = $value;
    }
    /**
     * Add properties to Serial Graph - column or bar properties
     */
    public function addGraphProperties($tag, $value)
    {
       $this->graphProperties[$tag] = $value;
    }
    public function javascriptChart()
    {
        //Init AmCharts
        $this->addChartPropertiesByType();
        $javascript = "var chartData = ". $this->convertDataArrayToJavascriptArray() . ";";
        $javascript .=" $(document).ready(function () {     ";
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
        foreach ($this->chartProperties as $tag => $chartProperty)
        {
            $javascript .= "chart." . $tag . " = " . $chartProperty . ";";
        }

        if (!$this->chartIsPie)
        {
            //Add serial as graph
            foreach ($this->serial as $key => $serial)
            {
                $javascript .= "var graph{$key} = new AmCharts.AmGraph();
                    graph{$key}.valueField = '". $serial['valueField'] ."';
                    graph{$key}.type = '" . $serial['type'] .  "';";
                if(count($serial['options']) === 0)
                {
                    //Add graph properties from GraphType
                    foreach($this->graphProperties as $graphTag => $graphOption)
                    {
                        $javascript .= "graph{$key}." . $graphTag . " = " . $graphOption . ";";
                    }
                }
                else
                {
                    //Add graph properties from option passed
                    foreach($serial['options'] as $graphTag => $graphOption)
                    {
                        $javascript .= "graph{$key}." . $graphTag . " = " . $graphOption . ";";
                    }
                }
                $javascript .= "chart.addGraph(graph{$key});";
            }
            //Add categoryAxis properties from GraphType
            $javascript .= "var categoryAxis = chart.categoryAxis;";
            foreach($this->categoryAxisProperties as $tag => $option)
            {
                $javascript .= "categoryAxis." . $tag . " = " . $option . ";";
            }
            //Add valueAxis properties from GraphType
            $javascript .= "var valueAxis = new AmCharts.ValueAxis();";
            foreach($this->valueAxisProperties as $tag => $option)
            {
                $javascript .= "valueAxis." . $tag . " = " . $option . ";";
            }
            $javascript .=   "chart.addValueAxis(valueAxis);";
        }
        //Write chart
        $javascript .= "chart.write('chartContainer{$this->id}');
                 });";
        return $javascript;
    }
}
?>