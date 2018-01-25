<?php


// when this function is called, the user should be initated as being online.
function is_online($player_id) {

  require('../../config/dbconfig.php');
  require('../../config/modules.php');

  $conn = get_connection();

  $get = mysqli_query($conn, "SELECT player_record_latest_date, player_username FROM users WHERE player_id = $player_id");

  if ($get->num_rows > 0) {
    $record = mysqli_fetch_array($get);

    $latest_update = $record['player_record_latest_date'];
    $now = now();

    $diff = $now - $latest_update;

    if ($diff <= 60) { // 60 secs.
      return true;
    }

    return false;
  }
}


 ?>
