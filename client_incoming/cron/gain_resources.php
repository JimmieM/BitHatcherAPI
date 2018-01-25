<?php

/*
script to run once every few days..
*/

require('../config/dbconfig.php');
require('../config/modules.php');
require('../config/translators.php');

function gain_resources($username = null) {
  // select a handful of players to give resources..
  $conn = get_connection();


  if ($username === null) {
    $select = mysqli_query("SELECT player_username FROM users ORDER BY rand() LIMIT rand()");
  } else {
    $select = mysqli_query("SELECT player_username FROM users WHERE player_username = '$username' LIMIT 1");
  }



  while ($users = mysqli_fetch_array($select)) {

    $username = $users['player_username'];

    foreach($users as $user) {
      // give resources..
      $token = create_token();

      $resource_array = num_food();

      $random_x = array_rand($resource_array,rand(1,5));

      foreach ($random_x as $x) {
        $return_food = food_translator($x);

        // $return_food might have cooked_steak now.
        $amount = rand(1,10);

        $stmt = $conn->prepare("UPDATE users SET $return_food = ?");
        $stmt->bind_param("i", $amount);

        // log this in seperate table as well..
        $stmt1 = $conn->prepare("INSERT INTO (player_username, food_type, amount, date) VALUES (?,?,?,?)");
        $stmt1->bind_param("ssis", $username, $return_food, $amount, $now);

        if (($stmt->execute()) && ($stmt1->execute())) {
          # code...
        }
      }

      // use function x times
      // give x amount of foodtype

    }
  }
}


 ?>
