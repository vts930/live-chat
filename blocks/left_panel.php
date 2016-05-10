<div class="col-sm-4 col-md-3 sidebar">
  <div class="panel panel-default col-sm-12" style="padding:0 !important;" >
    <div class="panel-heading"><h4>Naujausi susirašinėjimai</h4></div>
    <?php $last_connections = getLastConnections() ?>

    <?php foreach($last_connections AS $connection): ?>
 
          
 
      <a href="index.php?to_send=<?php echo (int)$connection['to_send'] ?>">
        <div class="media">
          <div class="media-left media-top"></div>
          <div class="media-body">
            <h5 class="media-heading"><?php echo $connection["to_first_name"]." ".$connection["to_last_name"] ?></h5>
            <h6><?php echo $connection["message"] ?></h6>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>