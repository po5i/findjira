<?php

require_once("../service_google_maps.php");

$map = new GoogleMap();

echo "<head>";
$map->printJS();
echo "</head>";
echo "<body>";
//pagina para sacar keys porsiaca
//http://www.google.com/apis/maps/signup.html

$map->setAPIkey("ABQIAAAAS92FhP9_3QBHPMGXCjiYERT2yXp_ZAY8_ufC3CFXhHIE1NvwkxSERkUmS3n_pB-rqXP--pub0Sko9g");

$map->addAddress("Guayaquil");
$map->addAddress("Quito");
$map->addAddress("Cuenca");
$map->centerMap(0,0);


$map->setWidth("400");
$map->setHeight("400");

$map->showMap();

echo "</body>";



?>