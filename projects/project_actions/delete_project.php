<?php

require_once('../../config/dbconfig.php');
require_once('../../config/modules.php');
require_once('../../config/translators.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

if ($_POST['delete_project']) {
  $project_id = clean_input($conn, 'project_id');
  $username_request = clean_input($conn, 'username_request');
  $delete = delete_project($conn, $project_id, $username_request);

  printjson($delete);
} else {
  printjson(array('success' => 0));
}

function delete_project($conn,$project_id, $username) {
  $qry = mysqli_query($conn, "SELECT player1, player2 FROM projects WHERE player1 = '$username_request' OR player2 = '$username_request'");
  if (($qry) && ($qry->num_rows > 0)) {
    // valid
    if (mysqli_query($conn, "DELETE FROM projects WHERE id = $project_id")) {
      // also delete ALL battles done AND ongoing.
      $battles = mysqli_query($conn, "UPDATE battles SET battle_finished = 1 WHERE battle_player1_project_id = $project_id OR battle_player2_project_id = $project_id");
      if ($battles) {
        return array('success' => 1);
      } else {
        return array('success' => 0, 'error_log' => 'Could not delete battles! MySQLi error:' . mysqli_error($conn));
      }

    }
  } else {
    return array('success' => 0);
  }
}

 ?>
