<?

//test
require_once("../service_flickr.php");

set_time_limit(0);


$ob_tubo = new service_flickr();

	$ob_tubo->set_query("naruto");
	
	$ob_tubo->set_resultados_por_pagina(10);

	$ob_tubo->image_search();
	
	$ob_tubo->test();
	


?>