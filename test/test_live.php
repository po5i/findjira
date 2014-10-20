<?

//test
require_once("../service_live.php");


$ob_tubo = new service_live();

	$ob_tubo->set_query("naruto");
	
	$ob_tubo->set_resultados_por_pagina (10);

	$ob_tubo->image_search();
	
	$ob_tubo->test();
	
?>