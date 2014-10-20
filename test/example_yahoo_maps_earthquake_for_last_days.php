<html><head>
<script type="text/javascript" src="http://api.maps.yahoo.com/ajaxymap?v=3.0&appid=rlerdorf"></script>
<?php
include '/usr/local/lib/php/simple_rss.php';

$url = 'http://earthquake.usgs.gov/eqcenter/recenteqsww/catalogs/eqs7day-M2.5.xml';
$feed = rss_request($url, $timeout=3600);
echo <<<EOB
<title>{$feed['title'][0]}</title>
</head><body>
<h1>{$feed['title'][0]}</h1>
<p><font size="+2">{$feed['description'][0]}<br />
{$feed['pubDate'][0]}</font></p>
EOB;
?>
<div id="mapContainer" style="height: 600px; width: 800px;"></div>
<script type="text/javascript">
var latlon = new YGeoPoint(37.416384, -122.024853); 
var mymap = new  YMap(document.getElementById('mapContainer'));
var marker = new Array();
mymap.addPanControl();
mymap.addZoomLong();
mymap.drawZoomAndCenter(latlon, 14);

<?php 
  $i = 0;
  while(!empty($feed[$i])) {
    $info = $feed[$i]['title'][0]."<br />";
    $info .= $feed[$i]['description'][0]."<br />";
    $info .= '<a href="'.$feed[$i]['link'][0].'">more info</a>';
    $info = addslashes($info);
?>  
marker[<?php echo $i?>] = new YMarker(new YGeoPoint(<?php echo $feed[$i]['lat'][0].','.$feed[$i]['long'][0]?>)); 
marker[<?php echo $i?>].addLabel("<b><?php echo $i?></b>"); 
YEvent.Capture(marker[<?php echo $i?>], EventsList.MouseClick, new Function("marker[<?php echo $i?>].openSmartWindow('<?php echo $info?>');")); 
mymap.addOverlay(marker[<?php echo $i?>]);
<?php
    $i++;
  }
?>
</script> 
<a href="http://developer.yahoo.net/about"> <img src="http://us.dev1.yimg.com/us.yimg.com/i/us/nt/bdg/websrv_120_1.gif" border="0"></a>
&nbsp;&nbsp;&nbsp;<a href="/php/ymap/dquakes.phps">[Source Code]</a>
</body></html>