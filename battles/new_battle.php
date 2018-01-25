<?php

/*
script to create a new battle
*/

require('../config/modules.php');
require('../config/dbconfig.php');
require('../config/translators.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

$project_id_signed = (int)clean_input($conn, 'project_id'); // project that created the battle
$username_signing = clean_input($conn, 'username_signing'); // project owner that created the battle.
$id_signing = (int)clean_input($conn, 'id_signing'); // id of owner

if (empty($id_signing) || empty($project_id_signed) || empty($username_signing)) {
  printjson(array('success' => 0, 'error_log' => 'Missing parameters'), true);
}

$battle_winner_reward_pet = (int)clean_input($conn, 'winner_reward_pet');
$battle_winner_reward_bitfunds = (int)clean_input($conn, 'winner_reward_bitfunds');


$project_attacks = mysqli_real_escape_string($conn, $_POST['attacks']);
$project_attacks = json_encode($project_attacks);

$battle_pvp_pve = clean_input($conn, 'pvp_pve');

$rows = mysqli_query($conn, "SELECT * FROM battles");

$count = $rows->num_rows + 2;
//
// if (empty($battle_winner_reward_pet)) {
//   $battle_winner_reward_pet = 0;
// }

// check if the project_id signing up already have a ongoing battle / looking for opponent

$c = mysqli_query($conn, "SELECT in_battle_battle_id FROM projects WHERE id = $project_id_signed AND in_battle_battle_id > 0");

// in battle
if ($c->num_rows > 0) {
  printjson(array('success' => 0, 'in_battle_or_signed' => 1));
} else {

  if ($battle_pvp_pve === 'pvp') {
    // create a new battle instance.
    $string = "INSERT INTO battles (battle_id,battle_player1,battle_player1_id,battle_player1_project_id,battle_signed_up_date, battle_winner_reward_bitfunds, battle_winner_reward_pet, battle_pvp_pve, battle_player1_attacks)
    VALUES
    ($count,'$username_signing',$id_signing,$project_id_signed,'$now',$battle_winner_reward_bitfunds, $battle_winner_reward_pet, 'pvp', $project_attacks)";
    $qry = mysqli_query($conn,$string);

    if ($qry) {
      $string2 = "UPDATE projects SET in_battle = 1, in_battle_battle_id = $count WHERE id = $project_id_signed";
      $update_project = mysqli_query($conn,"UPDATE projects SET in_battle = 1, in_battle_battle_id = $count WHERE id = $project_id_signed");

      if ($update_project) {

        printjson(array('success' => 1, 'query'  => $string2 . mysqli_error($conn)));
      } else {

        printjson(array('success' => 0, 'error_log' => 'mysqli_error', 'mysqli_error' => mysqli_error($conn), 'mysqli_query' => $string . $string2));
      }
    } else {
      $count = $count + 1;
      if (mysqli_query($conn, $string)) {
        printjson(array('success' => 1,));
      } else {
        printjson(array('success' => 0, 'error_log' => 'mysqli_error', 'mysqli_error' => mysqli_error($conn), 'mysqli_query' => $string . $string2));
      }
    }
  } else if($battle_pvp_pve === 'pve') {

    /*
    calculate bets.

    * if a pet,
    * amount of BitFunds.
    * the projects stats.
    */

    // a pet is in the bet.
    $winning_chance = 85; // %

    if ($battle_winner_reward_pet >= 1) {
      $winning_chance = $winning_chance - 20;
    }

    switch ($battle_winner_reward_bitfunds) {
      case 20:
        $winning_chance = $winning_chance - 15;
      break;

      case 50:
        $winning_chance = $winning_chance - 23;
      break;

      case 75:
        $winning_chance = $winning_chance - 30;
      break;
    }

    $rand = rand(1,100);

    if ($rand <= $winning_chance) {
      // project won!
      $winner = $project_id_signed;
    } else {
      $winner = 0;
    }


    $battle_until_date = set_time('+'.rand(1,4).' hours');

    //$battle_until_date = set_time('+1 minutes');

    // create a new battle instance.
    $string = "INSERT INTO battles (battle_id,battle_player1,battle_player1_id,battle_player1_project_id,battle_signed_up_date, battle_winner_reward_bitfunds, battle_winner_reward_pet, battle_pvp_pve, battle_until_date, battle_winner_project_id)
    VALUES
    ($count,'$username_signing',$id_signing,$project_id_signed,'$now',$battle_winner_reward_bitfunds, $battle_winner_reward_pet, 'pve', '$battle_until_date', $winner)"; // change battle_until_date to 2hrs from $now! TODO!
    $qry = mysqli_query($conn,$string);

    if ($qry) {
      $string2 = "UPDATE projects SET in_battle = 1, in_battle_battle_id = $count WHERE id = $project_id_signed";
      $update_project = mysqli_query($conn,"UPDATE projects SET in_battle = 1, in_battle_battle_id = $count WHERE id = $project_id_signed");

      if ($update_project) {

        printjson(array('success' => 1, 'battle_until' => $battle_until_date));
      } else {

        printjson(array('success' => 0, 'error_log' => 'mysqli_error', 'mysqli_error' => mysqli_error($conn), 'mysqli_query' => $string . $string2));
      }
    } else {
      $count = $count + 1;
      if (mysqli_query($conn, $string)) {
        printjson(array('success' => 1,));
      } else {
        printjson(array('success' => 0, 'error_log' => 'mysqli_error', 'mysqli_error' => mysqli_error($conn), 'mysqli_query' => $string . $string2));
      }
      //

    }
  } else {
    printjson(array('success' => 0, 'error_log' => 'No valid battle (PvP/PvE)'));
  }



}


mysqli_close($conn);

 ?>
