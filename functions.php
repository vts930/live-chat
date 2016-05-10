<?php
	function getLastConnections()
	{

			/*$user_id = $_SESSION['user']['id'];
			$query = getDatabase()->prepare('
				SELECT
					u.id as to_send,
					u.first_name as to_first_name,
					u.last_name as to_last_name,
					u.avatar,
					(
						SELECT 
							m.message 
						FROM messages AS m 
						WHERE 
							(m.to_send = :userId AND m.from_send = u.id) 
							OR 
							(m.to_send = u.id AND m.from_send = :userId) 
						ORDER BY m.create_time DESC 
						LIMIT 1
					) AS message
				FROM users AS u
				WHERE u.id IN(SELECT IF(m.to_send != :userId, m.to_send, m.from_send) FROM messages AS m WHERE m.to_send = :userId OR m.from_send = :userId GROUP BY m.to_send, m.from_send ORDER BY m.create_time DESC) AND u.id != :userId
			');
			$query->bindValue(":userId", $user_id);
			$query->execute();
			
			return $query->fetchAll(PDO::FETCH_ASSOC);*/
			return array();
		
	}

	function getAllUsers()
	{
		$user_id = $_SESSION['user']['id'];
		$query = getDatabase()->prepare('
			SELECT * FROM users LIMIT 1000
		');
		$query->bindValue(":userId", $user_id);
		$query->execute();							
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
	function getAllMessagesByUser($from_send)
	{
				if (isRedis()) 
					{					
						$user_id = $_SESSION['user']['id'];
						$firstKeyName = getRedis()->EXISTS("messages_$user_id/$from_send");
						$secondKeyName = getRedis()->EXISTS("messages_$from_send/$user_id");
						$to_send_messages_array = array();
						
						if ($firstKeyName == true) 
						{
							echo "Naudojamas Redis";
							$getAllConnectionsMesages = getRedis() ->ZRANGE("messages_$user_id/$from_send", 0, -1);							
							foreach ($getAllConnectionsMesages as $getAllConnectionMessage) 
							{
								$decodes_to_send_message = json_decode($getAllConnectionMessage,true);
								array_push($to_send_messages_array, $decodes_to_send_message);
							}
						}
						elseif ($secondKeyName==true){ 
							echo "Naudojamas Redis";
							$getAllConnectionsMesages = getRedis() ->ZRANGE("messages_$from_send/$user_id", 0, -1);
							
							foreach ($getAllConnectionsMesages as $getAllConnectionMessage) 
							{
								$decodes_to_send_message = json_decode($getAllConnectionMessage,true);
								array_push($to_send_messages_array, $decodes_to_send_message);
							}
						}		
						else
						{					
						 	echo "Sukuriamas naujas redis Sorted Sets";
							$user_id = $_SESSION['user']['id'];
							$query = getDatabase()->prepare('
								SELECT 
									m.id,
									m.message,
									m.create_time,
									u.first_name AS to_first_name,
									u.last_name AS to_last_name,
									m.to_send,
									m.from_send
								FROM messages AS m 
								LEFT JOIN users AS u ON u.id = m.from_send
								WHERE (m.to_send = :fromSend AND m.from_send = :userId) OR (m.to_send = :userId AND m.from_send = :fromSend)
								GROUP BY m.id
								ORDER BY m.create_time
							');	
							$query->bindValue(":fromSend", $from_send);
							$query->bindValue(":userId", $user_id);
							$query->execute();
							
							$to_send_messages_array = $query->fetchAll(PDO::FETCH_ASSOC);
							
							foreach ($to_send_messages_array as $key ) {
								$date = date_create($key["create_time"]);							
								$getDate = date_format($date, 'Y-m-d H:i:s');
								$long = strtotime($getDate);									
								
								$encode_message =json_encode($key);
								$messagesSortedSets = getRedis()->ZADD("messages_$user_id/$from_send",$long,$encode_message);
								//var_dump($messagesSortedSets);
							}
							
						}	
						return $to_send_messages_array;	
					}
				else
					{
						$user_id = $_SESSION['user']['id'];
						$query = getDatabase()->prepare('
							SELECT 
								m.id,
								m.message,
								m.create_time,
								u.first_name AS to_first_name,
								u.last_name AS to_last_name,
								m.to_send,
								m.from_send
							FROM messages AS m 
							LEFT JOIN users AS u ON u.id = m.from_send
							WHERE (m.to_send = :fromSend AND m.from_send = :userId) OR (m.to_send = :userId AND m.from_send = :fromSend)
							GROUP BY m.id
							ORDER BY m.create_time
						');	
						$query->bindValue(":fromSend", $from_send);
						$query->bindValue(":userId", $user_id);
						$query->execute();
						
						return $query->fetchAll(PDO::FETCH_ASSOC);
					}	
	}

	function saveNewMessage($params = array())
	{	
		if ($params) 
		{
				if (isRedis()) 
				{				
					$user_id = (int)$_SESSION['user']['id'];
					$to_send = $params["to_send"];
					$create_time = date('Y-m-d H:i:s');
					$firstKeyName = getRedis()->EXISTS("messages_$user_id/$to_send");
					$secondKeyName = getRedis()->EXISTS("messages_$to_send/$user_id");
					$long = strtotime($create_time);									
					
					if ($firstKeyName == true) 
					{
						$messagesCount = getRedis()->ZCOUNT("messages_$user_id/$to_send","-inf","+inf" );
						$hashesId = getRedis()->EXISTS("message_$user_id/$to_send:$messagesCount");
						if ($hashesId==true) 
						{
							$messagesCount = $messagesCount+1;
						}
						
						$fromFirstName = getRedis()->HGET("User:$user_id","first_name");
						$fromLastName = getRedis()->HGET("User:$user_id","last_name");
						$toFirstName = getRedis()->HGET("User:$to_send","first_name");
						$toLastName = getRedis()->HGET("User:$to_send","last_name");
						
						$messagesHash = getRedis()->HMSET("message_$user_id/$to_send:$messagesCount","id",$messagesCount,"message",$params["message"],"to_send",$params["to_send"],"from_send",$user_id,"create_time",date('Y-m-d H:i:s'),"to_first_name",$fromFirstName,"to_last_name",$fromLastName,"from_first_name",$toFirstName,"from_last_name",$toLastName);
						$getMessagesFromHashes= getRedis()->HGETALL("message_$user_id/$to_send:$messagesCount");
						$encode_message =json_encode($getMessagesFromHashes);
						$messagesSortedSets = getRedis()->ZADD("messages_$user_id/$to_send",$long,$encode_message);

					}
					elseif ($secondKeyName==true) 
					{
						$messagesCount = getRedis()->ZCOUNT("messages_$to_send/$user_id","-inf","+inf");
						$hashesId = getRedis()->EXISTS("message_$to_send/$user_id:$messagesCount");
						if ($hashesId==true) 
						{
							$messagesCount = $messagesCount+1;
						}	
						
						$fromFirstName = getRedis()->HGET("User:$user_id","first_name");
						$fromLastName = getRedis()->HGET("User:$user_id","last_name");
						$toFirstName = getRedis()->HGET("User:$to_send","first_name");
						$toLastName = getRedis()->HGET("User:$to_send","last_name");

						$messagesHash = getRedis()->HMSET("message_$to_send/$user_id:$messagesCount","id",$messagesCount,"message",$params["message"],"to_send",$params["to_send"],"from_send",$user_id,"create_time",date('Y-m-d H:i:s'),"to_first_name",$fromFirstName,"to_last_name",$fromLastName,"from_first_name",$toFirstName,"from_last_name",$toLastName);
						$getMessagesFromHashes= getRedis()->HGETALL("message_$to_send/$user_id:$messagesCount");
						$encode_message =json_encode($getMessagesFromHashes);
						$messagesSortedSets = getRedis()->ZADD("messages_$to_send/$user_id",$long,$encode_message);						
					}
					else
					{
						$messagesCount = getRedis()->ZCOUNT("messages_$to_send/$user_id","-inf","+inf");
						$hashesId = getRedis()->EXISTS("message_$to_send/$user_id:$messagesCount");
						if ($hashesId==true) 
						{
							$messagesCount = $messagesCount+1;
						}
						
						$fromFirstName = getRedis()->HGET("User:$user_id","first_name");
						$fromLastName = getRedis()->HGET("User:$user_id","last_name");
						$toFirstName = getRedis()->HGET("User:$to_send","first_name");
						$toLastName = getRedis()->HGET("User:$to_send","last_name");
						
						$messagesHash = getRedis()->HMSET("message_$user_id/$to_send:$messagesCount","id",$messagesCount,"message",$params["message"],"to_send",$params["to_send"],"from_send",$user_id,"create_time",date('Y-m-d H:i:s'),"to_first_name",$fromFirstName,"to_last_name",$fromLastName,"from_first_name",$toFirstName,"from_last_name",$toLastName);
						$getMessagesFromHashes= getRedis()->HGETALL("message_$user_id/$to_send:$messagesCount");
						$encode_message =json_encode($getMessagesFromHashes);
						$messagesSortedSets = getRedis()->ZADD("messages_$user_id/$to_send",$long,$encode_message);
					}
					return $getMessagesFromHashes;	
				}
				else
				{
					$user_id = $_SESSION['user']['id'];
					$query = getDatabase()->prepare('
					INSERT INTO messages
					(message, to_send, from_send) 
					VALUES 
					(:message, :to_send, :from_send)
					');
					$query->bindValue(":message", $params["message"]);
					$query->bindValue(":to_send", $params["to_send"]);
					$query->bindValue(":from_send", $user_id);
					$query->execute();
					$id = getDatabase()->lastInsertId();

					return getMessageById($id);
				}
		}

	}

	function getMessageById($id)
	{				

		$query = getDatabase()->prepare('
			SELECT 
				m.id,
				m.message,
				m.create_time,
				u.first_name AS to_first_name,
				u.last_name AS to_last_name,
				m.to_send,
				m.from_send
			FROM messages AS m 
			LEFT JOIN users AS u ON u.id = m.from_send
			WHERE m.id = :id
			ORDER BY m.create_time
		');
		$query->bindValue(":id", $id);
		$query->execute();

		return $query->fetch(PDO::FETCH_ASSOC);
					
	}
	function getLastMessagesByUser($params)
	{
		
		if (isRedis()) 
		{
			$user_id = $_SESSION['user']['id'];
			$to_send = $params['from_send'];
		 	$firstKeyName = getRedis()->EXISTS("messages_$user_id/$to_send");
			$secondKeyName = getRedis()->EXISTS("messages_$to_send/$user_id");
			$last_message_id = $params['last_message_id'];

			$to_send_messages_array = array();

			if ($firstKeyName == true) 
			{				
				$newMessages_count = getRedis()->ZCOUNT("messages_$user_id/$to_send","-inf","+inf");	
				$getAllMessages = getRedis()->ZRANGE("messages_$user_id/$to_send",(int)$last_message_id+1,(int)$newMessages_count);
				foreach ($getAllMessages as $getAllConnectionMessage) 
				{
					$decodes_to_send_message = json_decode($getAllConnectionMessage,true);
					array_push($to_send_messages_array, $decodes_to_send_message);
				}
				return $to_send_messages_array;
			}
			elseif ($secondKeyName == true) 
			{				
				$newMessages_count = getRedis()->ZCOUNT("messages_$to_send/$user_id","-inf","+inf");	
				$getAllMessages = getRedis()->ZRANGE("messages_$to_send/$user_id",(int)$last_message_id+1,(int)$newMessages_count);
				foreach ($getAllMessages as $getAllConnectionMessage) 
				{
					$decodes_to_send_message = json_decode($getAllConnectionMessage,true);
					array_push($to_send_messages_array, $decodes_to_send_message);
				}
				return $to_send_messages_array;
			}
			else
			{
				$user_id = $_SESSION['user']['id'];
				$query = getDatabase()->prepare('
					SELECT 
						m.id,
						m.message,
						m.create_time,
						u.first_name AS to_first_name,
						u.last_name AS to_last_name,
						m.to_send,
						m.from_send
					FROM messages AS m 
					LEFT JOIN users AS u ON u.id = m.from_send
					WHERE m.to_send = :to_send AND m.from_send = :from_send AND m.id > :last_message_id
					ORDER BY m.create_time
				');
				$query->bindValue(":to_send", $user_id);
				$query->bindValue(":from_send", $params['from_send']);
				$query->bindValue(":last_message_id", $params["last_message_id"]);
				$query->execute();

				return $query->fetchAll(PDO::FETCH_ASSOC);
			}
		} 	
		else
		{
			$user_id = $_SESSION['user']['id'];
			$query = getDatabase()->prepare('
				SELECT 
					m.id,
					m.message,
					m.create_time,
					u.first_name AS to_first_name,
					u.last_name AS to_last_name,
					m.to_send,
					m.from_send
				FROM messages AS m 
				LEFT JOIN users AS u ON u.id = m.from_send
				WHERE m.to_send = :to_send AND m.from_send = :from_send AND m.id > :last_message_id
				ORDER BY m.create_time
			');
			$query->bindValue(":to_send", $user_id);
			$query->bindValue(":from_send", $params['from_send']);
			$query->bindValue(":last_message_id", $params["last_message_id"]);
			$query->execute();

			return $query->fetchAll(PDO::FETCH_ASSOC);
		}
	}

	function CheckUser($params)
	{
		$email = $params['email'];
		$password = $params['password'];

		try 
		{
			$query = getDatabase()->prepare('
			SELECT * 
			FROM users 
			WHERE email = :email 
			AND password = :password
			');
			$query->bindValue(":email",$email);
			$query->bindValue(":password",$password);
			$query->execute();
		} 
		catch (Exception $e) 
		{
			var_dump($e);
		}
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	function GetUserInfoById($id)
	{
		$query = getDatabase()->prepare('
			SELECT first_name,last_name,email,id
			FROM users 
			WHERE id= :id
			');
			$query->bindValue(":id",$id);
			$query->execute();

		return $query->fetch(PDO::FETCH_ASSOC);
	}
//tik savo? nu tos zinites
	//tik savo parasytas? ai nu jo cj
	function deleteMessage($params){
		/*$getAllUserConnections= getRedis()->KEYS("messages_".$params['to_send']);

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
		getRedis()->flushall();*/
		//su redziu pats padarysi
		//veikia?nu lyg jo

		$user_id = $_SESSION['user']['id'];
		$query = getDatabase()->prepare('
			SELECT 
				m.*
			FROM messages AS m 
			WHERE m.id = :message_id AND m.from_send = :user_id
		');
		$query->bindValue(":user_id", $user_id);
		$query->bindValue(":message_id", $params['message_id']);
		$query->execute();

		$message = $query->fetch(PDO::FETCH_ASSOC);

		if ($message) {
			$query = getDatabase()->prepare('
				DELETE FROM `messages` WHERE id=:message_id
			');
			$query->bindValue(":message_id", $params['message_id']);
			$query->execute();
			return true;
		}

		//ftas budas nelabai paeis nes cia dar zinutes gali nebut mysql :D reiktu pirmiau viska surasyt is redis i mysql ir tada va taip ieskot ir istrint

		return false;
	}
