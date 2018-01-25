<?php
include_once('../../config/modules.php');
include_once('../../config/dbconfig.php');
include_once('attack_rules.php');

$conn = get_connection();

if (($_SERVER['REQUEST_METHOD'] === 'POST')) {
  $post = new json_post();

  $post->__construct(false);

  $conn = get_connection();

  $username_request = clean_input($conn, 'username_request');
  $project_id = clean_input($conn, 'project_id');

  if (!empty($username_request) && !empty($project_id)) {
    $attacks = new attack_rules($conn, $username_request, $project_id);

    $attack_rules = $attacks->attack_rules();

    $current_attacks = $attacks->pet_attacks($conn, $project_id);

    // here....
    $attacks->find_achievements();

    $owned = array(); // attacks the pet has.
    $available = array(); // attakcs the pet CAN purchase
    $unavailable = array(); // attacks the pet cant purchase.


    printjson(array($current_attacks, $attack_rules), true);
  } else {
    printjson(array('success' => false, 'error_log' => 'Missing username and/or project id'), true);
  }


}

 ?>
