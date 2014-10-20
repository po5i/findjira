<?
//CLASSES DE SERVICIOS
require_once("service_flickr.php");
require_once("service_itunes.php");
require_once("service_live.php");
require_once("service_odeo.php");
require_once("service_searchmash.php");
require_once("service_technorati.php");
require_once("service_yahoo.php");
require_once("service_youtube.php");
require_once("repositorio.php");

/*
CLASE : 	handler
DETALLES: esta clase es una interfaz entre el framework y el usuario, siendo parte aun del framework, permite una integración entre servicios y repositorios.
*/
class handler
{
	var $query;
	var $resultados_por_pagina;
	var $thumbnail_width;
	var $thumbnail_height;             
	
	//parametros avanzados
	var $site;
	var $intitle;
	var $inurl;
	var $filetype;
	var $excluidos;
	
	//repositorios
	var $rep_audio;
	var $rep_feed;
	var $rep_image;
	var $rep_web;
	var $rep_video;
	var $rep_file;
	
	//SERVICIOS
	var $ser_flickr;
	var $ser_itunes;
	var $ser_live;
	var $ser_odeo;
	var $ser_searchmash;
	var $ser_technorati;
	var $ser_yahoo;
	var $ser_youtube;
	
	
	/*
	FUNCION: 		constructor
	DESCRIPCION: 	Aqui se inicializan todos los componentes y se crean los objetos de tipo repositorio y servicios
	USO: 		Automaticamente al crear un objeto de esta clase
	RECIBE: 		n/a
	DEVUELVE:		n/a
	*/
	function __construct()
	{
		//inicializar atributos		
		$query = "";
		$resultados_por_pagina = "";
		$thumbnail_width = "";
		$thumbnail_height = "";    
		
		$site = "";
		$intitle = "";
		$inurl = "";
		$filetype = "";
		$excluidos = "";
		
		
		//inicializar repositorios
		$this->rep_audio = new repositorio();
		$this->rep_feed = new repositorio();
		$this->rep_image = new repositorio();
		$this->rep_web = new repositorio();
		$this->rep_video = new repositorio();
		$this->rep_file = new repositorio();
		
		//inicializar SERVICIOS
		$this->ser_flickr = new service_flickr();
		$this->ser_itunes = new service_itunes();
		$this->ser_live = new service_live();
		$this->ser_odeo = new service_odeo();
		$this->ser_searchmash = new service_searchmash();
		$this->ser_technorati = new service_technorati();
		$this->ser_yahoo = new service_yahoo();
		$this->ser_youtube = new service_youtube();
		
		//maximo numero de resultados por pagina: depende del API
		$this->ser_flickr->set_max_resultados_por_pagina(500);
		$this->ser_live->set_max_resultados_por_pagina(10);
		$this->ser_odeo->set_max_resultados_por_pagina(100);
		$this->ser_searchmash->set_max_resultados_por_pagina(10);
		$this->ser_technorati->set_max_resultados_por_pagina(100);
		$this->ser_yahoo->set_max_resultados_por_pagina(100);
		$this->ser_youtube->set_max_resultados_por_pagina(100);
	}	
	
	
	/*
	FUNCION: 		limpiar_repositorios
	DESCRIPCION: 	Vacia el contenido de todos los repositorios usados
	USO: 		Cada vez que se desee limpiar el contenido de los repositorios
	RECIBE: 		n/a
	DEVUELVE:		n/a
	*/
	function limpiar_repositorios()
	{
		$this->rep_audio->limpiar();
		$this->rep_feed->limpiar();
		$this->rep_image->limpiar();
		$this->rep_web->limpiar();
		$this->rep_video->limpiar();
		$this->rep_file->limpiar();
	}
	
