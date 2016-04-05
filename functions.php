<?php
	$db = new PDO('mysql:host=localhost;dbname=baigiamasis;charset=utf8', 'root', 'tarakonas');
	//$db = new PDO('mysql:host=localhost;dbname=u606861065_bbd;charset=utf8', 'u606861065_bbd', 'vgtubakalauras');


	function getLastConnections(){
		global $db;
		global $redis;
		/*$query = $db->prepare('
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
		$query = $db->prepare('
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
			global $db;
			$user_id = $_SESSION['user']['id'];
			$query = $db->prepare('
				SELECT * FROM users WHERE id != :userId LIMIT 100
			');
			$query->bindValue(":userId", $user_id);
			$query->execute();

			return $query->fetchAll(PDO::FETCH_ASSOC);
			
	}

	function getAllMessagesByUser($from_send){
			global $UseRedis;

				
				if (isset($UseRedis)) 
					{
						global $redis;
							$user_id = $_SESSION['user']['id'];
							$messages=$redis->hget("messages",$user_id);
							//
							return $messages;

					}
				else
					{
										
						global $db;
						$user_id = $_SESSION['user']['id'];
						$query = $db->prepare('
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
			global $UseRedis;
				if (isset($UseRedis)) 
					{
						global $redis;
						$user_id = $_SESSION['user']['id'];
						$test =json_encode(array(
							'messageid' => 1, 
							'message' =>  $params["message"],
							'to_send' => $params["to_send"],
							'from_send' => $user_id,							
							'create_time' => '2012-12-12'
							));
						//var_dump($test);
						$redis->hset("messages",$user_id,$test);
						//$id = $db->lastInsertId();
						

						return getMessageById($user_id);
					}
				else
					{
						global $db;

						$user_id = $_SESSION['user']['id'];

						$query = $db->prepare('
							INSERT INTO messages
							(message, to_send, from_send) 
							VALUES 
							(:message, :to_send, :from_send)
						');
						$query->bindValue(":message", $params["message"]);
						$query->bindValue(":to_send", $params["to_send"]);
						$query->bindValue(":from_send", $user_id);
						$query->execute();

						$id = $db->lastInsertId();

						return getMessageById($id);

					}
		}

	}

	function getMessageById($id){
		global $UseRedis;
				if (isset($UseRedis)) 
					{
						global $redis;
						
							$messages=$redis->hget("messages",(int)$id);
							
							
							return $messages;
						
					}
					else
					{
						global $db;

						$query = $db->prepare('
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
		global $db;

		$user_id = $_SESSION['user']['id'];

		$query = $db->prepare('
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
		global $db;

		$email = $params['email'];
		$password = $params['password'];

		try {
			$query = $db->prepare('
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

		global $db;
		$query = $db->prepare('
			SELECT first_name,last_name,email,id
			FROM users 
			WHERE id= :id
			');
			$query->bindValue(":id",$id);
			$query->execute();

		return $query->fetch(PDO::FETCH_ASSOC);
	}