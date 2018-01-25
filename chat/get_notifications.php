<?php
// when this function is called, the user should be initated as being online.
// called FROM userInformation.php also a POST


require_once('../config/dbconfig.php');
require_once('../config/modules.php');


$conn = get_connection();

$post = new json_post();

$post->__construct(false);

$username_request = clean_input($conn, 'username_request'); // user logged in that r requesting the chat.


if (!empty($username_request)) {
  $notifications = get_all_notifications($username_request);

  echo json_encode($notifications);
}


function get_all_notifications($username_request) {

  $conn = get_connection();

  $get = mysqli_query($conn, "SELECT player_username FROM users WHERE player_username NOT LIKE '$username_request'");

  $notifications = 0;

  if ($get->num_rows > 0) {

    while ($record = mysqli_fetch_array($get)) {
      $username = $record['player_username'];

      $amount = get_chat_notifications($conn, $username_request, $username);

      $notifications += $amount;
    }

    return ['notifications' => $notifications];

  }
}

function get_chat_notifications($conn, $username_request, $player2) {
  // store messages FROM requester

  $notifications = 0;
  $string1 = "SELECT count(*) as unseen_chats FROM chat WHERE to_username = '$username_request' AND from_username = '$player2' AND seen_by_to = 0";
  $unseen = mysqli_query($conn, $string1);

  $rows = mysqli_fetch_assoc($unseen);

  $notifications = (int)$rows['unseen_chats'];

  if ($notifications > 0) {
    $notifications = 1;
  }


  return $notifications;
}


 ?>
