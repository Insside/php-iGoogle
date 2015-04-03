<?php

/**
 * Copyright (c) 2015, Jose Alexis Correa valencia
 * Except as otherwise noted, the content of this library  is licensed under the Creative Commons 
 * Attribution 3.0 License, and code samples are licensed under the Apache 2.0 License.
 * @link http://creativecommons.org/licenses/by/3.0/
 *
 * THE WORK (AS DEFINED BELOW) IS PROVIDED UNDER THE TERMS OF THIS CREATIVE COMMONS 
 * PUBLIC LICENSE ("CCPL" OR "LICENSE"). THE WORK IS PROTECTED BY COPYRIGHT AND/OR OTHER 
 * APPLICABLE LAW. ANY USE OF THE WORK OTHER THAN AS AUTHORIZED UNDER THIS LICENSE OR 
 * COPYRIGHT LAW IS PROHIBITED. BY EXERCISING ANY RIGHTS TO THE WORK PROVIDED HERE, 
 * YOU ACCEPT AND AGREE TO BE BOUND BY THE TERMS OF THIS LICENSE. TO THE EXTENT THIS 
 * LICENSE MAY BE CONSIDERED TO BE A CONTRACT, THE LICENSOR GRANTS YOU THE RIGHTS 
 * CONTAINED HERE IN CONSIDERATION OF YOUR ACCEPTANCE OF SUCH TERMS AND CONDITIONS.
 * @link http://creativecommons.org/licenses/by-nd/3.0/us/legalcode
 * 
 *  php - iCharts
 * Esta clase puede incrustar gráficos en una página web con Google Charts API. Puede generar 
 * HTML y JavaScript para realizar llamadas a la API de Google Charts para mostrar varios tipos de 
 * gráficos estadísticos. Actualmente soporta la incrustación de gráficos de tipo pastel, columna, 
 * área, línea, barras, burbujas, marcadores geográficos y caída libre.
 * @author Jose Alexis Correa Valencia <insside@facebook.com> 
 * @package iGoogle 
 * @see http://code.google.com/apis/chart/ 
 * @see http://code.google.com/apis/ajax/playground/?type=visualization 
 */
class iCharts {

  const jsapi = '<script src="https://www.google.com/jsapi" type="text/javascript"></script>\n';
  const eol = "\n"; // Salto de linea

  private static $libreria = false;

  /**
   * obtener la biblioteca JS que necesitamos para generar gráficos
   * @param boolean $force forces the inclusion 
   * @return string 
   */
  public static function include_library($forzar = false) {
    if (self::$libreria == false OR $forzar == true) {
      self::$libreria = true;
      return(self::jsapi);
    }
    return(false);
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
    $ret.='<script type="text/javascript">' . self::eol;

    //depending on the chart type load different vars 
    switch ($chart_type) {
      case 'GeoChart':
        $ret.='google.load("visualization", "1", {packages:["geochart"]});' . self::eol;
        $ret.='google.setOnLoadCallback(drawMarkersMap);' . self::eol;
        $ret.='function drawMarkersMap() {' . self::eol;
        break;
      case 'Gauge':
        $ret.='google.load("visualization", "1", {packages:["gauge"]});' . self::eol;
        $ret.='google.setOnLoadCallback(drawChart);' . self::eol;
        $ret.='function drawChart() {' . self::eol;
        break;
      default:
        $ret.='google.load("visualization", "1", {packages:["corechart"]});' . self::eol;
        $ret.='google.setOnLoadCallback(drawChart);' . self::eol;
        $ret.='function drawChart() {' . self::eol;
    }

    $ret.='var data = new google.visualization.DataTable();' . self::eol;
    //chart columns 
    foreach ($columns as $k => $v) {
      $ret.="data.addColumn('" . $v . "', '" . $k . "');" . self::eol;
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
    $ret.=']);' . self::eol;

    //adding the options 
    $ret.='var options = {';
    foreach ($options as $k => $v) {
      $ret.= $k . ': ';
      $ret.= (strpos($v, '{') !== FALSE OR is_numeric($k)) ? $v : '\'' . $v . '\'';
      $ret.= ',';
    }
    $ret.='};' . self::eol;

    //draw the chart 
    $ret.='var chart = new google.visualization.' . $chart_type . '(document.getElementById(\'' . $chart_div . '\'));' . self::eol;
    $ret.='chart.draw(data, options);' . self::eol;
    $ret.='}</script>' . self::eol . '<div id="' . $chart_div . '"></div>' . self::eol;

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
