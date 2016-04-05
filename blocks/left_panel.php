<div class="col-sm-4 col-md-3 sidebar">
  <div class="panel panel-default col-sm-12" style="padding:0 !important;" >
    <div class="panel-heading"><h4>Naujausi susirašinėjimai</h4></div>
    <?php $last_connections = getLastConnections() ?>
    <?php foreach($last_connections AS $connection): ?>
      <a href="index.php?to_send=<?php echo $connection['id'] ?>">
        <div class="media">
          <div class="media-left media-top">
            <img style="width:40px; height:auto;" class="media-object" src='images/<?php echo $connection["avatar"] ?>' alt="...">
          </div>
          <div class="media-body">
            <h5 class="media-heading"><?php echo $connection["first_name"]." ".$connection["last_name"] ?></h5>
            <h6><?php echo $connection["last_message"] ?></h6>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>