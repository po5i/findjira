<?

//test
require_once("../service_itunes.php");

set_time_limit(0);


$ob_tubo = new service_itunes();

	$ob_tubo->set_query("naruto");
	
	$ob_tubo->audio_search();
	
	$ob_tubo->test();
	
//http://odeo.com/audio/3984483/json

?>