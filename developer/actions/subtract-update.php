<?php

/*
script to subtract latest update in a project. Mainly used to see how health goes down during x amount of hours.
*/

require('../../config/dbconfig.php');
require('../../config/modules.php');
require('../../config/translators.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

if (isset($_POST['project_id']) && (isset($_POST['hours']))) {
  $hours = $_POST['hours'];
  $project_id = $_POST['project_id'];
  $date_target = $_POST['target_date'];

  if ($hours == 24) {
    $days = 1;
  } elseif($hours == 48) {
    $days = 2;
  }

  if (!empty($days)) {

    date_default_timezone_set('America/Chicago'); // CDT

    $days = (strtotime('-' . $days . 'day'));

    $huh = date('Y-m-d H:i:s',$days);

  } else {

    $hours = (strtotime('-' . $hours . 'hour'));

    $huh = date('Y-m-d H:i:s',$hours);
  }

  if ($date_target === 1) {
    $qry = mysqli_query($conn, "UPDATE projects SET date_latest_update = '$huh' WHERE id = '$project_id'");
  } else if($date_target === 2) {
    $qry = mysqli_query($conn, "UPDATE projects SET date_latest_fed_food = '$huh' WHERE id = '$project_id'");
  }



  if ($qry) {
    printjson(array('success' => 1, 'subtracted_to' => $huh));
  }
}

 ?>
