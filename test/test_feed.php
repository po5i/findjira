<?

//test
require_once("../service_feed.php");


$ob_tubo = new service_feed();

	$ob_tubo->set_feed_url("http://kuroizero.blogspot.com/feeds/posts/default");
	$ob_tubo->feed_retrieve();
	
	$ob_tubo->test();
	
	$ob_tubo->set_feed_url("http://www.eluniverso.com/rss/arteyespectaculos.xml");
	$ob_tubo->feed_retrieve();
	
	$ob_tubo->test();
	
?>