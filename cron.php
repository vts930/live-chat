<?php 
	include_once("config.php");
	include_once("functions.php");
 
 	//$user_id = (int)$_SESSION['user']['id'];

 	//tik cia dabar reikia butent to konnectiono
	$getAllUserConnections= getRedis()->KEYS("messages_*");

	foreach ($getAllUserConnections as $getAllUserConnections) {
				
		$getMessages = getRedis()->ZRANGE($getAllUserConnections,"0","-1");
		
		foreach ($getMessages as $getMessage ) {
				$getMessage =json_decode($getMessage,true);	
				$query = getDatabase()->prepare('
				INSERT INTO messages
				( message, to_send, from_send,create_time,to_first_name,to_last_name,from_first_name,from_last_name) 
				VALUES 
				(:message, :to_send, :from_send, :create_time, :to_first_name, :to_last_name, :from_first_name, :from_last_name)
				');
				//$query->bindValue(":id", $getMessage['id']);
				$query->bindValue(":message", $getMessage['message']);
				$query->bindValue(":to_send", $getMessage['to_send']);
				$query->bindValue(":from_send",  $getMessage['from_send']);
				$query->bindValue(":create_time",  $getMessage['create_time']);
				$query->bindValue(":to_first_name",  $getMessage['to_first_name']);
				$query->bindValue(":to_last_name",  $getMessage['to_last_name']);
				$query->bindValue(":from_first_name",  $getMessage['from_first_name']);
				$query->bindValue(":from_last_name",  $getMessage['from_last_name']);
				$query->execute();
		}

	}
	getRedis()->flushall();	
					