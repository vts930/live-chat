<?php
	include_once("../config.php");
	include_once("../functions.php");

	if(isset($_POST["last_message_id"], $_POST["from_send"], $_SESSION['user'])):
		$last_message_id = (int)$_POST["last_message_id"];
		$user_id = $_SESSION['user']['id'];
		$from_send = (int)$_POST["from_send"];
		$messages = getLastMessagesByUser(array('last_message_id' => $last_message_id, 'from_send' => $from_send));
		?>
        <?php foreach ($messages as $message): ?>
	       <?php if (isRedis()):?>
	       <?php $message =json_decode($message,true); ?>
	       <?php endif ?>
	      <?php if ($message["from_send"] == $user_id): ?>
	        <?php include("../blocks/message/right_message.php") ?>
	      <?php else: ?>
	        <?php include("../blocks/message/left_message.php") ?>
	      <?php endif ?>
	    <?php endforeach ?>
	<?php endif; ?>