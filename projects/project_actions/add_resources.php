<?php

require('../../config/modules.php');
require('../../config/translators.php');

$_POST = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json');


require('../../config/dbconfig.php');


$conn = get_connection();


$resource_item_name = clean_input($conn,'resource_item_name');
$resource_item_amount = clean_input($conn,'resource_item_amount');
$project_id = clean_input($conn,'project_id');
$username = clean_input($conn,'username_request');

// translate
$resource_item_name = food_translator($resource_item_name);


try {
  // check if user has the resource item in the inventory

  // return player_'$resource'.
  $select_resource = return_player_resource($resource_item_name);


  $qry = mysqli_query($conn, "SELECT $select_resource FROM users WHERE player_username LIKE '$username' LIMIT 1");


  while ($result = mysqli_fetch_array($qry)) {
    $user_inventory_resource = $result[$select_resource];

    // check if resource typ in users inventory is higher or equals to the input.
    if ($user_inventory_resource >= $resource_item_amount) {
      // the swap may continue.

      // decrease users inventory resource by the amount of input
      $user_inventory_resource = $user_inventory_resource - $resource_item_amount;

      // insert new resource to users inventory


      // insert $resource_amount into project now.
      $query = "UPDATE projects SET $resource_item_name = $resource_item_name + $resource_item_amount WHERE id = $project_id";
      $project_resource = mysqli_query($conn, $query);

      // decrease users inventory if the project resource was successfull.
      if ($project_resource) {
        $user_inventory = mysqli_query($conn, "UPDATE users SET $select_resource = '$user_inventory_resource' WHERE player_username LIKE '$username'");
        echo json_encode(array('success' => 1, 'resource_updated_name' => $resource_item_name , 'resource_updated_amount' => $resource_item_amount));
      } else {
        echo json_encode(array('success' => 0, 'error_log' => mysqli_error($conn)));
      }


    } else {
      echo json_encode(array('success' => 0 , 'error_log' => 'resource amount does not exist in inventory'));
    }
  }
} catch (Exception $e) {
  echo json_encode(array('success' => 0, 'error_log' => $e));
}



 ?>
