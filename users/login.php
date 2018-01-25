<?php
/*
 Backend api to get $_POST Requests to verfiy login from Login.js in Ionic.
*/

  require('../config/dbconfig.php');
  require('../config/modules.php');
  $post = new json_post();

  $post->__construct(false);

  $conn = get_connection();

  $username = clean_input($conn, 'username');
  $password = clean_input($conn, 'password');

  $password = hash("sha256", $password);

  $verify = mysqli_query($conn, "SELECT player_username, player_password FROM users WHERE player_username = '$username' AND player_password = '$password'");

  if ($verify->num_rows == 0) {
    // no matches..
    $failure = array('success' => 0);
    $failure[] = array('username' => $username);
    echo json_encode($failure);
    die();
  } else {
    // match == True;
    $sth = mysqli_query($conn,"SELECT player_avatar, player_username, player_level, player_avatar FROM users WHERE player_username LIKE '$username' LIMIT 1");
    $rows = array();
    while($r = mysqli_fetch_assoc($sth)) {
        $success = array('success' => 1);
        $rows[] = $success;
        $rows[] = $r;
    }

    echo json_encode($rows);
  }
mysqli_close($conn);
 ?>
