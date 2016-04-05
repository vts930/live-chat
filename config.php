<?php
session_start();
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
$UseRedis = true;
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
}
else
		{
			$UseRedis = null;
		}