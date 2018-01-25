<?php
require('../config/dbconfig.php');
require('../config/modules.php');
$post = new json_post();

$post->__construct(false);

$conn = get_connection();

$username = clean_input($conn, 'username');
$email = clean_input($conn, 'email');
$password = clean_input($conn, 'password');

if (!empty($username) && !empty($email) && !empty($password)) {
  find_user($username, $email, $password);
}

function find_user($username, $email) {
  global $conn;
  $return = array();
  $return['success'] = false;
  $return['error_log'] = null;

  $query = mysqli_query($conn, "SELECT player_ID FROM users WHERE player_username = '$username' AND player_email = $email");

  if ($query->num_rows > 1) {
    $return['error_log'] = 'Multiple users were found!';
    return send_email($email);
  } else if($query->num_rows === 1) {
    //
  } else {
    # code...
  }
}

function send_email($email) {

}

 ?>
