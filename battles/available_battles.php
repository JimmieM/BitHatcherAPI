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

$fetch = mysqli_query($conn, "SELECT * FROM battles WHERE battle_available = 1 AND battle_deleted = 0 AND battle_pvp_pve = 'pvp' ORDER BY DATE(battles.battle_signed_up_date) DESC");

$all_battles = array();
if ($fetch->num_rows > 0) {
  while ($battles = mysqli_fetch_array($fetch)) {
    // get player1's stuff.
    $player1_id = $battles['battle_player1_id'];
    $player1_project_id = $battles['battle_player1_project_id'];

    // decode JSON string
    $battles['battle_player1_attacks'] = json_encode($battles['battle_player1_attacks']);
    $battles['battle_player1_attacks'] = stripslashes($battles['battle_player1_attacks']);

    $string= "SELECT users.player_level, users.player_avatar, users.player_username, users.player_id, projects.* FROM users,projects WHERE users.player_id = $player1_id AND projects.id = $player1_project_id";
    $data = mysqli_query($conn, $string);

    if ($data->num_rows > 0) {
      while ($select = mysqli_fetch_array($data)) {
        if ((int)$battles['battle_winner_reward_pet'] != 0 ) {
          $pet = animal_translator((int)$battles['battle_winner_reward_pet']);
        }
        $all_battles[] = array_merge(['success' => 1], $select, $battles, ['can_sign' => 1, 'battle_winner_reward_pet' => $pet]);
      }
    } else {
      printjson(array('success' => 0, 'error_log' => '2nd query returned empty.', 'query' => $string), true);
    }
    //$all_battles[] = $battles;
  }
} else {
  $all_battles[] = ['empty' => 1];
}

printjson($all_battles, true);

 ?>
