<?php

/*
    create if else for which action the user requested against project..

*/

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once('../../config/dbconfig.php');
require('../../config/translators.php');
require('../../config/modules.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

// project id is ideal to be sent properly..
if (isset($_POST['project_id']) && (isset($_POST['foodtype']))) {

  $project_id = clean_input($conn, 'project_id');

  try {

    $qry = mysqli_query($conn, "SELECT * FROM projects WHERE id = $project_id LIMIT 1");

    while ($data = mysqli_fetch_array($qry)) {
      // get project vars
      $creature = $data['creature'];
      $stage = $data['creature'];
      $health = $data['health'];
      $energy = $data['energy'];
      $water = $data['water'];
      $agility = $data['agility'];
      $strength = $data['strength'];

      // food
      $overfeeding = $data['overfeeding'];
      $foodtype_raw_chicken = $data['foodtype_raw_chicken'];
      $foodtype_cooked_chicken = $data['foodtype_cooked_chicken'];
      $foodtype_raw_steak = $data['foodtype_raw_steak'];
      $foodtype_cooked_steak = $data['foodtype_cooked_steak'];

      $foodtype_bird_seed = $data['foodtype_bird_seed'];
      $foodtype_carrot = $data['foodtype_carrot'];
      $foodtype_mini_carrot = $data['foodtype_mini_carrot'];

      if (empty($data)) {
        echo json_encode(array('success' => 0, 'mysqli_response' => 0));
        die();
      } else {

        $foodtype_string = food_translator($_POST['foodtype']);

        evaluation($overfeeding, $foodtype_string , $data[$foodtype_string],$creature,$health,$agility,$strength,$energy,$project_id,$conn);

      }
    }
  } catch (Exception $e) {
      echo json_encode( array('success' => 0, 'error_log' => $e));
      die();
  }

} else {
  echo json_encode( array('success' => 0, 'request_denied' => 1));
  die();
}

// foodtype_String  = name of food (carrots) : carrots
// foodtype = amount of food (carrots) : x amount
function evaluation($overfeeding,$foodtype_string,$foodtype,$creature, $health,$agility, $strength, $energy, $project_id,$conn) {

  // double check if the project has resources for chosen action
  if ($foodtype >= 1) {

    // overfeeding module
    if ($energy >= 100) {
      // over eating..
      // do somthing in DB
      $overfeeding = $overfeeding + 1;
    }

    // standard values that the food_types give..
    // each creature will gain standard vlaue + extra depending on creature

    // for eg. Fox will gain More extra value than a bird when eating Meat...

    $meat = false;
    $veggies = false;

    switch ($foodtype_string) {
      case 'foodtype_cooked_steak':
          $health_standard = 20;
          $agility_standard = 5;
          $strength_standard = 20;
          $energy_standard = 40;
          $meat = true;

        break;

      case 'foodtype_cooked_chicken':
          $health_standard = 20;
          $agility_standard = 5;
          $strength_standard = 20;
          $energy_standard = 40;
          $meat = true;

        break;

      case 'foodtype_mini_carrot':
          $health_standard = 2;
          $agility_standard = 1;
          $strength_standard = 4;
          $energy_standard = 4;
          $veggies = true;
        break;
      case 'foodtype_carrot':
          $health_standard = 5;
          $agility_standard = 5;
          $strength_standard = 5;
          $energy_standard = 5;
          $veggies = true;

        break;
      case 'foodtype_water':
          $health_standard = 2;
          $agility_standard = 3;
          $strength_standard = 4;
          $energy_standard = 1;
          $veggies = false;
          $meat = false;
        break;

      default:
        # code...
        break;
    }


    // how much should 1 steak increase in HP/strength/agility and energy?
    // the standard food increase plus how much the Fox gets from it.
    // The shall not get as much energy with steak as the fox gets..
    // TODO
    include('../pet_behaviours.php');
    $preffered_food = pet_behaviours($creature);

    $preffered_food = $preffered_food['food_preffered'];

    foreach ($preffered_food as $food => $value) {
      if ($food_type === $value) {
        // user fed pet with a preffered food type.
        $energy = $energy + 15;
        $strength = $strength + 15;
        $agility = $agility +15;
      }
    }

    switch ($creature) {
      case 0: // bird
        $meat = 2;
        $veggies = 2;
        $health = $health + $health_standard + (3 * $meat * $veggies);
        $agility = $agility + $agility_standard + 4;
        $strength = $strength + $strength_standard + 15;
        $energy = $energy + $energy_standard + 7;
        break;

      case 1: // fox
        $meat = 2;
        $veggies = 0;
        $health = $health + $health_standard + (3 * $meat * $veggies);
        $agility = $agility + $agility_standard + 15;
        $strength = $strength + $strength_standard + 15;
        $energy = $energy + $energy_standard + 15;
        break;

      case 2: // rabbit
        $health = $health + $health_standard + 3;
        $agility = $agility + $agility_standard + 15;
        $strength = $strength + $strength_standard + 17;
        $energy = $energy + $energy_standard + 17;

        break;

      case 3: // red_panda
        $health = $health + $health_standard + 3;
        $agility = $agility + $agility_standard + 10;
        $strength = $strength + $strength_standard + 30;
        $energy = $energy + $energy_standard + 25;

        break;

      case 4: // squirell
        $health = $health + $health_standard + 1;
        $agility = $agility + $agility_standard + 4;
        $strength = $strength + $strength_standard + 5;
        $energy = $energy + $energy_standard + 6;

        break;

      case 5: // turt
        $health = $health + $health_standard + 11;
        $agility = $agility + $agility_standard + 15;
        $strength = $strength + $strength_standard + 32;
        $energy = $energy + $energy_standard + 25;

        break;

      default:
        # code...
        break;
    }

  }

  // check if any stats is above 100.. if so, make it an even 100%.
  if ($health > 100) {
    $health = 100;
  }
  if ($agility > 100) {
    $agility = 100;
  }
  if ($strength > 100) {
    $strength = 100;
  }
  if ($energy > 100) {
    $energy = 100;
  }


  // return all values of increment
  execute_action($overfeeding,$foodtype_string,$foodtype,$health, $agility, $energy, $strength, $foodtype, $project_id,$conn);
}



function execute_action($overfeeding,$foodtype_string,$foodtype, $new_health, $new_agility, $new_energy, $new_strength, $foodtype_decrease,$project_id,$conn) {
  try {
    $now = date("Y-m-d H:i:s");

    $foodtype_decrease = $foodtype_decrease - 1;

    $sql_fed_with = 'date_latest_fed_food';
    if ($foodtype_string === "foodtype_water") {
      $sql_fed_with = 'date_latest_fed_water';
    }

    $update = mysqli_query($conn, "UPDATE projects SET
      $foodtype_string = '$foodtype_decrease',
      health = '$new_health',
      energy = '$new_energy',
      agility = '$new_agility',
      strength = '$new_strength',
      overfeeding = '$overfeeding',
      $sql_fed_with = '$now'
      WHERE id = '$project_id'");

      if ($update) {
        // return array of all foodtypes
        $foodtypes = num_foods();

        $fetch_food = array();
        foreach ($foodtypes as $food) {
          $return_food = food_translator($food);

          $fetch_food[] = $return_food;
        }

        $types = implode(', ', $fetch_food);

        $qry = mysqli_query($conn, "SELECT
          $types
          FROM projects WHERE project_id = $project_id");

        $result = null;
        if ($qry->num_rows > 0) {
          $result = mysqli_fetch_array($qry);
        }

        try {
          require('../../users/gain_experience.php');
          $experience = new experience($conn);

          $exp = $experience->gain_experience(clean_input($conn, 'username_request'), 400);


        } catch (Exception $e) {
            echo json_encode( array('success' => 0 , 'error_log' => $e));
        } finally {
          echo json_encode( array('achievement' => $exp,  'success' => 1 ,'overfeeding' => $overfeeding, 'health' => $new_health, 'energy' => $new_energy, 'agility' => $new_agility, 'strength' => $new_strength, 'foodtypes' => $result));
        }

      } else {
        echo json_encode( array('success' => 0 , 'error_log' => mysqli_error($conn)));
      }


  } catch (Exception $e) {
    echo json_encode( array('success' => 0, "error_log" =>$e ));
  }

}

 ?>
