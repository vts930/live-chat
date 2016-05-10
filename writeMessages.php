<?php 
include_once("config.php");
include_once("functions.php");
 $start =microtime(true); 
for ($i=0; $i <30; $i++) { 
	for ($k=0; $k <30 ; $k++) { 
	
		//$user_id = $_SESSION['user']['id'];
		$user_id = $k;
		$query = getDatabase()->prepare('
		INSERT INTO messages
			(message, to_send, from_send) 
			VALUES 
			(:message, :to_send, :from_send)
		');
		$query->bindValue(":message", "message_$i");
		$query->bindValue(":to_send", $i);
		$query->bindValue(":from_send",$user_id);
		$query->execute();
	}	
}
$endtime = microtime(true);
$diff = $endtime-$start;
echo  $diff; 
?>
