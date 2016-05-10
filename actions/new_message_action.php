<?php
	include_once("../config.php");
	include_once("../functions.php");

	if(isset($_POST["message"], $_POST["to_send"]) && isset($_SESSION['user'])):
		$new_message = trim(strip_tags($_POST["message"]));
		$to_send = (int)$_POST["to_send"];

		$message = saveNewMessage(array('message' => $new_message, 'to_send' => $to_send));
		if (!isRedis()):
        	include("../blocks/message/right_message.php");
		endif;
	endif;