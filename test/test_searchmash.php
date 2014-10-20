<?

//test
require_once("../service_searchmash.php");

set_time_limit(0);



$ob_tubo = new service_searchmash();

	$ob_tubo->set_query("panda sneezes");
	

	//$ob_tubo->set_intitle("ganondorf");
	
	$ob_tubo->set_resultados_por_pagina(10);

	//$ob_tubo->web_search();

	//$ob_tubo->image_search();
	
	//$ob_tubo->blog_search();
	
	$ob_tubo->video_search();
	
	//$ob_tubo->wiki_search();
	
	//$ob_tubo->set_query("madonna mp3");
	//$ob_tubo->indexof_search();
	
	
	
	$ob_tubo->test();
	
	
	
?>