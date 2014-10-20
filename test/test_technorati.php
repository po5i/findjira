<?

//test
require_once("../service_technorati.php");


$ob_tubo = new service_technorati();

	$ob_tubo->set_query("naruto");
	
	$ob_tubo->set_resultados_por_pagina (10);

	$ob_tubo->blog_search();
	
	$ob_tubo->test();
	
?>