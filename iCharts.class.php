<?php

/*
 * Copyright (c) 2015, Alexis
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Description of Charts
 *
 * @author Jose Alexis Correa Valencia <insside@facebook.com> 
 * @package iGoogle 
 * @see http://code.google.com/apis/chart/ 
 * @see http://code.google.com/apis/ajax/playground/?type=visualization 
 */
class iCharts {

  /**
   * 
   * Used to know if we already used the google js api somewhere so we don't call it twice 
   * @var boolean 
   */
  private static $included_lib = FALSE;

  /**
   * 
   * get the JS library that we need to generate charts 
   * @param boolean $force forces the inclusion 
   * @return string 
   */
  public static function include_library($force = FALSE) {
    if (self::$included_lib == FALSE OR $force == TRUE) {
      self::$included_lib = TRUE;
      return '<script src="https://www.google.com/jsapi" type="text/javascript"></script>' . PHP_EOL;
    }

    return FALSE;
  }

  /**
   * 
   * generates a google chart, with the given information 
   * @param string $chart_type 
   * @param array $data 
   * @param array $options 
   * @return mixed boolean|string 
   */
  public static function corechart($chart_type = 'ColumnChart', $data, $options = NULL) {
    //list of availables charts 
    $corecharts = array('ColumnChart', 'AreaChart', 'LineChart', 'BarChart', 'BubbleChart', 'PieChart', 'GeoChart', 'Gauge');

    if (!in_array($chart_type, $corecharts) OR ! is_array($data))
      return FALSE;

    //Defaults in case options are not set 
    if ($options == NULL) {
      $options['title'] = 'Title here';
      $options['height'] = 600;
      $options['width'] = 600;
    }

    //getting the columns for the data 
    $columns = array();
    foreach (reset($data) as $k => $v) {
      $columns[$k] = (is_numeric($v)) ? 'number' : 'string';
    }

    //name for the div where the chart appears 
    $chart_div = $chart_type . '_' . md5(uniqid(mt_rand(), false));

    //Start chart JS generation 
    $ret = '';
    $ret.=self::include_library();
    $ret.='<script type="text/javascript">' . PHP_EOL;

    //depending on the chart type load different vars 
    switch ($chart_type) {
      case 'GeoChart':
        $ret.='google.load("visualization", "1", {packages:["geochart"]});' . PHP_EOL;
        $ret.='google.setOnLoadCallback(drawMarkersMap);' . PHP_EOL;
        $ret.='function drawMarkersMap() {' . PHP_EOL;
        break;
      case 'Gauge':
        $ret.='google.load("visualization", "1", {packages:["gauge"]});' . PHP_EOL;
        $ret.='google.setOnLoadCallback(drawChart);' . PHP_EOL;
        $ret.='function drawChart() {' . PHP_EOL;
        break;
      default:
        $ret.='google.load("visualization", "1", {packages:["corechart"]});' . PHP_EOL;
        $ret.='google.setOnLoadCallback(drawChart);' . PHP_EOL;
        $ret.='function drawChart() {' . PHP_EOL;
    }

    $ret.='var data = new google.visualization.DataTable();' . PHP_EOL;
    //chart columns 
    foreach ($columns as $k => $v) {
      $ret.="data.addColumn('" . $v . "', '" . $k . "');" . PHP_EOL;
    }

    //adding data to the chart 
    $ret.="data.addRows([";
    foreach ($data as $d) {
      $ret.='[';
      foreach ($d as $k => $v) {
        $ret.= (is_numeric($v)) ? $v . ',' : "'" . $v . "',";
      }
      $ret.='],';
    }
    $ret.=']);' . PHP_EOL;

    //adding the options 
    $ret.='var options = {';
    foreach ($options as $k => $v) {
      $ret.= $k . ': ';
      $ret.= (strpos($v, '{') !== FALSE OR is_numeric($k)) ? $v : '\'' . $v . '\'';
      $ret.= ',';
    }
    $ret.='};' . PHP_EOL;

    //draw the chart 
    $ret.='var chart = new google.visualization.' . $chart_type . '(document.getElementById(\'' . $chart_div . '\'));' . PHP_EOL;
    $ret.='chart.draw(data, options);' . PHP_EOL;
    $ret.='}</script>' . PHP_EOL . '<div id="' . $chart_div . '"></div>' . PHP_EOL;

    return $ret;
  }

  /**
   * 
   * Wrappers for self::corechart 
   * 
   * usage example common for all of them: 
   * <?=Chart::pie($products,array('title'=>'Productos','width'=>700,'height'=>600))?> 
   * 
   * @param array $data 
   * @param array $options 
   * @return mixed boolean|string 
   */
  //http://code.google.com/apis/chart/interactive/docs/gallery/piechart.html 
  public static function pie($data, $options = NULL, $is3D = TRUE) {
    if ($is3D == TRUE) {
      $options+=array('is3D' => 'true');
    }

    return self::corechart('PieChart', $data, $options);
  }

  //http://code.google.com/apis/chart/interactive/docs/gallery/columnchart.html 
  public static function column($data, $options = NULL) {
    return self::corechart('ColumnChart', $data, $options);
  }

  //http://code.google.com/apis/chart/interactive/docs/gallery/areachart.html 
  public static function area($data, $options = NULL) {
    return self::corechart('AreaChart', $data, $options);
  }

  //http://code.google.com/apis/chart/interactive/docs/gallery/linechart.html 
  public static function line($data, $options = NULL) {
    return self::corechart('LineChart', $data, $options);
  }

  //http://code.google.com/apis/chart/interactive/docs/gallery/barchart.html 
  public static function bar($data, $options = NULL) {
    return self::corechart('BarChart', $data, $options);
  }

  //http://code.google.com/apis/chart/interactive/docs/gallery/bubblechart.html 
  public static function bubble($data, $options = NULL) {
    return self::corechart('BubbleChart', $data, $options);
  }

  //http://code.google.com/apis/chart/interactive/docs/gallery/geochart.html 
  public static function geomarkers($data, $options = NULL, $region = 'ES') {
    $options+=array('region' => $region,
        'displayMode' => 'markers',
        'colorAxis' => "{colors: ['green', 'blue']}");

    return self::corechart('GeoChart', $data, $options);
  }

  //http://code.google.com/apis/chart/interactive/docs/gallery/gauge.html 
  public static function gauge($data, $options = NULL) {
    $options+=array('redFrom' => 90,
        'redTo' => 100,
        'yellowFrom' => 75,
        'yellowTo' => 90,
        'minorTicks' => 5);
    return self::corechart('Gauge', $data, $options);
  }

}
