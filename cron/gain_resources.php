<?php

/*
script to run once every few days..
*/

require('../config/dbconfig.php');
require('../config/modules.php');
require('../config/translators.php');



// select a handful of players to give resources..
$conn = get_connection();

$username = clean_input($conn, 'username');

// success counter;
$success = 0;

// foreach($users as $user) {
// give resources..
$token = create_token();

for ($i = 0; $i < rand(1,5);  $i++) {

  $num_food = num_foods();
  $food = array_rand($num_food);
  $return_food = "player_" . food_translator($food);

  // $return_food might have cooked_steak now.
  $amount = rand(1,6);



  // $stmt = $conn->prepare("UPDATE users SET $return_food = $return_food + ? WHERE player_username = ?");
  // $stmt->bind_param("is", $amount, $username);

  // if ($stmt = $conn->prepare("UPDATE users SET $return_food = $return_food + ? WHERE player_username = ?")) {
  //   $stmt->bind_param("is",$amount, $username);
  // } else {
  //   die(json_encode(array("success" => 0)));
  // }

  try {

    $sql = "UPDATE users SET $return_food = $return_food + $amount WHERE player_username = '$username'";
    $stmt = mysqli_query($conn, $sql);

    $sql1 = "INSERT INTO gain_resources_requests (player_username, food_type, amount, datee, token) VALUES ('$username', '$return_food', $amount, '$now', '$token')";
    $stmt1 = mysqli_query($conn, $sql1);

    // why doesn't prepared statements work. TODO, you lazy programmer whom using mysqli_query in prod.

    // // log this in seperate table as well..
    // $stmt1 = $conn->prepare("INSERT INTO gain_resources_requests (player_username, food_type, amount, date, token) VALUES (?,?,?,?,?)");
    // $stmt1->bind_param("ssiss", $username, $return_food, $amount, $now, $token);

    $update = update_client_incoming($username,$token,$conn,'apply');

    if (($stmt) && ($update === 1) && ($stmt1)) {
      $success ++;
    }

  } catch (Exception $e) {
    // wtf todo
    // maby insert or smthing
  }
}

  echo json_encode(array('new_resources' => $success));


 ?>