	/*
	FUNCION: 		preparar_busqueda
	DESCRIPCION: 	Prepara las variables para ser utilizadas en la búsqueda simultanea
	USO: 		Antes de llamar las funciones de do_*_search
	RECIBE: 		query
				numero de resultados por pagina
				resultado inicial de esa página
				ancho de vista previa
				alto de vista previa
				ancho de embed
				alto de embed
				restriccion a sitio web
				buscar este texto en titulos
				buscar este texto en url
				tipos de archivos deseados
				palabras excluisdas de la busqueda
	DEVUELVE:		n/a
	*/
	function preparar_busqueda(
								$query,
								$resultados_por_pagina,
								$resultado_inicial,
								$thumbnail_width,
								$thumbnail_height,
								$embed_width,
								$embed_height,
								$site,
								$intitle,
								$inurl,
								$filetype,
								$excluidos
								)	
	{
		$this->setear_queries($query);					
		$this->setear_resultados_por_pagina($resultados_por_pagina);
		$this->setear_resultado_inicial($resultado_inicial);
		$this->setear_dimensiones_thumbnail($thumbnail_width,$thumbnail_height);
		$this->setear_dimensiones_embed($embed_width,$embed_height);
		
		if ($site!= "")
		{
			$this->ser_yahoo->set_site($site);
			$this->ser_searchmash->set_site($site);
		}
		
		if ($intitle!= "")
		{
			$this->ser_yahoo->set_intitle($intitle);
			$this->ser_searchmash->set_intitle($intitle);
		}	

		if ($inurl!= "")
		{
			$this->ser_yahoo->set_inurl($inurl);
			$this->ser_searchmash->set_inurl($inurl);
		}	
		
		if ($filetype!= "")
		{
			$this->ser_yahoo->set_filetype($filetype);
			$this->ser_searchmash->set_filetype($filetype);
		}	
		
		if ($excluidos!= "")
		{
			$this->ser_yahoo->set_excluidos($excluidos);
			$this->ser_searchmash->set_excluidos($excluidos);
		}			
		
		$this->limpiar_repositorios();
	}
	
		
	/*
	FUNCION: 		extraer_repositorio_video
	DESCRIPCION: 	Extrae el contenido del repositorio de video
	USO: 		Cada vez que se solicite el arreglo que conforma el repositorio de video
	RECIBE: 		indice inicial de elemento
				indice final de elemento
	DEVUELVE:		Un arreglo con el repositorio de video
	*/
	function extraer_repositorio_video($indice_inicial = "default", $indice_final = "default")
	{
		if($indice_inicial == "default" or $indice_final == "default")
			return $this->rep_video->extraer_elementos();
		else
			return $this->rep_video->extraer_batch($indice_inicial,$indice_final);
	}
	
	/*
	FUNCION: 		extraer_repositorio_audio
	DESCRIPCION: 	Extrae el contenido del repositorio de audio
	USO: 		Cada vez que se solicite el arreglo que conforma el repositorio de audio
	RECIBE: 		indice inicial de elemento
				indice final de elemento
	DEVUELVE:		Un arreglo con el repositorio de audio
	*/
	function extraer_repositorio_audio($indice_inicial = "default", $indice_final = "default")
	{
		if($indice_inicial == "default" or $indice_final == "default")
			return $this->rep_audio->extraer_elementos();
		else
			return $this->rep_audio->extraer_batch($indice_inicial,$indice_final);
	}
	
	/*
	FUNCION: 		extraer_repositorio_feed
	DESCRIPCION: 	Extrae el contenido del repositorio de feed
	USO: 		Cada vez que se solicite el arreglo que conforma el repositorio de feed
	RECIBE: 		indice inicial de elemento
				indice final de elemento
	DEVUELVE:		Un arreglo con el repositorio de feed
	*/
	function extraer_repositorio_feed($indice_inicial = "default", $indice_final = "default")
	{
		if($indice_inicial == "default" or $indice_final == "default")
			return $this->rep_feed->extraer_elementos();
		else
			return $this->rep_feed->extraer_batch($indice_inicial,$indice_final);
	}
	
	/*
	FUNCION: 		extraer_repositorio_image
	DESCRIPCION: 	Extrae el contenido del repositorio de imagenes
	USO: 		Cada vez que se solicite el arreglo que conforma el repositorio de imagenes
	RECIBE: 		indice inicial de elemento
				indice final de elemento
	DEVUELVE:		Un arreglo con el repositorio de imagenes
	*/
	function extraer_repositorio_image($indice_inicial = "default", $indice_final = "default")
	{
		if($indice_inicial == "default" or $indice_final == "default")
			return $this->rep_image->extraer_elementos();
		else
			return $this->rep_image->extraer_batch($indice_inicial,$indice_final);
	}
	
	/*
	FUNCION: 		extraer_repositorio_web
	DESCRIPCION: 	Extrae el contenido del repositorio web
	USO: 		Cada vez que se solicite el arreglo que conforma el repositorio web
	RECIBE: 		indice inicial de elemento
				indice final de elemento
	DEVUELVE:		Un arreglo con el repositorio web
	*/
	function extraer_repositorio_web($indice_inicial = "default", $indice_final = "default")
	{
		if($indice_inicial == "default" or $indice_final == "default")
			return $this->rep_web->extraer_elementos();
		else
			return $this->rep_web->extraer_batch($indice_inicial,$indice_final);
	}
	
