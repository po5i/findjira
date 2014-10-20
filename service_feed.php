<?
require_once ("service.php");
require_once ("feedsProcessingAPI/feedsProcessingAPI.php");
require_once ("class_xml2json.php");
require_once ("class_unicode.php");

/*
CLASE : service_feed
DETALLES: provee los servicios necesarios para leer un numero de posts de feeds ATOM y RSS, y los convierte a formato de repositorio FindJira
usa clase externa para manejo de feeds y reconocimiento de formatos
*/

class service_feed extends service 
{
	
	var $feed_title;//datos propios del xml del feed
	var $feed_link;//link principal de los rel del feed	
	var $feed_url; //para sacar la info, direccion del feed
	
	/*
	FUNCION : set_feed_url
	DETALLES: setea url del feed
	RECIBE: el url completo del feed
	RETORNA: n/a
	*/
	
	function set_feed_url($URL)
	{
		$this->feed_url = $URL;				
	}
	
	/*
	FUNCION : get_feed_url
	DETALLES: obtiene el url del feed
	RECIBE: n/a
	RETORNA: el url completo del feed
	*/
	
	function get_feed_url()
	{
		$out = $this->feed_url;		
		return $out;	
	}
	
	/*
	FUNCION : get_feed_info
	DETALLES: obtiene toda la informacion del feed
	RECIBE: n/a
	RETORNA: un arreglo serializado con las variables del objeto feed
	*/
	
	function get_feed_info()
	{
		$out[] = array (					    			
					    			"feed_title"	=> $this->feed_title,
					    			"feed_link"		=> $this->feed_link					    											
						);
		return $out;	
	}	
	
	//FUNCIONES DE BUSQUEDA
	
	/*
	FUNCION : feed_retrieve
	DETALLES: se conecta a un feed y obtiene la informacion necesaria
	RECIBE: n/a
	RETORNA: n/a
	INTERNO: modifica respuesta raw para tener el sitio retornado, 
	modifica respuesta findjira para convertir dicho sitio en un arreglo serializado
	*/	
	
	function feed_retrieve()
	{
		
		//conectarse y obtener la respuesta en formato nativo		
		feedsProcessingAPI::feedsProcessingAPI();
		feedsProcessingAPI::get($this->feed_url); // si es un feed valido

		//parserar xml del feed a arreglo asociativo
		$this->respuesta_raw = feedsProcessingAPI::parse_assoc_array(0);  
		//parsear resultados obtenidos del feed a formato findjira
		$this->respuesta_findjira = $this->feed_parse();
		
	}
	
	/*
	FUNCION : feed_parse
	DETALLES: parsea la informacion de un feed XML a JSON y de JSON a PHP serializado
	RECIBE: n/a
	RETORNA: arreglo en formato findjira
	INTERNO: asigna valores de variables especiales de la clase para ser extraidas despues
	crea la resupuesta_php con el arreglo del feed
	parsea toda la informacion del feed al arreglo en formato findjira
	*/
	
	function feed_parse()
	{

		$this->respuesta_php = $this->respuesta_raw ;					
				
		$this->feed_title = $this->respuesta_php["feed_info"]["title"];	
		$this->feed_link = $this->respuesta_php["feed_info"]["link"];	
				
		$i = 1;

		if($this->respuesta_php["feed_items"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}

		foreach ($this->respuesta_php["feed_items"] as &$value)
		{
				
			$respuesta[] = array (
					    			"feed_titulo"   => utf8_to_unicode_entities_encode($this->feed_title),
					    			"feed_url"		=> $this->feed_link,
					    			"post_id"		=> $value["id"],
					    			"titulo"		=> utf8_to_unicode_entities_encode($value["title"]),
					    			"fecha_post"	=> $value["date"],					    			
					    			"categoria"		=> utf8_to_unicode_entities_encode($value["category"]),
					    			"resumen"		=> utf8_to_unicode_entities_encode($value["description"]),
					    			"raw_url" 		=> $this->obtener_URL_sin_ultimo_slash($value["link"]),
					    			"display_url" 	=> $this->obtener_dominio($this->feed_link), 
					    			"cache_url" 	=> null,					    												
					    			"thumb_url" 	=> $this->obtener_web_thumbnail($this->feed_link),	//el thumbnail será de la pagina principal del blog y mas no del permalink
									"crop_url"		=> null,
					    			"embed_thumb"   => $this->obtener_embed_image($this->obtener_web_thumbnail($this->feed_link),$this->thumbnail_width,$this->thumbnail_height),
					    			"origen" 		=> "feed",
					    			"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
					    			);
			$i++;
		}
		
		return $respuesta;
	
		
	
	
	}	
	

} 

?>