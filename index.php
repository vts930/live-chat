<?php include_once("config.php")?>
<?php include_once("functions.php")?>
<?php if (isset($_SESSION['user'])):?>
  <?php $to_send_message = (isset($_GET["to_send"]) && (int)$_GET["to_send"] ? (int)$_GET["to_send"] : $_SESSION['user']['id']) ?>
  <html>
    <header>
      <!-- Latest compiled and minified CSS -->
      <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

      <!-- jQuery library -->
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>

      <!-- Latest compiled JavaScript -->
      <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
      <meta charset="utf8">
    </header>
    <body>
      <?php include_once("blocks/navbar.php"); ?>
      <?php include_once("blocks/left_panel.php"); ?>
      <?php include_once("blocks/center_panel.php"); ?>
      <?php include_once("blocks/right_panel.php"); ?>
    </body>
  </html>
<?php  else: 
    header("Location: login.php");
?>

<?php endif; ?> 