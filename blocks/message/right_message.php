<div class="panel panel-default col-sm-7 message_block" data-message-id="<?php echo $message['id'] ?>" style="float:right; padding:0 !important; background-color:#033B8E; color:#fff; ">
  	<div class="panel-heading">	
		<strong><?php echo $message['to_first_name']." ".$message['to_last_name'] ?></strong>
		<input class="btn btn-xs btn-danger action-button" id="delete" style="float:right;text-align:initial; font-size: 11px;" type="button" data-message-id="<?php echo $message['id'] ?>" data-action="delete" value="X">
	</div>
	<div class="panel-body ">
		<span style="font-size: 11px;"><i><?php echo $message["create_time"] ?></i></span>
		<br>
		<span>
			<?php echo $message["message"] ?>
		</span>
	</div>
</div>