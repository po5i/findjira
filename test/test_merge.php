<?

//test
require_once("../service_searchmash.php");
require_once("../service_yahoo.php");
require_once("../service_live.php");
require_once("../repositorio.php");
require_once("../service_odeo.php");
require_once("../service_itunes.php");


set_time_limit(0);

/*$ob_tubo = new service_searchmash();
$ob_tubo->set_query("naruto");		
$ob_tubo->set_resultados_por_pagina(5);	
$ob_tubo->web_search();	

$ob_tubo2 = new service_yahoo();
$ob_tubo2->set_query("naruto");	
$ob_tubo2->set_resultados_por_pagina(5);
$ob_tubo2->web_search();

$ob_tubo3 = new service_live();
$ob_tubo3->set_query("naruto");	
$ob_tubo3->set_resultados_por_pagina(5);
$ob_tubo3->web_search();
*/

$ob_tubo = new service_odeo();
$ob_tubo->set_query("naruto");
$ob_tubo->set_resultados_por_pagina(2);
$ob_tubo->audio_search();	

$ob_tubo2 = new service_itunes();
$ob_tubo2->set_query("naruto");
$ob_tubo2->audio_search();

/*******************/

$ob_repositorio = new repositorio();	
$ob_repositorio->insertar_batch($ob_tubo->get_respuesta_findjira());	
$ob_repositorio->insertar_batch($ob_tubo2->get_respuesta_findjira());	
//$ob_repositorio->insertar_batch($ob_tubo3->get_respuesta_findjira());


echo "<hr><hr><hr><pre>";
//var_dump($ob_repositorio->extraer_batch(3,6));					//rango
var_dump($ob_repositorio->extraer_elementos());		//todos
echo "</pre>";

$xml = generar_xspf($ob_repositorio->extraer_elementos());
echo nl2br(htmlentities($xml));

function generar_xspf($elementos)
{
	$xspf = "
	<?xml version=\"1.0\" encoding=\"UTF-8\"?>
	<playlist version=\"1\" xmlns=\"http://xspf.org/ns/0/\">
		<trackList>
	";

	foreach($elementos as &$elemento)
	{
		
		$xspf .= "
					<track>
						<title>".$elemento["titulo"]."</title>
						<image>".$elemento["thumb_url"]."</image>
						<location>".$elemento["download_url"]."</location>
					</track>
		";			
	}
	
	$xspf .= "
		</trackList>
	</playlist>
	";
	
	return $xspf;
}
	
?>