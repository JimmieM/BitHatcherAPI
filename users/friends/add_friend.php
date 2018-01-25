<?php
/*
function to add friend.
*/

require('../../config/dbconfig.php');
require('../../config/modules.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

$username_request = clean_input($conn, 'username_request'); // Jimmie
$username_add = clean_input($conn, 'username_add');

if ((!empty($username_request)) && (!empty($username_add))) {

  $add = add_friend($username_request, $username_add);

  printjson($add);
} else {
  printjson(array('success' => 0, 'error' => 'Empty usernames'));
}

function add_friend($username_request, $username_add) {

  global $conn;

  // get $username_requests player_id,

  // get $username_add player_id,

  $get_username_add_id = mysqli_query($conn, "SELECT player_id FROM users WHERE player_username = '$username_add'");

  $add_id = mysqli_fetch_array($get_username_add_id);

  $username_add_id = $add_id['player_id'];

  // get username_request ID
  $get_username_add_id = mysqli_query($conn, "SELECT player_id FROM users WHERE player_username = '$username_request'");

  $request_id = mysqli_fetch_array($get_username_add_id);

  $username_request_id = $request_id['player_id'];

  // double check if $username_add doesnt exists.
  $get = mysqli_query($conn, "SELECT friends_by_username, friends_by_user_id FROM friends WHERE friends_username = '$username_request'");

  // check if the user has any users. If so, the rows should return 1,
  if ($get->num_rows > 0) {
    $users = mysqli_fetch_array($get);

    $users_explode = explode(',', $users['friends_by_username']);
    $users_id_explode = explode(',', $users['friends_by_user_id']);

    $username_add_quotation = "'".$username_add."'";
    for ($i=0; $i < count($users_explode) ; $i++) {

      if ($username_add_quotation == $users_explode[$i]) {
        return array('success' => 0, 'error' => 'You already have this player as a friend.');
        break;
      }
    }

    $username_add = $users['friends_by_username'] . ",'" . $username_add."'";
    $username_add_by_id = $users['friends_by_user_id'] . ',' . $username_add_id;
    $query = sprintf('UPDATE friends SET friends_by_username = "%s", friends_by_user_id = "%s" WHERE friends_username = "%s"', $username_add, $username_add_by_id, $username_request);
    $update = mysqli_query($conn, $query);

    if ($update) {
      return array('success' => 1);
    }
    return array('success' => 0, 'error' => mysqli_error($conn), 'query' => $query);

  } else {
    // new row.
    $username_add = "'".$username_add."'";

    $query = "INSERT INTO friends (friends_username, friends_user_id, friends_by_username, friends_by_user_id) VALUES ('$username_request', $username_request_id, ''$username_add'', '$username_add_id')";

    $insert = mysqli_query($conn, $query);

      if ($insert) {
        return array('success' => 1);
        # code...
      } else {
        return array('success' => 0, 'error' => mysqli_error($conn), 'query' => $query);
      }
  }

}


 ?>
