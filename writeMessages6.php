<?php 
include_once("config.php");
include_once("functions.php");
 $start =microtime(true); 
for ($i=1900; $i <1950; $i++) { 
	for ($k=1900; $k <1950; $k++) { 
	
		//$user_id = $_SESSION['user']['id'];
		$user_id = $i;
		$query = getDatabase()->prepare('
		INSERT INTO messages
			(message, to_send, from_send,to_first_name,to_last_name,from_first_name,from_last_name) 
			VALUES 
			(:message, :to_send, :from_send,:to_first_name,:to_last_name,:from_first_name,:from_last_name)
		');
		$query->bindValue(":message", "message_$k");
		$query->bindValue(":to_send", $k);
		$query->bindValue(":from_send",$user_id);
		$query->bindValue(":to_first_name","Users_$k");
		$query->bindValue(":to_last_name","Second_$k");
		$query->bindValue(":from_first_name","Users_$user_id");
		$query->bindValue(":from_last_name","Second_$user_id");
		$query->execute();
	}	
}
$endtime = microtime(true);
$diff = $endtime-$start;
echo  $diff; 
?>
