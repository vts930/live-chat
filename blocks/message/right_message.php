<div class="panel panel-default col-sm-7 message_block" data-message-id="<?php echo $message['id'] ?>" style="float:right; padding:0 !important; background-color:#033B8E; color:#fff; ">
  	<div class="panel-heading">
		
			<?php 
			echo $message['to_first_name']." ".$message['to_last_name'] ?>
		</h6> 
	</div>
	<div class="panel-body ">
		<span><i><?php echo $message["create_time"] ?></i></span>
		<br>
		<span>
			<?php echo $message["message"] ?>
		</span>
	</div>
</div>