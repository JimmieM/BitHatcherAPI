<?php

/*
Mainly used for in-game shop,
but also to give user stuff during battles_winner.
*/

require_once(__DIR__.'/../config/dbconfig.php');
require_once(__DIR__.'/../config/modules.php');
require_once(__DIR__.'/../config/translators.php');
require_once(__DIR__.'/validate_bitfunds.php');


if ($_POST['purchase_item'] >= 0) {
  $post = new json_post();
  $post->__construct(false);

  $conn = get_connection();

  // get users purchase.
  $username = clean_input($conn, 'username_request');

  $item = clean_input($conn, 'purchase_item');

  $buy = purchase_item($now,$conn, $item, $username);
  //die(json_encode(array('succes' =>0)));
  if ($buy['success'] == 1) {
    // the purchase was successfull.
    echo json_encode(array('success' => 1, $buy));
  } else {
    echo json_encode(array('success' => 0, 'error_log' => $buy));
  }
} else {
  echo json_encode(array('success' => 0, 'error_log' => 'invalid POST'));
  die();
}

function purchase_item($now,$conn, $item, $username, $pay = true) {

  // POST request.
  $total_cost = (int)clean_input($conn, 'total_price'); // cost from client

  // get costs of pets in BitFunds.
  // constants for the pets.
  switch ($item) {
    case 0:
      $buy_pet = false;
      $cost = 15;
      $food_bundle = true;
      break;

    case 1:
      $creature = 0; // bird <-> egg
      $egg_creature = 1;
      $versions = 3;
      $cost = 40;


      $buy_pet = true;
      break;

    case 2:
      $creature = 1; // fox
      $egg_creature = 0;
      $versions = 3;
      $cost = 55;

      $buy_pet = true;
      break;

    case 3:
      $creature = 2; // rabbit
      $egg_creature = 0;
      $versions = 2;
      $cost = 40;
      $buy_pet = true;
      break;

    case 4:
      $creature = 3; // red panda
      $egg_creature = 0;
      $versions = 3;
      $cost = 60;
      $buy_pet = true;
      break;

    case 5:
      $creature = 4; // squirell
      $egg_creature = 0;
      $versions = 3;
      $cost = 70;
      $buy_pet = true;
      break;
    case 6:
      $creature = 5; // turtle
      $egg_creature = 0;
      $versions = 3;
      $cost = 125;
      $buy_pet = true;
      break;
    }


    // buy a pet
    if ($buy_pet) {
      // get the purchased pet params
      $pet_name = clean_input($conn, 'pet_name'); // pets name
      $player2 = clean_input($conn, 'player2'); // player2s name
      $stage = (int)clean_input($conn, 'pet_stage'); // stage of pet.

      // check if all post values have a value.
      if ((empty($pet_name) || (empty($stage)) || (empty($total_cost)))) {
        return array('success' => 0, 'error_log' => 'empty pet variables');
      }

      if (!empty($player2)) {
        // check if player2 exists & if you're not inviting yourself.
        if ($player2 !== $username) {
          if (!empty($player2)) {
            $player2_check = mysqli_query($conn, "SELECT player_username FROM users WHERE player_username LIKE '$player2'");
            if ($player2_check) {
              if ($player2_check->num_rows == 0) {
                // no user found!
                return array('success' => 0, 'error_log' => "Username doesn't exist");
              } else {

                require_once('../ApnsPHP/new_push.php');

                $device_token = retrieve_device_token($conn,$player2);

                if ($device_token['success'] == 1) {

                    new_push("You've been invited to join a pet!", 1, $device_token['device_token']);
                }
              }
            }
          }
        } else {
          return array('success' => 0, 'error_log' => "You can't invite yourself");
        }
      }




      if ($pay) {
        // validate total_cost from client AND server_cost which is the start price for the pet * the chosen stage.
        // also validate if the user has the amount of bitfunds in inventory,
        $validate = validate_bitfunds($conn, ($cost * $stage), $total_cost, $username);
        // $validate will return an array with success, error_log
        if ($validate['success'] != 1) {
          // this will most likely return error_log=>'not enough bitfunds.'.
          return array('success' => 0, 'error_log' => 'BitFunds: ' . $validate['error_log']);
        }
      }


      // create the pet.
      $current_status = "Status delayed..";
      $agility = 0;
      $strength = 0;

      if (($egg_creature === 1) && ($stage === 1)) {
        $current_status = "Waiting for egg to hatch";
        $agility = 0;
        $strength = 0;
        $happiness = 0;

      } else {
        $current_status = "something better..";

        // it can be a fox, so it's basically alive and has some kind of strength of brething etc..
        // assign a random value for strength n agility.
        $min = 20;
        $max = 100;
        $agility = rand($min,$max);
        $strength = rand($min,$max);
        $happiness = rand($min,$max);
      }
      // create a new project

      try {
        $response = array();

        $animal_path = animal_translator($creature);

        if (!$animal_path) {
          return array('success' => 0);
        }

        // choose animal version

        $version = rand(1,$versions);

        // convert to string
        $version = strval($version);

        $version = 'v' . $version;

        //$src_path = "img/animals/" . $animal_path . "/" . $version;

        $src_path = sprintf('img/animals/%s/%s/stage_',$animal_path, $version);

        //creature: chosen in Switch Case function, "bird", "fox", or something
        //egg_Creature, basically a bool, where 1 is true and 0 is false. In case of Fox, the egg_create is false 0
        $string = "INSERT INTO projects
          (creature,
          egg_creature,
          name,
          player1,
          player2,
          date_created,
          current_status,
          strength,
          agility,
          happiness,
          date_latest_fed_food,
          date_latest_fed_water,
          date_latest_update,
          stage,
          bought_as_stage,
          src_path,
          src_version)
          values (
          $creature,
          $egg_creature,
          '$pet_name',
          '$username',
          '$player2',
          '$now',
          '$current_status',
          $strength,
          $agility,
          $happiness,
          '$now',
          '$now',
          '$now',
          $stage,
          $stage,
          '$src_path',
          '$version')";

        $qry = mysqli_query($conn,$string);

          if ($qry) {
            // withdraw BitFunds.
            if ($pay) {
              $update_user = mysqli_query($conn, "UPDATE users SET player_bitfunds = player_bitfunds - $cost WHERE player_username = '$username'");
              if ($update_user) {
                return array('success' => 1, 'paid' => 1);
              } else {
                return array('success' => 0 , 'error_log' => mysqli_error($conn), 'paid' => 0);
              }
            } else {
              return array('success' => 1, 'paid' => 1);
            }

            //printjson(array('path' => $src_path, 'strength' => $strength, 'agility' => $agility, 'current_status' => $current_status, 'creature' => $creature, 'versions_available' => $versions, 'version_picked' => $version), true);

          } else {
            return array('success' => 0 , 'error_log' => mysqli_error($conn), 'query' => $string);

          }
      } catch (Exception $e) {
        return array('success' => 0 , 'error_log' => $e);
      }
    // food bundle
    }
    if($food_bundle) {

      $validate = validate_bitfunds($conn, $cost, $total_cost, $username);

      // $validate will return an array with success, error_log
      if ($validate['success'] != 1) {
        // this will most likely return error_log=>'not enough bitfunds.'.
        return array('success' => 0, 'error_log' => 'BitFunds: ' . $validate['error_log']);
      }

      // withdraw BitFunds.
      $withdraw = mysqli_query($conn, "UPDATE users SET player_bitfunds = player_bitfunds - $total_cost WHERE player_username = '$username'");

      if (!$withdraw) {
        return array('success' => 0, 'error_log' => 'mysqli', 'mysqli_error' => mysqli_error($conn));
      }

      // give resources..
      $token = create_token();

      $resource_array = num_foods();

      $random_x = array();
      //$random_x[] = array_rand($resource_array,rand(1,7));

      for ($i=0; $i < rand(1,16); $i++) {
        $rand = array_rand($resource_array);
        $random_x[] = $resource_array[$rand];
      }

      $all_resources_gained = array();

      foreach ($random_x as $x) {
        // $return_food might have cooked_steak now.
        $return_food = food_translator($x);

        // get the amount of cooked_steak that should be added.
        $amount = rand(2,24);

        if ($amount >= 14) {
          $amount = rand(20,40);
        }

        // $stmt = $conn->prepare("UPDATE projects SET $return_food = ? WHERE id = $project_id");
        // $stmt->bind_param("i", $amount);

        // player_$returnfood
        $return_food = return_player_resource($return_food);

        $string = "UPDATE users SET $return_food = $return_food + $amount WHERE player_username = '$username'";
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
          $food_name = replace_string($food_name, '_', ' ');
          $all_resources_gained[] = ['success' => 1 ,'resource' => $amount . ' ' . $food_name];
        } else {
          $all_resources_gained[] = ['success' => 0, 'error_log' => mysqli_error($conn)];
        }
      }

      //return $all_resources_gained;
      return array('success' => $success, 'all_resources' => $all_resources_gained);
    }
  }

  function new_attack_instance($project_id, $conn) {
    $query = mysqli_query($conn, "INSERT INTO project_attacks (attacks_project_id) VALUES ($project_id)");
    if ($query) {
      return true;
    }

    return false;
  }
  mysqli_close($conn);




 ?>
