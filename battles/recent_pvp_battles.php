<?php
/*
script to return to client with all available battles.
*/

require('../config/modules.php');
require('../config/dbconfig.php');
require('../config/translators.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

$fetch = mysqli_query($conn, "SELECT * FROM battles WHERE battle_available = 0 AND battle_deleted = 0 AND battle_pvp_pve = 'pvp' ORDER BY DATE(battles.battle_signed_up_date) DESC LIMIT 30");

$all_battles = array();
if ($fetch->num_rows > 0) {
  while ($battles = mysqli_fetch_array($fetch)) {
    // get player1's stuff.
    $player1_id = $battles['battle_player1_id'];
    $player2_id = $battles['battle_player2_id'];
    $player1_project_id = $battles['battle_player1_project_id'];
    $player2_project_id = $battles['battle_player2_project_id'];

    $bitfunds_won = $battles['battle_winner_reward_bitfunds'];

    $battle_winner = $battles['battle_winner_project_id'];
    $player1 = get_data($conn, $player1_project_id, $player1_id);

    $player2 = get_data($conn, $player2_project_id, $player2_id);

    if ((int)$battles['battle_winner_reward_pet'] != 0 ) {
      $pet = animal_translator((int)$battles['battle_winner_reward_pet']);
    }

    $all_battles[] = ['project_1' => $player1, 'project_2' => $player2, 'battle_winner_reward_pet' => $pet,'battle_winner_reward_bitfunds' => $bitfunds_won, 'project_winner' => $battle_winner];

    //$all_battles[] = $battles;
  }
} else {
  $all_battles[] = ['empty' => 1];
}

printjson($all_battles);

function get_data($conn, $project_id, $player_id) {
  $project_data = array();
  $data = mysqli_query($conn, "SELECT users.player_level,
    users.player_avatar,
    users.player_username,
    users.player_id,
    projects.id,
    projects.stage,
    projects.name,
    projects.src_path,
    projects.player1,
    projects.player2
    FROM users, projects
    WHERE users.player_id = $player_id
    AND projects.id = $project_id");

  if ($data->num_rows > 0) {

    $project_data = mysqli_fetch_array($data);

    return $project_data;
  } else {
    return null;
  }
}



 ?>
