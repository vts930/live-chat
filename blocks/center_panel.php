<div class="panel panel-default col-sm-6" style="padding:0 !important;" >
  <div class="panel-heading"><h4><?php echo (isset($_GET['to_send']) ? GetUserInfoById($_GET['to_send'])['first_name'] : $_SESSION['user']['first_name']); ?></h4></div>
  <div id="message_block" class="panel panel-default col-sm-12" style="min-height:350px;max-height:400px;padding-top:20px; padding-bottom:20px; margin-bottom:0 !important; border:none!important;  overflow:scroll;">  
    <?php $messages = getAllMessagesByUser($to_send_message) 

    ?>
    <?php  foreach ($messages as $message): var_dump($message);?>
      <?php if ($message["from_send"] == $_SESSION['user']['id']): ?>
        <?php include("message/right_message.php") ?>
      <?php else: ?>
        <?php include("message/left_message.php") ?>
      <?php endif ?>
    <?php endforeach ?>
  </div>

  <form id="new_message_form" class="col-sm-12" style="background-color:#033B8E; margin-top:20px; padding-bottom:20px;">
    <textarea class="form-control " id="message" name="message" rows="4" style="margin-top:10px;" placeholder="Message"></textarea> 
    <input type="hidden" name="to_send" value="<?php echo $to_send_message ?>" />
    <button type="button" id="send" class="btn btn-default">Siųsti žinutę</button>
  </form>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $(document).on('click', '#send', function(event) {
      event.preventDefault();
      $.ajax({
        url: 'actions/new_message_action.php',
        type: 'POST',
        data: $("#new_message_form").serialize(),
      })
      .done(function($data) {
        if ($.trim($data) != "") {
             $("#message_block").append($data);
             $("#message").val("");
             $("#message_block").animate({ scrollTop: $('#message_block')[0].scrollHeight }, "slow");

        }
      });
    });
    var last_message_id = $('.message_block').last().data("message-id");
    setInterval(function(){
      $.ajax({
        url: 'actions/get_new_message_action.php',
        type: 'POST',
        data: {"last_message_id": last_message_id, "from_send": <?php echo $to_send_message ?>},
      })
      .done(function($data) {
        if ($.trim($data) != "") {
          $("#message_block").append($data);
          last_message_id = $('.message_block').last().data("message-id");
           $("#message_block").animate({ scrollTop: $('#message_block')[0].scrollHeight }, "slow");

        }
      });
    }, 1000);
  });
</script>