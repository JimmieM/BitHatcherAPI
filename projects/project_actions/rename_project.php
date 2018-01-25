<?php

require_once('../../config/dbconfig.php');
require_once('../../config/modules.php');
require_once('../../config/translators.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

if ($_POST['rename_project']) {
  $project_id = clean_input($conn, 'project_id');
  $username_request = clean_input($conn, 'username_request');
  $new_project_name = clean_input($conn, 'project_new_name');

  $new_name = rename_project($conn, $project_id, $new_project_name, $username_request);

  printjson($new_name);
} else {
  printjson(array('success' => 0));
}

function rename_project($conn, $project_id, $new_project_name, $username_request) {

  $username = mysqli_query($conn, "SELECT player1, player2 FROM projects WHERE player1 = '$username_request' OR player2 = '$username_request'");

  if (($username) && ($username->num_rows > 0)) {
    if (mysqli_query($conn, "UPDATE projects SET name = '$new_project_name' WHERE id = $project_id")) {
      return array('success' => 1);
    } else {
      return array('success' => 0);
    }
  } else {
    return array('success' => 0);
  }
}

 ?>
