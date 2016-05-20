<?php 
include_once("config.php");
include_once("functions.php");
$start =microtime(true); 
for ($i=0; $i <100000 ; $i++) { 
	$user_id = $i;
	$to_send = $i+1;
		$query = getDatabase()->prepare('
		INSERT INTO messages
			(message, to_send, from_send,to_first_name,to_last_name,from_first_name,from_last_name) 
			VALUES 
			(:message, :to_send, :from_send,:to_first_name,:to_last_name,:from_first_name,:from_last_name)
		');
		$query->bindValue(":message", "message_$i");
		$query->bindValue(":to_send", $to_send);
		$query->bindValue(":from_send",$user_id);
		$query->bindValue(":to_first_name","Users_$to_send");
		$query->bindValue(":to_last_name","Second_$to_send");
		$query->bindValue(":from_first_name","Users_$user_id");
		$query->bindValue(":from_last_name","Second_$user_id");
		$query->execute();
}
$endtime = microtime(true);
$diff = $endtime-$start;
echo  $diff; 