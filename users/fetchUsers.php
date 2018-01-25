<?php


$_POST = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');

require ('../config/dbconfig.php');

$conn = get_connection();

if (isset($_POST['token']) && (isset($_POST['username_request']))) {

  if (isset($_POST['token'])  == '123') {

    $username_request = mysqli_real_escape_string($conn, $_POST['username_request']);


    $fetch = mysqli_query($conn, "SELECT player_username FROM users WHERE player_username NOT LIKE '$username_request'");

    $users = array();
    while ($usernames = mysqli_fetch_array($fetch)) {
      $users[] = $usernames['player_username'];

      // merge user arrays.

    }

    // insert into a new DB table called requests.
    $insert = mysqli_query($conn, "INSERT INTO hatch_requests
    (username_request, date, type)
    VALUES
    ('$username_request', NOW(), 'fetch users')");

    echo json_encode($users);

  }

}


 ?>
