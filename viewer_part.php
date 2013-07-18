<?php

ini_set('error_reporting', E_ALL | E_NOTICE | E_STRICT | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);

ob_start();

libxml_use_internal_errors(true);

session_start();

require_once('viewer.php');
require_once('map.php');
require_once('layer.php');
require_once('tileset.php');
require_once('tilelayer.php');
require_once('compat.php');

$map=new Map();

$file='../../tmw/room.tmx';

if(array_key_exists('called_from',$_REQUEST) && array_key_exists('choice',$_REQUEST)) {
	$msg='';
	$var='';
	switch(strtolower($_REQUEST['choice'])) {
		case 'list':
			$var='list';
		break;
		case 'url':
			$var='url';
		break;
		case 'file':
			//$var='file';
			$msg='not yet implemented.';
		break;
	}
	if($msg!='') {
		die($msg);
	}
	if(array_key_exists($var, $_REQUEST)) {
		$file=$_REQUEST[$var];
	}
}
if(array_key_exists('ref', $_REQUEST)) {
	$ref=$_REQUEST['ref'];
}
else {
	$ref='';
}


/*var_dump($_REQUEST);
var_dump($file);//*/

//echo '<pre>'."\r\n";
$res=$map->load($file, $ref);



/*var_dump($map->getProperties());echo '<br/>'."\r\n";
var_dump($map->tilesets[0]->getProperties());echo '<br/>'."\r\n";
var_dump($map->tilesets[0]->getAllProperties());echo '<br/>'."\r\n";
//var_dump($map->tilesets[0]->getIdProperties(0));echo '<br/>'."\r\n";
var_dump($map->layers[0]->getProperties());echo '<br/>'."\r\n";
var_dump($map->objectlayers[0]->getProperties());echo '<br/>'."\r\n";
var_dump($map->objectlayers[0]->getObjectCount());echo '<br/>'."\r\n";
echo '<br/>'."\r\n";
var_dump($map->objectlayers[0]->getObject(0));echo '<br/>'."\r\n";
echo '</pre>'."\r\n";
die();//*/

$viewer=new Viewer();
$viewer->setMap($map);

ini_set('output_buffering','off');

$data=ob_get_clean();
if(!empty($data)) {
	header('Content-Type: text/plain'."\r\n");
	echo $data;
	die();
}

ob_start();

if(!array_key_exists('layers_nodraw', $_SESSION)) {
	$_SESSION['layers_nodraw']=array('collision');
}
if(!array_key_exists('tilesets_nodraw', $_SESSION)) {
	$_SESSION['tilesets_nodraw']=array('collision');
}

$viewer->load_ts();

$zoom=1;
if(array_key_exists('zoom',$_REQUEST)) {
	assert(is_numeric($_REQUEST['zoom'])) or die('bad zoom value');
	$zoom=floatval($_REQUEST['zoom']);
	$viewer->zoom=$zoom;
	assert($viewer->zoom>=0.1 && $viewer->zoom<=10) or die('bad zoom range');
}

$x=0;
if(array_key_exists('x',$_REQUEST)) {
	assert(is_numeric($_REQUEST['x'])) or die('bad x value');
	$x=intval($_REQUEST['x']);
}
$y=0;
if(array_key_exists('y',$_REQUEST)) {
	assert(is_numeric($_REQUEST['y'])) or die('bad y value');
	$y=intval($_REQUEST['y']);
}
$w=PHP_INT_MAX;
if(array_key_exists('w',$_REQUEST)) {
	assert(is_numeric($_REQUEST['w'])) or die('bad w value');
	$w=intval($_REQUEST['w']);
}
$h=PHP_INT_MAX;
if(array_key_exists('h',$_REQUEST)) {
	assert(is_numeric($_REQUEST['h'])) or die('bad h value');
	$h=intval($_REQUEST['h']);
}

$ox=-$x*$map->tilewidth *$zoom;
if(array_key_exists('ox',$_REQUEST)) {
	assert(is_numeric($_REQUEST['ox'])) or die('bad ox value');
	$ox=intval($_REQUEST['ox']);
}
$oy=-$y*$map->tileheight*$zoom;
if(array_key_exists('oy',$_REQUEST)) {
	assert(is_numeric($_REQUEST['oy'])) or die('bad oy value');
	$oy=intval($_REQUEST['oy']);
}

$dt=true;
if(array_key_exists('dt',$_REQUEST)) {
	$dt=$_REQUEST['dt'];
	if(is_null($dt)||empty($dt)) $dt=true;
	else if( strcasecmp($dt,'true' )==0 || strcasecmp($dt,'yes')==0 || $dt===1 ) $dt=true;
	else if( strcasecmp($dt,'false')==0 || strcasecmp($dt,'no' )==0 || $dt===0 ) $dt=false;
	else $dt=false;
	$viewer->draw_tiles=$dt;
}
$do=true;
if(array_key_exists('do',$_REQUEST)) {
	$do=$_REQUEST['do'];
	if(is_null($do)||empty($do)) $do=true;
	else if( strcasecmp($do,'true' )==0 || strcasecmp($do,'yes')==0 || $do===1 ) $do=true;
	else if( strcasecmp($do,'false')==0 || strcasecmp($do,'no' )==0 || $do===0 ) $do=false;
	else $do=false;
	$viewer->draw_objects=$do;
}
$di=true;
if(array_key_exists('di',$_REQUEST)) {
	$di=$_REQUEST['di'];
	if(is_null($di)||empty($di)) $do=true;
	else if( strcasecmp($di,'true' )==0 || strcasecmp($di,'yes')==0 || $di===1 ) $di=true;
	else if( strcasecmp($di,'false')==0 || strcasecmp($di,'no' )==0 || $di===0 ) $di=false;
	else $di=false;
	$viewer->draw_images=$di;
}

$viewer->ox=$ox;
$viewer->oy=$oy;

//var_dump($x, $y, $w, $h);die();

$viewer->init_draw($x, $y, $w, $h);
$viewer->draw($x, $y, $w, $h);

$data=ob_get_contents();
if(strlen($data)!=0) {
	header('Content-Type: text/plain'."\r\n");
	echo $data;
	die();
}

$viewer->render();

$data=ob_get_clean();
unset($viewer);$viewer=NULL;
if(!defined('DEBUG')||DEBUG!==true) {
	//header('Content-Type: image/jpeg'."\r\n");
	header('Content-Type: image/png'."\r\n");
}
echo $data;

?>