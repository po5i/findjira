<?
/*
ARCHIVO: 		util_recortar
DESCRIPCION: 	Recorta una imagen sin desproporcionarla, luego la imprime en pantalla
USO: 		Para ser llamada desde cualquier lugar con todos los parametros indicados
RECIBE: 		nuevo ancho en pixeles
			nuevo alto en pixeles
			ubicacion de la imagen
			extension del archivo
DEVUELVE:		imprime (con encabezado) la imagen procesada
CREDITOS:		http://www.findmotive.com/2006/12/13/php-crop-image/
			http://www.jpexs.com/eng/default/php.html
*/

require_once("class_bmp.php");

cropImage($_GET["w"], $_GET["h"], utf8_decode($_GET["src"]), $_GET["ext"]);

function cropImage($nw, $nh, $source, $stype) 
{	 	    
	$size = getimagesize($source);
	$w = $size [0];
	$h = $size [1];
 
	switch( $stype) {
		case 'gif':
		$simg = imagecreatefromgif ($source);
		break;
		case 'jpg':
		case 'jpeg':
		$simg = imagecreatefromjpeg ($source);
		break;
		case 'png':
		$simg = imagecreatefrompng ($source);
		break;
		case 'bmp':
		$simg = imagecreatefrombmp($source);
		break;
		case 'wbmp':
		$simg = imagecreatefromwbmp($source);
		break;
		case 'xbm':
		$simg = imagecreatefromxbm($source);
		break; 
		
		default:
		$simg = null;
		break;
	}
 
	$dimg = imagecreatetruecolor ($nw, $nh);
 
	$wm = $w/ $nw;
	$hm = $h/ $nh;
 
	$h_height = $nh/ 2;
	$w_height = $nw/ 2;
 
	if( $w> $h) {
 
		$adjusted_width = $w / $hm;
		$half_width = $adjusted_width / 2;
		$int_width = $half_width - $w_height;
 
		imagecopyresampled($dimg ,$simg,-$int_width,0,0, 0,$adjusted_width,$nh,$w,$h );
 
	} elseif (($w <$h ) || ($w == $h )) {
 
		$adjusted_height = $h / $wm;
		$half_height = $adjusted_height / 2;
		$int_height = $half_height - $h_height;
 
		imagecopyresampled($dimg ,$simg,0,-$int_height,0, 0,$nw,$adjusted_height,$w,$h );
 
	} else {
		imagecopyresampled($dimg ,$simg,0,0,0, 0,$nw,$nh,$w,$h );
	}
	
	header('Content-type: image/jpeg');
	imagejpeg($dimg);
}
?>