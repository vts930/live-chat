<?php 
	include_once("../config.php");
	include_once("../functions.php");
 if (isset($_SESSION['user'])) {
	unset($_SESSION['user']);
	
 }
	header("Location: ../login.php");
