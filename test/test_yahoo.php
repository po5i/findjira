<?

//test
require_once("../service_yahoo.php");

set_time_limit(0);


	
$ob_tubo = new service_yahoo();

	$ob_tubo->set_query("naruto");
	
	$ob_tubo->set_resultados_por_pagina(2);

	$ob_tubo->web_search();
	
	$ob_tubo->test();
	

/** prueba */

?>