	/*
	FUNCION: 		extraer_repositorio_file
	DESCRIPCION: 	Extrae el contenido del repositorio de archivos
	USO: 		Cada vez que se solicite el arreglo que conforma el repositorio de archivos
	RECIBE: 		indice inicial de elemento
				indice final de elemento
	DEVUELVE:		Un arreglo con el repositorio de archivos
	*/
	function extraer_repositorio_file($indice_inicial = "default", $indice_final = "default")
	{
		if($indice_inicial == "default" or $indice_final == "default")
			return $this->rep_file->extraer_elementos();
		else
			return $this->rep_file->extraer_batch($indice_inicial,$indice_final);
	}
	
	/*
	FUNCION: 		do_all_search
	DESCRIPCION: 	Realiza todas las busquedas simultaneamente
	USO: 		Ejecuta las busquedas de todos los tipos de medios pero este proceso es muy costoso
	RECIBE: 		n/a
	DEVUELVE:		n/a
	*/
	function do_all_search()
	{
		$this->do_video_search();
		$this->do_audio_search();
		$this->do_feed_search();
		$this->do_image_search();
		$this->do_web_search();
		//$this->do_wiki_search();
		//$this->do_file_search("doc|pdf|txt");
	}
	
	/*
	FUNCION: 		do_video_search
	DESCRIPCION: 	Realiza busqueda de videos
	USO: 		Cada vez que se desee ejecutar una búsqueda de videos
	RECIBE: 		elemento inicial de resultados
				total de resultados deseados
	DEVUELVE:		n/a
	*/
	function do_video_search($inicial,$total)
	{
		$this->search_n_medio($this->ser_searchmash,"video",$inicial,$total);
		$this->search_n_medio($this->ser_yahoo,"video",$inicial,$total);		
	}
	
	/*
	FUNCION: 		do_audio_search
	DESCRIPCION: 	Realiza busqueda de audio
	USO: 		Cada vez que se desee ejecutar una búsqueda de audio
	RECIBE: 		elemento inicial de resultados
				total de resultados deseados
	DEVUELVE:		n/a
	*/
	function do_audio_search($inicial,$total)
	{
		$this->search_n_medio($this->ser_itunes,"audio",$inicial,$total);
		$this->search_n_medio($this->ser_odeo,"audio",$inicial,$total);		
	}	
	
	/*
	FUNCION: 		do_feed_search
	DESCRIPCION: 	Realiza busqueda de feeds
	USO: 		Cada vez que se desee ejecutar una búsqueda de feeds
	RECIBE: 		elemento inicial de resultados
				total de resultados deseados
	DEVUELVE:		n/a
	*/
	function do_feed_search($inicial,$total)
	{
		$this->search_n_medio($this->ser_live,"feed",$inicial,$total);
		$this->search_n_medio($this->ser_searchmash,"feed",$inicial,$total);		
		$this->search_n_medio($this->ser_technorati,"feed",$inicial,$total);		
	}
	
	/*
	FUNCION: 		do_image_search
	DESCRIPCION: 	Realiza busqueda de imágenes
	USO: 		Cada vez que se desee ejecutar una búsqueda de imágenes
	RECIBE: 		elemento inicial de resultados
				total de resultados deseados
	DEVUELVE:		n/a
	*/
	function do_image_search($inicial,$total)
	{
		$this->search_n_medio($this->ser_searchmash,"image",$inicial,$total);	
		$this->search_n_medio($this->ser_flickr,"image",$inicial,$total);
		$this->search_n_medio($this->ser_live,"image",$inicial,$total);					
	}	
	
	/*
	FUNCION: 		do_web_search
	DESCRIPCION: 	Realiza busqueda de páginas web
	USO: 		Cada vez que se desee ejecutar una búsqueda de páginas web
	RECIBE: 		elemento inicial de resultados
				total de resultados deseados
	DEVUELVE:		n/a
	*/
	function do_web_search($inicial,$total)
	{
		$this->search_n_medio($this->ser_searchmash,"web",$inicial,$total);			
		$this->search_n_medio($this->ser_live,"web",$inicial,$total);
		$this->search_n_medio($this->ser_yahoo,"web",$inicial,$total);				
	}	
	
