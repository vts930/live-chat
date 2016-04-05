<?php 
	include_once("config.php");
	include_once("functions.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<?php if (isset($_SESSION['error_message'])): ?>
		<span class="bg-danger"><?php echo $_SESSION['error_message']; ?> </span>
		<?php unset($_SESSION['error_message']); ?>
	<?php endif; ?>
	<form action="actions/login_action.php" method="POST">
			<input type="email" name="email">
			<input type="password" name="password">
			<button type="submit">Prisijungti</button>
	</form>	
</body>
</html>
