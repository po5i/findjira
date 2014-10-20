<?
require_once ("service.php");
require_once ("feedsProcessingAPI/feedsProcessingAPI.php");
require_once ("class_xml2json.php");


/*
CLASE : service_feed_podcast
DETALLES: proveera soporte para feeds de podcasts o videocasts
*/

class service_feed_podcast extends service 
{
	
	var $feed_title;//datos propios del xml del feed
	var $feed_link;
	
	var $feed_url; //para sacar la info
	
	function set_feed_url($URL)
	{
		$this->feed_url = $URL;		
		
	}
	
	function get_feed_url()
	{
		$out = $this->feed_url;
		
		return $out;
	
	}
	
	function get_feed_info()
	{
		$out[] = array (
					    			
					    			"feed_title"	=> $this->feed_title,
					    			"feed_link"		=> $this->feed_link					    											
					    			);
		return $out;
	
	}
	
	
	//FUNCIONES DE BUSQUEDA
	
	function feed_retrieve()
	{
		
		//conectarse y obtener la respuesta en formato nativo
		
		feedsProcessingAPI::feedsProcessingAPI();
		feedsProcessingAPI::get($this->feed_url); // si es un feed valido

		//a la clase
		//funcion parodiada por davicho
		$this->respuesta_raw = feedsProcessingAPI::parse_assoc_array(0);  
		//0, para q muestre todos los resultados conseguidos del feed,
		$this->respuesta_findjira = $this->feed_parse();
		
	}
	
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
					    			"feed_titulo"   => $this->feed_title,
					    			"feed_url"		=> $this->feed_link,
					    			"post_id"		=> $value["id"],
					    			"titulo"		=> $value["title"],
					    			"fecha_post"	=> $value["date"],					    			
					    			"categoria"		=> $value["category"],
					    			"resumen"		=> $value["description"],
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