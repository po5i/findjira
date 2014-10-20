<?
require_once ("service.php");
require_once ("class_xml2json.php");

/*
CLASE : service_feed_blogger
DETALLES: provee los servicios necesarios para leer un numero de posts de feeds del servicio de blogger, y los convierte a formato de repositorio FindJira
*/

class service_feed extends service 
{
	
	var $feed_title; //datos propios del xml del feed
	var $feed_last_update; //ultima actualizacion del feed
	var $feed_links; //vinculos del feed, tipos de formatos q soporta
	var $feed_author_info; //acerca del autor del feed
	var $feed_generator; //fuente del feed ej, blogger, spaces, etc	
	var $feed_url; //para sacar la info, direccion del feed
	
	/*
	FUNCTION : set_feed_url
	DETALLES: setea url del feed
	RECIBE: el url completo del feed
	RETORNA: n/a
	*/
	
	function set_feed_url($URL)
	{
		$this->feed_url = $URL;				
	}
	
	/*
	FUNCTION : get_feed_url
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
	FUNCTION : get_feed_info
	DETALLES: obtiene toda la informacion del feed
	RECIBE: n/a
	RETORNA: un arreglo serializado con las variables del objeto feed
	*/
	
	function get_feed_info()
	{
		$out[] = array (					    			
					    			"feed_title"		=> $this->feed_title,
					    			"feed_last_update" 	=> $this->feed_last_update,
					    			"feed_links"		=> $this->feed_links,
					    			"feed_author_info" 	=> $this->feed_author_info, 
					    			"feed_generator" 	=> $this->feed_generator,						);
		return $out;
	
	}
	
	
	//FUNCIONES DE BUSQUEDA
	
	
	/*
	FUNCTION : feed_retrieve
	DETALLES: se conecta a un feed y obtiene la informacion necesaria
	RECIBE: n/a
	RETORNA: n/a
	INTERNO: modifica respuesta raw para tener el sitio retornado, 
	modifica respuesta findjira para convertir dicho sitio en un arreglo serializado
	*/
	
	function feed_retrieve()
	{		
		//conectarse y obtener la respuesta en formato nativo		
		$this->respuesta_raw = $this->buscar($this->feed_url);		//TODO: validar una respuesta exitosa		
		//transformar la respuesta raw a finjira		
		$this->respuesta_findjira = $this->feed_parse();			//web_parse utiliza respuesta_raw para transformarla
	}
	
	/*
	FUNCTION : feed_parse
	DETALLES: parsea la informacion de un feed XML a JSON y de JSON a PHP serializado
	RECIBE: n/a
	RETORNA: arreglo en formato findjira
	INTERNO: asigna valores de variables especiales de la clase para ser extraidas despues
	crea la resupuesta_php con el arreglo del feed
	parsea toda la informacion del feed al arreglo en formato findjira
	*/
	
	function feed_parse()
	{
		$this->respuesta_json = xml2json::transformXmlStringToJson($this->respuesta_raw);
		
		$this->respuesta_json = utf8_encode($this->respuesta_json);		
		
		$this->respuesta_php = json_decode($this->respuesta_json,true);	
		

		
		$this->feed_title = $this->respuesta_php["feed"]["title"];			
		$this->feed_last_update = $this->respuesta_php["feed"]["updated"];		
		$this->feed_links = $this->respuesta_php["feed"]["link"];	
		$this->feed_author_info = $this->respuesta_php["feed"]["author"];		
		$this->feed_generator = $this->respuesta_php["feed"]["generator"];
		
		
		
		$i = 1;
		
		if($this->respuesta_php["feed"]["entry"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		foreach ($this->respuesta_php["feed"]["entry"] as &$value)
		{

						
			$respuesta[] = array (
					    			"feed_titulo"   => $this->feed_title,
					    			"titulo"		=> $value["title"],
					    			"resumen"		=> $value["summary"],
					    			"raw_url" 		=> $this->obtener_URL_sin_ultimo_slash($value["link"][0]["@attributes"]["href"]),
					    			"display_url" 	=> $this->obtener_dominio($this->feed_links[0]["@attributes"]["href"]), 
					    			"cache_url" 	=> null,
									"feed_url"		=> $this->feed_links[1]["@attributes"]["href"] != "" ? $this->feed_links[1]["@attributes"]["href"] : $this->feed_links[0]["@attributes"]["href"],
					    			"thumb_url" 	=> $this->obtener_web_thumbnail($this->feed_links[0]["@attributes"]["href"]),	//el thumbnail será de la pagina principal del blog y mas no del permalink
									"crop_url"		=> null,
					    			"embed_thumb"   => $this->obtener_embed_image($this->obtener_web_thumbnail($this->feed_links[0]["@attributes"]["href"]),$this->thumbnail_width,$this->thumbnail_height),
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