<?php


	function getLastConnections(){
		
		/*$query = getDatabase()->prepare('
			SELECT 
				uc.first_user_id, 
				uc.second_user_id, 
				u.user_name,
				u.user_lastname,
				u.user_image 
			FROM Users_connections AS uc
			LEFT JOIN Users AS u ON uc.second_user_id = u.user_id 
			WHERE uc.first_user_id = :userId
		');
		$query->bindValue(":userId", $user_id);
		$query->execute();

		return $query->fetchAll(PDO::FETCH_ASSOC);*/

		/*SELECT 
				m.id,
				m.message,
				m.create_time,
				u.first_name AS to_first_name,
				u.last_name AS to_last_name,
				m.to_send,
				m.from_send
			FROM messages AS m 
			LEFT JOIN users AS u ON u.id = m.from_send
			WHERE m.to_send = :userId OR m.from_send = :userId
			GROUP BY u.id
			ORDER BY m.create_time*/


		$user_id = $_SESSION['user']['id'];
		$query = getDatabase()->prepare('
			SELECT
				u.id,
				u.first_name,
				u.last_name,
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
				) AS last_message
			FROM users AS u
			WHERE u.id IN(SELECT IF(m.to_send != :userId, m.to_send, m.from_send) FROM messages AS m WHERE m.to_send = :userId OR m.from_send = :userId GROUP BY m.to_send, m.from_send ORDER BY m.create_time DESC) AND u.id != :userId
		');
		$query->bindValue(":userId", $user_id);
		$query->execute();
		
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	function getAllUsers(){
			
			$user_id = $_SESSION['user']['id'];
			$query = getDatabase()->prepare('
				SELECT * FROM users WHERE id != :userId LIMIT 100
			');
			$query->bindValue(":userId", $user_id);
			$query->execute();

			return $query->fetchAll(PDO::FETCH_ASSOC);
			
	}

	function getAllMessagesByUser($from_send){
				
				if (isRedis()) 
					{						
						$user_id = $_SESSION['user']['id'];
						$from=$user_id-1;
						$to=$user_id+1;						
						$from2=$from_send -1 ;	
						$to2=$from_send +1 ;
						$to_send_messages = getRedis()->ZRANGEBYSCORE("messages","($from","($to");
						$to_send_messages2 = getRedis()->ZRANGEBYSCORE("messages","($from2","($to2");
						$to_send_messages_array = array();
						foreach ($to_send_messages as $to_send_message) 
						{
							$decodes_to_send_message = json_decode($to_send_message,true);
							array_push($to_send_messages_array, $decodes_to_send_message);				
						}
						foreach ($to_send_messages2 as $to_send_message) 
						{
							$decodes_to_send_message = json_decode($to_send_message,true);					
							array_push($to_send_messages_array, $decodes_to_send_message);				
						}
						//var_dump($to_send_messages_array);
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
						
						$user_id = $_SESSION['user']['id'];
						$array = array(
							'message' =>  $params["message"],
							'to_send' => $params["to_send"],
							'from_send' => $user_id,							
							'create_time' => date('Y-m-d H:i:s'),
							"id" => getRedis()->ZCOUNT("messages","-inf","+inf")+1	
							);
						$encode_message =json_encode($array);
						$message = $params["message"];
						$to_send = $params["to_send"];
						$from_send = $user_id;
						$create_time = date('Y-m-d H:i:s');

						//$redis->LPUSH("messages:$user_id",$test);
						
						var_dump($array);
						getRedis()->ZADD("messages",$params["to_send"],$encode_message);
						
						/*$redis->HMSET("messages", "message $message" ,"to_send $to_send", "from_send $from_send", "create_time $create_time");*/
						return $array;
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

	function getMessageById($id){
		
				if (isRedis()) 
					{
						
						
						$user_id = $_SESSION['user']['id'];
						$messages=getRedis()->ZRANGE("messages",0,-1);
						$testarray=array();
						foreach ($messages as $messagesdecode) {
							$decodemessages = json_decode($messagesdecode,true);
							array_push($testarray, $decodemessages);							
						}
						
						return $testarray;				
					}
					else
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
	}

	function getLastMessagesByUser($params){
		
		if (isRedis()) {
		 		# code...
		 	} 	


		

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

		return $query->fetchAll(PDO::FETCH_ASSOC);;
	}

	function CheckUser($params){
		$email = $params['email'];
		$password = $params['password'];

		try {
			$query = getDatabase()->prepare('
			SELECT * 
			FROM users 
			WHERE email = :email 
			AND password = :password
			');
			$query->bindValue(":email",$email);
			$query->bindValue(":password",$password);
			$query->execute();
		} catch (Exception $e) {
			var_dump($e);
		}
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	function GetUserInfoById($id){
		$query = getDatabase()->prepare('
			SELECT first_name,last_name,email,id
			FROM users 
			WHERE id= :id
			');
			$query->bindValue(":id",$id);
			$query->execute();

		return $query->fetch(PDO::FETCH_ASSOC);
	}
