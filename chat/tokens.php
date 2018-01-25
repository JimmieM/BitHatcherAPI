<?php

function new_chat_token($conn, $player1, $player2) {
  //$return = array();

  // check if chat_inbetweens table has a connection between both players.

  $new_token = new_token();
  $find_connection = mysqli_query($conn,"SELECT token FROM chat_betweens
    WHERE
    (player1 = '$player1' AND player2 = '$player2')
    OR
    (player1 = '$player2' AND player2 = '$player1')");
  if ($find_connection->num_rows > 0) {

      if (mysqli_query($conn, "UPDATE chat_betweens
        SET token = '$new_token'
        WHERE (player1 = '$player1' AND player2 = '$player2')
        OR
        (player1 = '$player2' AND player2 = '$player1')")) {
        return ['success' => 1, 'token' => $new_token];
      } else {
        return ['success' => 0, 'error' => mysqli_error($conn) . mysqli_connect_errno()];
      }
  } else {
    // create connection.

    if (mysqli_query($conn, "INSERT INTO chat_betweens (player1, player2, token)
    VALUES
    ('$player1', '$player2', '$new_token')")) {
      return ['success' => 1, 'token' => $new_token];
    } else {
      return ['success' => 0, 'error' => mysqli_error($conn) . mysqli_connect_errno()];
    }
  }

}

function validate_token($conn, $player1, $player2, $given_token) {
  $qry = "SELECT token FROM chat_betweens
    WHERE
    (player1 = '$player1' AND player2 = '$player2')
    OR
    (player1 = '$player2' AND player2 = '$player1')";
  $x = mysqli_query($conn,$qry);
  if ($x->num_rows > 0) {
      $current_token = mysqli_fetch_array($x);
      $current_token = $current_token['token'];

      if ($given_token === $current_token) {
        return ['is_the_same' => 1, 'current_token' => $current_token];
      }
      return ['is_the_same' => 0, 'current_token' => $current_token];
    }
}

function new_token($length = 28) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}


 ?>
