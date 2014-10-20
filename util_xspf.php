<?
/*
ARCHIVO: 		util_xspf
DESCRIPCION: 	Genera un archivo XSPF volatil
USO: 		Para ser llamada desde cualquier lugar con todos los parametros indicados
RECIBE: 		ubicacion del directorio de servidor que será procesado
DEVUELVE:		un archivo XML con formato XSPF de tipo de lista de reproducción.
*/

require_once("service.php");

if(!isset($_GET["url"]))
{
	echo "Falta parametro GET: url";
	return;
}

$files = service::obtener_archivos_directorio(urldecode($_GET["url"]));
$xspf = service::generar_xspf($files);

echo $xspf;
?>