<div class="col-sm-4 col-md-3 sidebar" style=" max-height:500px; overflow: scroll;">
  <div class="panel panel-default col-sm-12" style="padding:0 !important;" >
    <div class="panel-heading"><h4>Prisijungę vartotojai</h4></div>
    <?php //$start =microtime(true); ?>
     
      <?php //$endtime = microtime(true);?>
      <?php   //$diff = $endtime-$start;
      //echo  $diff;  ?>
     <?php $getAllExistsUsers= getRedis()->KEYS("User:*"); ?>
      <?php if ((int)$getAllExistsUsers>0): ?>
      <?php foreach($getAllExistsUsers AS $user):?>
      <?php 
      echo "redis";
          $first_name = getRedis()->HGET($user,"first_name");
          $last_name = getRedis()->HGET($user,"last_name");
          $avatar = getRedis()->HGET($user,"avatar");
          $id = getRedis()->HGET($user,"id");
      ?>
        <div class="media">
          <div class="media-left media-top">
            <img style="width:40px; height:auto;" class="media-object" src='images/<?php echo $avatar ?>' alt="...">
          </div>
          <div class="media-body">
            <h5 class="media-heading"><?php echo $first_name." ".$last_name?></h5>
            <a class="btn btn-primary button" href="index.php?to_send=<?php echo (int)$id ?>" style="font-size:10pt;padding-left: 6px;padding-right: 6px;padding-bottom: 3px;padding-top: 3px;">
              Rašyti žinutę
            </a>
          </div>
        </div>
      <?php endforeach; ?>
      <?php else: ?>
       <?php $all_users = getAllUsers() ?> 
      <?php foreach($all_users AS $user):?>
      <?php 
        echo "tikrai";
        $id = (int)$user['id'];
        echo
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        $password = $user['password'];
        $email = $user['email'];
        $avatar = $user['avatar'];
        $users = getRedis()->HMSET("User:$id","id",$id,"first_name",$first_name,"last_name",$last_name,"password",$password,"email",$email,"avatar",$avatar);
      ?>
        <div class="media">
          <div class="media-left media-top">
            <img style="width:40px; height:auto;" class="media-object" src='images/<?php echo $user["avatar"] ?>' alt="...">
          </div>
          <div class="media-body">
            <h5 class="media-heading"><?php echo $user["first_name"]." ".$user["last_name"] ?></h5>
            <a class="btn btn-primary button" href="index.php?to_send=<?php echo $user['id'] ?>" style="font-size:10pt;padding-left: 6px;padding-right: 6px;padding-bottom: 3px;padding-top: 3px;">
              Rašyti žinutę
            </a>
          </div>
        </div>
      <?php endforeach; ?>
<?php endif ?>
  </div>
</div>