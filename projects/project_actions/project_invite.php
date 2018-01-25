<?php

/*
Script created to handle users accept/decline requests towards inviting eachother
into projects..

This script will only be accessed by Player2
*/

require '../../config/dbconfig.php';
require '../../config/modules.php';

$conn = get_connection();

$post = new json_post();

$post->__construct(false);

if (isset($_POST['project_action'])) {
  // while project_action is TRUE, accept the project.. else decline.

  $project_action = clean_input($conn,'project_action'); // var will be either true or false.
  $project_id = clean_input($conn,'project_id');
  $player2 = clean_input($conn,'username');

  /*
  * TODO.
   1. Get player1,
   2. send push notification.
  */

  $pl1 = mysqli_query($conn, "SELECT player1 FROM projects WHERE id = $project_id");

  $player1 = mysqli_fetch_array($pl1);
  $player1 = $player1['player1'];

  require_once('../../ApnsPHP/new_push.php');

  $device_token = retrieve_device_token($conn,$player1);

  if ($device_token['success'] == 1) {
    $init_push = true;
  } else {
    $init_push = false;
  }

  $response = array();
  // if action == true
  if ($project_action) {

    try {
      $qry = mysqli_query($conn, "UPDATE projects SET player2_accepted = 1 WHERE id LIKE '$project_id'");
      $response[] = array('success' => 1, 'action' => true);
      if ($init_push) {
        new_push($player2 . ' has accepted your pet invite!', 1, $device_token['device_token']);
      }
    } catch (Exception $e) {
      $response[] = array('success' => 0, 'action' => true, 'error_log' => $e  + mysqli_error($conn));
    }
  } else {
    // -1 == declined..
    try {
      $qry = mysqli_query($conn, "UPDATE projects SET player2_accepted = '-1' WHERE id LIKE '$project_id'");
      $response[] = array('success' => 1, 'action' => false);
      if ($init_push) {
        new_push($player2 . ' has declined your pet invite!', 1, $device_token['device_token']);
      }
    } catch (Exception $e) {
      $response[] = array('success' => 0, 'action'=> false, 'error_log' => $e + mysqli_error($conn));
    }
  }
  echo json_encode($response);
}

 ?>
