<?php

require_once("../service_yahoo_maps.php");

$map = new YahooMap();

echo "<head>";
$map->printJS();
echo "</head>";
echo "<body>";

$map->setAPIkey("IrREloHV34GYdu3Z26K5P4mXhqeNC8_uPKg.21zghRSTe8Fe2qH.HSOrMdpSLf1PBgs-");
//$map->setGeocodeAPIkey("IrREloHV34GYdu3Z26K5P4mXhqeNC8_uPKg.21zghRSTe8Fe2qH.HSOrMdpSLf1PBgs-");
$map->setGeocodeAPIkey("YahooDemo"); //esto esta fallando??

$map->addAddress("Guayaquil");
$map->addAddress("Quito");
$map->addAddress("Cuenca");
$map->centerMap(0,0);

$map->setWidth("400");
$map->setHeight("400");

$map->showMap();




echo "</body>";

?>