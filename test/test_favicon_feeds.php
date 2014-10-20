<?php

require_once("../service.php");

$ob_tubo = new service();

	
	var_dump($ob_tubo->obtener_favicon_url("http://feeds.weblogssl.com/genbeta?feed=atom"));
	
	var_dump($ob_tubo->obtener_feeds_autodiscovery("http://kuroizero.blogspot.com"))
	
	
	
	

?>