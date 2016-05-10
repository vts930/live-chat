<?php
	include_once("../config.php");
	include_once("../functions.php");

	if(isset($_POST["message_id"], $_SESSION['user'], $_POST["to_send"])):
		$message = deleteMessage(array('message_id' => $_POST["message_id"], 'to_send' => $_POST["to_send"]));
		if($message ){
			echo json_encode(array('remove' => 1));
		}else{
			echo json_encode(array('remove' => 0));

		}
	endif;