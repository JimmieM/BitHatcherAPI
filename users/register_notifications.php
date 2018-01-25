<?php
require('../config/dbconfig.php');
require('../config/modules.php');

$conn = get_connection();

$username = clean_input($conn, 'username');
$device_token = clean_input($conn, 'deviceToken');

$update = update_device_token($username, $device_token);

echo json_encode(array('success' => $update));

function update_device_token($username, $device_token) {

  global $conn;

  $update = mysqli_query($conn, "UPDATE users SET player_ios_device_token = '$device_token' WHERE player_username = '$username'");

  if ($update) {
    return true;
  }
  return false;
}

function reset_token() {
  global $conn;
  $return = array();
  $return['success'] = false;
  $return['failed_queries'] = array();
  $return['error_log'] = null;

  // if another user has the token bound to account
  $search = mysqli_query($conn, "SELECT player_ios_device_token, player_username FROM users WHERE username NOT LIKE '$username'");

  //$instances = array();
  if ($search->num_rows > 0) {
    while ($row = mysqli_fetch_array($search)) {
      //$instances[] = $row;

      $user = $row['player_username'];

      $reset = mysqli_query($conn, "UPDATE users SET player_ios_device_token = NULL WHERE player_username = '$user'");

      if (!$reset) {
        $return['failed_queries'][] = $user . ' device token failed! Token: ' . $row['player_ios_device_token'];
      }
    }

    if (!empty($return['failed_queries'])) {

    }

    $return['success'] = true;
    return $return;
  } else {
    $return['success'] = true;
    return $return;
  }
}


 ?>
