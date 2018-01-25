<?php
/*
script to get a users friendslist
*/

require('../../config/dbconfig.php');
require('../../config/modules.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

$username_request = clean_input($conn, 'username_request'); // Jimmie

if (!empty($username_request)) {
  $friends = return_friends($username_request);

  printjson($friends);
} else {
  return array('success' => 0);
}

function return_friends($username_request) {

  global $conn;

  // get by their usernames.

  $get = mysqli_query($conn, "SELECT friends_by_username FROM friends WHERE friends_username = '$username_request'");

  // check if the user has any users. If so, the rows should return 1,
  if ($get->num_rows > 0) {
    $users = mysqli_fetch_array($get);

    require_once('../status/online_players.php');

    $friends = get_online_players($users['friends_by_username']);

    // $users = explode(',', $users['friends_by_username']);
    return array('success' => 1, 'friends' => $friends);
  } else {
    return array('success' => 0, 'error' => 'You have no friends.');
  }



}

 ?>
