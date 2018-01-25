<?php
/*
  action only available by battle creator, aka player1.

*/

require('../config/modules.php');
require('../config/dbconfig.php');
require('../config/translators.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

$username_request = clean_input($conn, 'username_request');
$battle_id = clean_input($conn, 'battle_id');

$query = mysqli_query($conn, "SELECT battle_player1 FROM battles WHERE battle_id = $battle_id LIMIT 1");

if ($query->num_rows > 0) {
  $row = mysqli_fetch_array($query);

  // double check if the player1 is actually requesting this action.
  // if its not player1, somethin gis wrong within the app..
  if ($row['battle_player1'] === $username_request) {
    $delete = mysqli_query($conn, "UPDATE battles SET battle_deleted = 1 WHERE battle_id = $battle_id AND battle_player1 = '$username_request'");
    if ($delete) {
      printjson(array('success' => 1));
    } else {
      printjson(array('success' => 0, 'error_log' => mysqli_error($conn)));
    }
  } else {
    printjson(array('success' => 0, 'error_log' => "Battle couldn't be deleted since you're not the creator."));
  }
}

 ?>
