<?php

// first function to be initated.


require_once('../config/dbconfig.php');
require_once('../config/modules.php');

$conn = get_connection();

$post = new json_post();

$post->__construct(false);

$username_request = clean_input($conn, 'username_request'); // user logged in that r requesting the chat.
$player2 = clean_input($conn, 'player2');
$current_token = clean_input($conn, 'chat_token');
$amount = clean_input($conn, 'amount');

;
if ((!empty($username_request)) && (!empty($player2))) {
  $chat = load_chat($conn, $current_token, $username_request, $player2, true, $amount);

  printjson($chat, true);
}

function load_chat($conn, $token_received, $username_request, $player2, $by_pass_token = false, $amount) {

    // check tokens between players.
    require_once('tokens.php');
    $is_the_same = validate_token($conn, $username_request, $player2, $token_received);

    $current_token = $is_the_same['current_token'];
    if ($is_the_same['is_the_same'] == 1) { // if not the same.
      if (!$by_pass_token) {
        return array('valid_token' => 1, 'success' => 1);
      }

    }
  $arr_from = array();
  $arr_to = array();

  // get messages query
  $string1 = "SELECT * FROM chat
  WHERE
  (from_username = '$username_request' AND to_username = '$player2')
  OR
  (to_username = '$username_request' AND from_username = '$player2')
  ORDER BY id DESC
  LIMIT $amount";

  $all_chats = mysqli_query($conn, $string1);

  $username_request_avatar = null;
  $player2_avatar = null;

  $notifications = 0;

  if ($all_chats->num_rows > 0) {
    while($chats = mysqli_fetch_array($all_chats)) {
      $from = $chats['from_username'];

      if ($username_request_avatar === null) {
        $g_avatar = mysqli_query($conn, "SELECT player_avatar FROM users WHERE player_username = '$from'");
        $row = mysqli_fetch_array($g_avatar);

        $username_request_avatar = $row['player_avatar'];
      } else if($player2_avatar === null) {
        $g_avatar = mysqli_query($conn, "SELECT player_avatar FROM users WHERE player_username = '$from'");
        $row = mysqli_fetch_array($g_avatar);

        $player2_avatar = $row['player_avatar'];
      }

      if ($from === $username_request) {
        $avatar = $username_request_avatar;
      } else {
        $avatar = $player2_avatar;
      }

      mysqli_query($conn, "UPDATE chat SET seen_by_to = 1 WHERE to_username = '$username_request' AND from_username = '$player2' AND seen_by_to = 0");


      $arr_from[] = array_merge($chats, ['player_avatar' => $avatar]);
    }
  }

  return array_merge(['chats' => $arr_from, 'valid_token' => 0, 'current_token' => $current_token, 'success' => 1]);
}

 ?>
