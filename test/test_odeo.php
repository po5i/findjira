<?

//test
require_once("../service_odeo.php");

set_time_limit(0);


$ob_tubo = new service_odeo();

	$ob_tubo->set_query("naruto");
	
	$ob_tubo->set_resultados_por_pagina(15);

	$ob_tubo->audio_search();
	
	$ob_tubo->test();
	
//http://odeo.com/audio/3984483/json

?>