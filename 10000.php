<?php 
include_once("config.php");
include_once("functions.php");
$start =microtime(true); 
for ($i=0; $i <100000 ; $i++) { 
	$user_id = $i;
	$to_send = $i+3;
	$create_time = date('Y-m-d H:i:s');
	$long = strtotime($create_time);												
	$messagesCount = getRedis()->ZCOUNT("messages_$user_id/$to_send","0","+inf" );
		$hashesId = getRedis()->EXISTS("message_$user_id/$to_send:$messagesCount");
		if ($hashesId==true) 
		{
			$messagesCount = $messagesCount+1;
		}
		
		$fromFirstName = "Users_$user_id";	
		$fromLastName =  "Second_$user_id";
		$toFirstName = "Users_$to_send";
		$toLastName = "Second_$to_send";
		
		$messagesHash = getRedis()->HMSET("message_$user_id/$to_send:$messagesCount","id",$messagesCount,"message","message_$i","to_send",$to_send,"from_send",$user_id,"create_time",date('Y-m-d H:i:s'),"to_first_name",$fromFirstName,"to_last_name",$fromLastName,"from_first_name",$toFirstName,"from_last_name",$toLastName,"is_from_db",0);
		$getMessagesFromHashes= getRedis()->HGETALL("message_$user_id/$to_send:$messagesCount");
		$encode_message =json_encode($getMessagesFromHashes);
		$messagesSortedSets = getRedis()->ZADD("messages_$user_id/$to_send",$long,$encode_message);

	}

$endtime = microtime(true);
$diff = $endtime-$start;
echo  $diff; 