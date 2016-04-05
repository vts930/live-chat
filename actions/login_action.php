	<?php 
	include_once("../config.php");
	include_once("../functions.php");

		if(isset($_POST["email"],$_POST["password"])):
			$email = trim(strip_tags($_POST["email"]));
			$password = trim(strip_tags($_POST["password"]));

			$user = CheckUser(array('email' => $email,'password'=>$password));
			if ($user) {
				$_SESSION["user"]=$user;
				header('Location: ../index.php');
			}
			else
			{
				$_SESSION["error_message"]='ERROR';
				header('Location: '.$_SERVER['HTTP_REFERER']);
			}
		else:
			$_SESSION["error_message"]='ERROR';
			header('Location: '.$_SERVER['HTTP_REFERER']);
		endif;