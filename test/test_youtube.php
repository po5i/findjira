<?
//prueba de sincronizacion
//test
require_once("../service_youtube.php");


$ob_tubo = new service_youtube();

	$ob_tubo->set_query("rockman commercial");
	
	//$ob_tubo->set_resultados_por_pagina (3);

	$ob_tubo->video_search();
		
	$ob_tubo->test();
	
?>
