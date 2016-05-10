<?php
session_start();
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
$UseRedis =true;
$db = new PDO('mysql:host=localhost;dbname=baigiamasis;charset=utf8', 'root', 'tarakonas');
//$db = new PDO('mysql:host=localhost;dbname=u606861065_bbd;charset=utf8', 'u606861065_bbd', 'vgtubakalauras');
if ($UseRedis == true) {
	require "predis/autoload.php";
	Predis\Autoloader::register();
	try 
	{
	   	$redis = new Predis\Client();
	}
	catch (Exception $e) 
	{
	    echo "Couldn't connected to Redis";
	    echo $e->getMessage();
	}

}else{
	
	$UseRedis = false;
}


function isRedis(){
	global $UseRedis;
	return $UseRedis;
}

function getRedis(){
	global $redis;
	return $redis;
}
function getDatabase(){
	global $db;
	return $db;
}
