<?php

try {
  $_POST = json_decode(file_get_contents('php://input'), true);
  header('Content-Type: application/json');

  require('../config/dbconfig.php');
  require('../config/translators.php');
  require('../config/modules.php');
  require('../chat/get_notifications.php');

  $conn = get_connection();

  $username = mysqli_real_escape_string($conn,$_POST['username']);

  // return array of all foodtypes
  $foodtypes = num_foods();

  $fetch_food = array();
  foreach ($foodtypes as $food) {
    $return_food = food_translator($food);

    $fetch_food[] = "player_" . $return_food;
  }
  $types = implode(', ', $fetch_food);

} catch (Exception $e) {
  echo json_encode(array('error_log' => $e));
  die();
}

$qry = mysqli_query($conn, "SELECT
  player_avatar,
  player_level,
  player_ID,
  player_email,
  player_bitfunds,
  player_experience,
  player_experience_range,
  $types
  FROM users WHERE player_username LIKE '$username' LIMIT 1");

$rows = array();
while($row = mysqli_fetch_array($qry)) {
  // set new record
  $now = now();

  $all_notifications = get_all_notifications($username);

  $record_seconds = $row['player_record_seconds'];
  $record_date = $row['player_record_latest_date'];
  $record_start_date = $row['player_record_start_date'];

  $row['player_experience_bar'] = (int)$row['player_experience'] / (int)$row['player_experience_range'];
  // create first record.
  if ($record_date === 0 || $record_start_date === null) {
    $record_date = $now;

    $apply = mysqli_query($conn, "UPDATE users SET player_record_latest_date = '$record_date' WHERE player_username = '$username'");

  } else {
    // evaluate latest record into seconds and apply to second col.

    $record_seconds = $now - $record_date;
  }

  // define variables
  $rows[] = array_merge($row, $all_notifications);
}

echo json_encode($rows);

mysqli_close($conn);


 ?>
