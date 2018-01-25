<?php
// when this function is called, the user should be initated as being online.

if (($_SERVER['REQUEST_METHOD'] === 'POST')) {

  require_once('../../config/dbconfig.php');
  require_once('../../config/modules.php');

  $conn = get_connection();

  $post = new json_post();

  $post->__construct(false);
  if (isset($_POST['get_all_users'])) {
    $players = get_online_players();

    echo json_encode($players);
  }

}


function get_online_players($user_array = null) {
  global $conn;

  $username_request = clean_input($conn, 'username_request');

  if (empty($user_array)) {
    $query = "SELECT player_username, player_id, player_level,player_avatar, player_record_latest_date, player_username FROM users WHERE player_username NOT LIKE '$username_request'";

  } else {
    $query = "SELECT player_username, player_id, player_level,player_avatar, player_record_latest_date, player_username FROM users WHERE player_username in ($user_array)";
  }

  $get = mysqli_query($conn, $query);

  $online_players = array();

  if ($get->num_rows > 0) {

    while ($record = mysqli_fetch_array($get)) {
      $latest_update = strtotime($record['player_record_latest_date']);
      $username = $record['player_username'];
      $id = $record['player_id'];
      $level = $record['player_level'];
      $avatar = $record['player_avatar'];
      $now = strtotime(now());

      $notifications = get_chat_notifications($conn, $username_request, $username);


      $diff = $now - $latest_update;

      if ($username === 'BitHatcher' or $username == 'Alex') {
        $diff = 60;
      }

      if ($diff <= 60) { // 60 secs.
        $online_players[] = ['username' => $username, 'id' => $id, 'level' => $level, 'avatar' => $avatar, 'is_online' => 1, 'chat_notifications' => $notifications];
      } else {
        $online_players[] = ['username' => $username, 'id' => $id, 'level' => $level, 'avatar' => $avatar, 'is_online' => 0, 'chat_notifications' => $notifications];
      }
    }

    return $online_players;

  }
}

function get_chat_notifications($conn, $username_request, $player2) {
  // store messages FROM requester
  $string1 = "SELECT count(*) as unseen_chats FROM chat WHERE to_username = '$username_request' AND from_username = '$player2' AND seen_by_to = 0";
  $unseen = mysqli_query($conn, $string1);

  $notifications = 0;

  $rows = mysqli_fetch_assoc($unseen);

  $notifications = (int)$rows['unseen_chats'];


  return $notifications;
}


 ?>
