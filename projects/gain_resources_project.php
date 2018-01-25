<?php

/*
script to give a project some resources on call

forexample when winning a battle.
*/

function gain_resources_project($project_id) {
  require_once('../config/dbconfig.php');
  require_once('../config/modules.php');
  require_once('../config/translators.php');

  //show_errors();

  // select a handful of players to give resources..
  $conn = get_connection();

  // give resources..
  $token = create_token();

  $resource_array = num_foods();

  $random_x = array();
  //$random_x[] = array_rand($resource_array,rand(1,7));

  for ($i=0; $i < rand(1,8); $i++) {
    $rand = array_rand($resource_array);
    $random_x[] = $resource_array[$rand];
  }

  $all_resources_gained = array();

  foreach ($random_x as $x) {
    // $return_food might have cooked_steak now.
    $return_food = food_translator($x);

    // get the amount of cooked_steak that should be added.
    $amount = rand(1,10);

    // $stmt = $conn->prepare("UPDATE projects SET $return_food = ? WHERE id = $project_id");
    // $stmt->bind_param("i", $amount);

    $string = "UPDATE projects SET $return_food = $return_food + $amount WHERE id = $project_id";
    $qry = mysqli_query($conn, $string);

    // // log this in seperate table as well..
    // $stmt1 = $conn->prepare("INSERT INTO gain_resources_requests (player_username, food_type, amount, date) VALUES (?,?,?,?)");
    // $stmt1->bind_param("ssis", $username, $return_food, $amount, $now);

    if ($qry) {
      // if atleast 1 qry was successfuly,
      $success = 1;
      // translate X into the real name of the foodtype. Example foodtype_carrot into Carrots
      $food_name = food_translator_names($x);
      if ($amount > 1) {
        // plural
        $food_name = $food_name . 's';
      }
      $all_resources_gained[] = ['success' => 1 ,'battle_resource_won' => $amount . ' ' . $food_name];
    } else {
      $all_resources_gained[] = ['success' => 0, 'qry' => $string, 'error_log' => mysqli_error($conn)];
    }
  }

  //return $all_resources_gained;
  return array('battle_resource_success' => $success, 'all_battle_resources_won' => $all_resources_gained);
}


 ?>