	/*
	FUNCION: 		do_indexof_search
	DESCRIPCION: 	Realiza busqueda en directorios
	USO: 		Cada vez que se desee ejecutar una búsqueda en directorios
	RECIBE: 		elemento inicial de resultados
				total de resultados deseados
	DEVUELVE:		n/a
	*/
	function do_indexof_search($inicial,$total)
	{
		$this->search_n_medio($this->ser_searchmash,"indexof",$inicial,$total);			
		$this->search_n_medio($this->ser_live,"indexof",$inicial,$total);
		$this->search_n_medio($this->ser_yahoo,"indexof",$inicial,$total);				
	}
	
		
	/* **************************************************** */

	/*function do_wiki_search()
	{			
		$this->ser_searchmash->wiki_search();	
		
		$this->rep_web->insertar_batch($this->ser_searchmash->get_respuesta_findjira());		
	}
	
	function do_file_search($extensiones)	//extensiones separadas con barra vertical "|"
	{		
		$this->ser_searchmash->set_query($this->query." ".$extensiones);	
		$this->ser_searchmash->indexof_search();
		$this->ser_searchmash->set_query($this->query);
		
		$this->rep_file->insertar_batch($this->ser_searchmash->get_respuesta_findjira());
	}
	*/

	
	
	/*
	FUNCION: 		setear_queries
	DESCRIPCION: 	Asigna el query en los objetos de servicios
	USO: 		Es utilizada en preparar_busqueda
	RECIBE: 		query
	DEVUELVE:		n/a
	*/
	function setear_queries($query)
	{
		$this->ser_flickr->set_query($query);
		$this->ser_itunes->set_query($query);
		$this->ser_live->set_query($query);
		$this->ser_odeo->set_query($query);
		$this->ser_searchmash->set_query($query);
		$this->ser_technorati->set_query($query);
		$this->ser_yahoo->set_query($query);
		$this->ser_youtube->set_query($query);
	}
	
	/*
	FUNCION: 		setear_queries
	DESCRIPCION: 	Asigna el query en los objetos de servicios
	USO: 		Es utilizada en preparar_busqueda
	RECIBE: 		query
	DEVUELVE:		n/a
	*/
	function setear_resultados_por_pagina($rpp)
	{
		$this->ser_flickr->set_resultados_por_pagina($rpp);		
		$this->ser_itunes->set_resultados_por_pagina($rpp);		
		$this->ser_live->set_resultados_por_pagina($rpp);
		$this->ser_odeo->set_resultados_por_pagina($rpp);
		$this->ser_searchmash->set_resultados_por_pagina($rpp);
		$this->ser_technorati->set_resultados_por_pagina($rpp);
		$this->ser_yahoo->set_resultados_por_pagina($rpp);
		$this->ser_youtube->set_resultados_por_pagina($rpp);
	}
	
	/*
	FUNCION: 		setear_resultado_inicial
	DESCRIPCION: 	Asigna el resultado inicial en los objetos de servicios
	USO: 		Es utilizada en preparar_busqueda
	RECIBE: 		indice de resultado inicial
	DEVUELVE:		n/a
	*/
	function setear_resultado_inicial($ri)
	{
		$this->ser_flickr->set_resultado_inicial($ri);		
		$this->ser_itunes->set_resultado_inicial($ri);		
		$this->ser_live->set_resultado_inicial($ri);
		$this->ser_odeo->set_resultado_inicial($ri);
		$this->ser_searchmash->set_resultado_inicial($ri);
		$this->ser_technorati->set_resultado_inicial($ri);
		$this->ser_yahoo->set_resultado_inicial($ri);
		$this->ser_youtube->set_resultado_inicial($ri);
	}
	
	/*
	FUNCION: 		setear_dimensiones_thumbnail
	DESCRIPCION: 	Asigna las dimensiones de la vista previa en los objetos de servicios
	USO: 		Es utilizada en preparar_busqueda
	RECIBE: 		ancho y alto
	DEVUELVE:		n/a
	*/
	function setear_dimensiones_thumbnail($w,$h)
	{
		$this->ser_flickr->set_thumbnail_res($w,$h);
		$this->ser_itunes->set_thumbnail_res($w,$h);
		$this->ser_live->set_thumbnail_res($w,$h);
		$this->ser_odeo->set_thumbnail_res($w,$h);
		$this->ser_searchmash->set_thumbnail_res($w,$h);
		$this->ser_technorati->set_thumbnail_res($w,$h);
		$this->ser_yahoo->set_thumbnail_res($w,$h);
		$this->ser_youtube->set_thumbnail_res($w,$h);
	}
	
