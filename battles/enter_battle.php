<?php

require('../config/modules.php');
require('../config/dbconfig.php');
require('../config/translators.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

$battle_id = clean_input($conn, 'battle_id');
$project_id_signed = clean_input($conn, 'project_id'); // project that signed up for battle for $project_id_versus
$username_signing = clean_input($conn, 'username_signing'); // project that signed up for battle for $project_id_versus
$id_signing = clean_input($conn, 'id_signing');

$project_attacks = mysqli_real_escape_string($conn, $_POST['attacks']);
$project_attacks = json_encode($project_attacks);


// $min_add = $now + (200 * 60);
// $battle_until_date = date('m-d-Y H:i:s', $min_add);

$battle_until_date = set_time('+2 hours');

$qry = mysqli_query($conn, "UPDATE battles SET battle_available = 0, battle_until_date = '$battle_until_date', battle_player2_id = $id_signing, battle_player2_project_id = $project_id_signed, battle_player2_attacks = '$project_attacks' WHERE battle_id = $battle_id");

// set the user that is signing up for the battle as in_battle = 1 AND which battle the project joined.
$string = "UPDATE projects SET in_battle = 1, in_battle_battle_id = $battle_id WHERE id = $project_id_signed";
$update_project = mysqli_query($conn, $string);

if (($qry) && ($update_project)) {
  $winner = get_playerdata($conn, $battle_id, $project_id_signed);
  if ($winner === '-1') {
    printjson(array('success' => 0, 'error_log' => $winner), true);
  }
  // main.
  printjson(array('success' => 1, 'winner' => $winner, 'battle_until_date'=>$battle_until_date));

} else {
  printjson(array('success' => 0, 'error_log' => mysqli_error($conn)));
}

// pick a winner.
function get_playerdata($conn, $battle_id, $project_id_signed) {

  $battle_player1 = mysqli_query($conn, "SELECT projects. * , battles.*
  FROM battles, projects
  WHERE battles.battle_id = $battle_id
  AND projects.id = battles.battle_player1_project_id LIMIT 1");

  if ($battle_player1->num_rows > 0) {

      while ($player1 = mysqli_fetch_array($battle_player1)) {
        require_once('../ApnsPHP/new_push.php');
        // notify users FROM project2, that someones entered the battle.
        $project1_player1 = $player1['player1'];
        $project1_player2 = $player1['player2'];

        $project1_player1_device_token = retrieve_device_token($conn, $project1_player1);

        if ($project1_player1_device_token['success'] == 1) {
          new_push("Someone has joined your battle!", 1, $project1_player1_device_token['device_token']);
        }

        if (!empty($project1_player2)) {
          if ($project1_player2_device_token['success'] == 1) {
            $project2_player2_device_token = retrieve_device_token($conn, $project1_player2);
            new_push("Someone has joined your battle!", 1, $project1_player2_device_token['device_token']);
          }
        }

        $battle_player2 = mysqli_query($conn, "SELECT * FROM projects WHERE id = $project_id_signed");

        if ($battle_player2->num_rows > 0) {
          while ($player2 = mysqli_fetch_array($battle_player2)) {
            return select_winner($conn,$battle_id ,$player1, $player2);
          }
        } else {
          return 'Could not find player 2';
        }
      }

      //return select_winner($player, $player2);
  } else {
    return 'Could not find player 1';
  }
}

// each player1, and player2 should be project1, project2. Idk.

function attacks_eval($conn, $player1, $player2) {
    // get your pets attacks

    // get opponent pets attacks

    // new instance
    $attack_rules = new attack_rules;

    // return array with all rules
    $rules = $attack_rules->return_rules();

    // reduce opponent HP by your attacks.

    // if opponent has Protection/enrage which reduces HP taken, also do that.


}

function select_winner($conn, $battle_id, $player1, $player2) {

  require('../projects/pet_behaviours.php');

  $arr_picker = array();

  // $player1's projects stuff.
  $push_player1 = $player1['agility'] + $player1['strength']; // add HP

  $push_player2 = $player2['agility'] + $player2['strength']; // add HP


  for ($i=0; $i < $push_player1; $i++) {
    //array_push($arr_picker, "player1");
    $arr_picker[] = 'player1';
  }

  for ($i=0; $i < $push_player2; $i++) {
    $arr_picker[] = 'player2';
  }

  if ($player1['stage'] > $player2['stage']) {

    $arr_picker[] = array_fill(0, 50, 'player1');
  } else if($player2['stage'] > $player1['stage']) {
    $arr_picker[] = array_fill(0, 50, 'player2');
  } // else its the same stage.. === equal winning chance..

  $traits_player1 = pet_behaviours($player1['creature']);
  $traits_player2 = pet_behaviours($player2['creature']);

  $arr_picker['player1'].array_push($traits_player1['winning_chance']['stage_' . $player1['stage']]);
  $arr_picker['player2'].array_push($traits_player2['winning_chance']['stage_' . $player2['stage']]);

  $arr_picker = array_merge($arr_picker, $arr_picker);


  // set battle winner
  $winner = $arr_picker[array_rand($arr_picker)];

  $project_id = '';
  if ($winner === 'player1') {
    $project_id = $player1['id'];
  } else {
    $project_id = $player2['id'];
  }

  $string = "UPDATE battles SET battle_winner_project_id = $project_id WHERE battle_id = $battle_id";

  $sel_ = mysqli_query($conn, $string);

  return $winner;
}

 ?>
