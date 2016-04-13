<div class="col-sm-4 col-md-3 sidebar">
  <div class="panel panel-default col-sm-12" style="padding:0 !important;" >
    <div class="panel-heading"><h4>Naujausi susirašinėjimai</h4></div>
    <?php $last_connections = getLastConnections() ?>

    <?php foreach($last_connections AS $connection): ?>
    <?php        
          if (isRedis()) {
          $connectionsFromSets = getRedis()->ZRANGE($connection,"0","1");
          foreach ($connectionsFromSets as $connectionFromSet) {
              $connection = json_decode($connectionFromSet,true);
         }
          }
          
      ?>

      <a href="index.php?to_send=<?php echo (int)$connection['to_send'] ?>">
        <div class="media">
          <div class="media-left media-top">
            
          </div>
          <div class="media-body">
            <h5 class="media-heading"><?php echo $connection["from_first_name"]." ".$connection["from_last_name"] ?><span class="badge">3</span></h5>
            <h6><?php echo $connection["message"] ?></h6>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>