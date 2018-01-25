<?php

/*

script to return data when user request to view someones profile.

*/

require('../config/dbconfig.php');
require('../config/modules.php');
require('../config/translators.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

try {

  // id to be fetched
  $username_get = clean_input($conn, 'username_get');
  $player_request = clean_input($conn, 'username_request');

  // return array of all foodtypes
  $foodtypes = num_foods();

  $fetch_food = array();
  foreach ($foodtypes as $food) {
    $return_food = food_translator($food);

    $fetch_food[] = "player_" . $return_food;
  }

  $types = implode(', ', $fetch_food);

} catch (Exception $e) {
  /// idk
}


$qry = mysqli_query($conn, "SELECT player_username, player_account_created, player_record_latest_date, player_ID, player_level, player_avatar, $types, player_account_created, player_experience, player_bitfunds FROM users WHERE player_username = '$username_get' LIMIT 1");

$arr_ = array();
if ($qry->num_rows > 0) {
  $rows = mysqli_fetch_array($qry);

  $id = $rows['player_ID'];
  $account_created = $rows['player_account_created'];
  $username = $username_get;
  $latest_date = $rows['player_record_start_date'];

  $datetime1 = new DateTime($account_created);
  $datetime2 = new DateTime($latest_date);
  $interval = $datetime1->diff($datetime2);
  $played_time =  $interval->format('%h')." Hours ".$interval->format('%i')." Minutes";

  $is_online = is_online($id);
  $achievements = achievements($id);

  if ($player_request !== $username_get) {

    $is_friend = is_friend($player_request, $id);
  }

  $pets = mysqli_query($conn, "SELECT count(*) as total FROM projects WHERE player1 = '$username' OR player2 = '$username'");

  $x = mysqli_fetch_array($pets);

  $arr_[] = array_merge($rows, ['achievements' => $achievements, 'player_pets_owned' => $x['total'], 'player_gameplay_record' => $played_time, 'player_is_online' => $is_online, 'player_is_a_friend' => $is_friend['is_friend']]);

  printjson(array_merge(['success' => 1], $arr_));
} else {
  printjson(array('success' => 0, 'error' => 'Invalid username'));
}

function is_friend($player_request, $player_id) {
  global $conn;
  $get = mysqli_query($conn, "SELECT friends_by_user_id FROM friends WHERE friends_username = '$player_request'");

  // check if the user has any users. If so, the rows should return 1,
  if ($get->num_rows > 0) {
    $users = mysqli_fetch_array($get);
    $users = explode(',', $users['friends_by_user_id']);

    for ($i=0; $i < count($users) ; $i++) {
      if ($player_id == (int)$users[$i]) {
        return array('success' => 1, 'is_friend' => true);
        break;
      }
    }

    return array('success' => 1, 'is_friend' => false);
  } else {
    return array('success' => 1, 'is_friend' => false);
  }
}

function is_online($player_id) {
  $conn = get_connection();

  $get = mysqli_query($conn, "SELECT player_record_latest_date, player_username FROM users WHERE player_id = $player_id");

  if ($get->num_rows > 0) {
    $record = mysqli_fetch_array($get);

    $latest_update = strtotime($record['player_record_latest_date']);
    $now = strtotime(now());

    $diff = $now - $latest_update;

    if ($diff <= 60) {
      return true;
    }

    return false;
  }
}

function achievements($player_id) {
  global $conn;
  $qry = mysqli_query($conn, "SELECT player_achievements FROM users WHERE player_id = $player_id LIMIT 1");

  if ($qry->num_rows > 0) {
    $rows = mysqli_fetch_array($qry);

    $player_achievements = $rows['player_achievements'];

    if (!empty($player_achievements)) {
      return array('success' => 1, 'achievements' => $player_achievements, 'empty' => 0);
    }

    return array('success' => 1, 'empty' => 1);

  }

  return array('success' => 0, 'error_log' => "Could not find user by ID");
}


 ?>
