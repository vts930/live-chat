<div class="panel panel-default col-sm-7 message_block" data-message-id="<?php echo $message['id'] ?>" style="float:left; padding:0 !important; background-color:#044BB5; color:#fff;" >
	<div class="panel-heading">		
		<strong><?php echo $message['to_first_name']." ".$message['to_last_name'] ?></strong>
	</div>
	<div class="panel-body ">
		<span style="font-size: 11px;"><i><?php echo $message["create_time"]; ?></i></span>
		<br>
		<span>
			<?php echo $message["message"]; ?>
		</span>
	</div>
</div>