	/*
	FUNCION: 		setear_dimensiones_embed
	DESCRIPCION: 	Asigna las dimensiones del objeto incrustable en los objetos de servicios
	USO: 		Es utilizada en preparar_busqueda
	RECIBE: 		ancho y alto
	DEVUELVE:		n/a
	*/
	function setear_dimensiones_embed($w,$h)
	{
		$this->ser_flickr->set_embed_res($w,$h);
		$this->ser_itunes->set_embed_res($w,$h);
		$this->ser_live->set_embed_res($w,$h);
		$this->ser_odeo->set_embed_res($w,$h);
		$this->ser_searchmash->set_embed_res($w,$h);
		$this->ser_technorati->set_embed_res($w,$h);
		$this->ser_yahoo->set_embed_res($w,$h);
		$this->ser_youtube->set_embed_res($w,$h);
	}
	
	//UTILS
	
	/*
	FUNCION: 		setear_dimensiones_embed
	DESCRIPCION: 	Hacer un hipervinculo del los servicios de un resultado dado un string separado con comas
	USO: 		Es usado en los php_handlers para mostrar en el GUI 
	RECIBE: 		un string con palabras que representan los servicios separadas por coma
				el query
	DEVUELVE:		un string con formato HTML con hipervinculos de imágenes apuntando hacia la busqueda en cada servicio.
	*/
	function make_service_links($services,$q)
	{
		$servicios = explode(",",$services);
		$servicio_link = "";
		foreach($servicios as $servicio)
		{
			switch($servicio)
			{
				case "google":
					$link_q = "http://www.google.com/search?q=";
					break;
				case "yahoo":
					$link_q = "http://search.yahoo.com/search?p=";
					break;
				case "live":
					$link_q = "http://search.live.com/results.aspx?q=";
					break;
				case "technorati":
					$link_q = "http://www.technorati.com/search/";
					break;
				case "flickr":
					$link_q = "http://www.flickr.com/search/?q=";
					break;
				case "youtube":
					$link_q = "http://www.youtube.com/results.php?search_query=";
					break;
				case "odeo":
					$link_q = "http://odeo.com/find/";
					break;
				case "itunes":
					$link_q = "http://ax.phobos.apple.com.edgesuite.net/WebObjects/MZStore.woa/wa/browserRedirect?url=itms%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZSearch.woa%252Fwa%252Fsearch%253Fterm%253D";
					break;				
			}
			
			$servicio_link .= "<a href=\"".$link_q.$q."\" target=\"_blank\"><img border=\"0\" src=\"images/favicons/".$servicio.".png\"></a> ";
		}

		return $servicio_link;	
	}
	
	
	/*
	FUNCION: 		search_n_medio
	DESCRIPCION: 	Busquedas iterativas para cada servicio y en cada una recibiendo un trozo de resultados
	USO: 		Dado que cada servicio tiene sus limites, esta funcion llamada en los do_*_search basado en el limite que desa el usuario
	RECIBE: 		objeto de servicio
				un string que representa el medio
				elemento inicial de resultados
				total de resultados deseados
	DEVUELVE:		n/a
	*/
	function search_n_medio($servicio,$medio,$inicio,$total_resultados)
	{
		$limite_service = 10; //de cuantos resultados es cada tanda de search
		
		if ( ($inicio < 1) || ($inicio == null) || ($inicio == "")) 
			$inicio =1;
			
		if ($servicio == null) 
			return;	
		
		if ( ($total_resultados < 1)) 
			$total_resultados =1;			
				
		for ($counter = $inicio; $counter <= $total_resultados; $counter += $limite_service)
		{
					$servicio->set_resultado_inicial($counter);
					$servicio->set_resultados_por_pagina($limite_service);
					
							switch ($medio)
							{
								case "web":
									$servicio->web_search();
									$this->rep_web->insertar_batch($servicio->get_respuesta_findjira());
								break;
								case "image":
									$servicio->image_search();
									$this->rep_image->insertar_batch($servicio->get_respuesta_findjira());
								break;
								case "audio":
									$servicio->audio_search();
									$this->rep_audio->insertar_batch($servicio->get_respuesta_findjira());
								break;
								case "feed":
									$servicio->blog_search();
									$this->rep_feed->insertar_batch($servicio->get_respuesta_findjira());
								break;
								case "video":
									$servicio->video_search();
									$this->rep_video->insertar_batch($servicio->get_respuesta_findjira());									
								break;
								case "indexof":
									$servicio->indexof_search();	
									$this->rep_web->insertar_batch($servicio->get_respuesta_findjira());								
								break;
								default:
								break;
							}
		
		}
		
		$servicio->set_resultados_por_pagina($servicio->get_resultados_por_pagina); // lo dejamos como estaba
		
	}
}

?>