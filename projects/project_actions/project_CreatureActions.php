<?php
/*
  all actions regarding projects.
  - feed
  - give water
  - pet
  - train
  - etc..
*/


/*
    create if else for which action the user requested against project..
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);


$_POST = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json');

require('../../config/dbconfig.php');



  $conn = get_connection();

// project id is ideal to be sent properly..
if (isset($_POST['project_id'])) {



  switch ($_POST) {
    case $_POST['project_action_feed']:
      // feed actionco
      break;
    case $_POST['project_action_water']:
        // feed action
      break;
    case $_POST['project_action_pet']:
        // feed action
        break;
    default:
      # code...
      break;
  }


// this file is deprecrated..

}



 ?>
