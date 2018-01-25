<?php

/*
  usage of revival potion..
*/

require_once('../../config/dbconfig.php');
require_once('../../config/modules.php');
require_once('../../config/translators.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

if ($_POST['revive_project']) {
  $username = clean_input($conn, 'username_request');
  $project_id = (int)clean_input($conn, 'project_id');

  $revive = revival($conn, $project_id,$username);

  printjson($revive);
}

function revival($conn, $project_id, $username){

  // check if the user requesting exists in the project.
  $qry = "SELECT id, player2, player1 FROM projects WHERE (player1 = '$username' AND id = $project_id AND dead = 1) OR (player2 = '$username' AND id = $project_id AND dead = 1)";
  $project_check = mysqli_query($conn, $qry);
  if ($project_check->num_rows > 0) {
    // check if either projects inventory OR users inventory has a revival potion.
    $player = mysqli_query($conn, "SELECT player_potiontype_revival FROM users WHERE player_username = '$username' AND player_potiontype_revival >= 1");
    $project = mysqli_query($conn, "SELECT potiontype_revival FROM projects WHERE id = $project_id AND potiontype_revival >= 1");
    if ($player->num_rows > 0) {
      $player_result = mysqli_fetch_assoc($player);
      if ($player_result['player_potiontype_revival'] == 0) {
        return array('success' => 0, 'error_log' => 'missing revival potion in player inventory');
      }
      // user has in inventory
      // revive and withdraw.
      $revive = mysqli_query($conn, "UPDATE projects SET dead = 0, health = 100, agility = 100, strength = 100, energy = 100 WHERE id = $project_id");
      if ($revive) {
        $update = mysqli_query($conn, "UPDATE users SET player_potiontype_revival = player_potiontype_revival - 1 WHERE player_username = '$username'");
        if ($update) {
          return array('success' => 1);
        } else {
          return array('success' => 0, 'error_log' => 'Could not update player inventory: ' . mysqli_error($conn));
        }
      } else {
        return array('success' => 0, 'error_log' => 'Could not revive pet: ' . mysqli_error($conn));
      }
    } else if($project->num_rows > 0) {
      $project_result = mysqli_fetch_assoc($project);
      if ($project_result['potiontype_revival'] == 0) {
        return array('success' => 0, 'error_log' => 'missing revival potion in project inventory');
      }

      // project has in project_inventory.
      // revive and withdraw
      $revive = mysqli_query($conn, "UPDATE projects SET dead = 0, health = 100, agility = 100, strength = 100, energy = 100 WHERE id = $project_id");
      if ($revive) {
        $update = mysqli_query($conn, "UPDATE projects SET potiontype_revival = potiontype_revival - 1 WHERE id = $project_id");
        if ($update) {
          return array('success' => 1);
        } else {
          return array('success' => 0, 'error_log' => 'Could not update project inventory: ' . mysqli_error($conn));
        }
      } else {
        return array('success' => 0, 'error_log' => 'Could not revive pet: ' .  mysqli_error($conn));
      }
    } else {
      return array('success' => 0, 'error_log' => 'missing revival potion');
    }
  } else {
    return array('success' => 0, 'error_log' => 'access failed' , 'mysqli_query' => $qry);
  }
}
 ?>
