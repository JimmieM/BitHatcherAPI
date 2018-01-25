<?php

  require('../config/modules.php');
  require('../config/dbconfig.php');
  require('../config/translators.php');

  $post = new json_post();

  $post->__construct(false);

  $conn = get_connection();

  // get data from the user that has requested a new project.
  $player1 = clean_input($conn,'player1');
  $player2 = clean_input($conn,'player2');
  $name = clean_input($conn,'projectname');
  $duo_bool = clean_input($conn,'duo');

  // this will be the selector which lets the user pick a mammal or plant in the application.
  $creature_selection = clean_input($conn, 'creature');

  $creature;
  $egg_creature;
  $versions;
  $stage;
  // fetch which creature the user selected.. Plant or creature.

  // Creature
  if ($creature_selection === '1') {

    $num = rand(1, 4);

    switch ($num) {
      case 1:
        $creature = 0; // bird <-> egg
        $egg_creature = 1;
        $versions = 3;
        $stage = 0;
        break;
      case 2:
        $creature = 1; // fox
        $egg_creature = 0;
        $versions = 3;
        $stage = 1;
        break;
      case 3:
        $creature = 2; // rabbit
        $egg_creature = 0;
        $versions = 2;
        $versions = 3;
        $stage = 1;
        break;
      case 4:
        $creature = 3; // red panda
        $egg_creature = 0;
        $versions = 3;
        $stage = 1;
        break;
    }

    $current_status = "Status delayed..";
    $agility = 0;
    $strength = 0;

    //die(json_encode(array('BLA!' => $creature, 'BLA!' =>$egg_creature, 'BLA!' =>$versions,'BLA!' => $agility, 'BLA!' =>$strength)));

    if ($egg_creature === 1) {
      $current_status = "Waiting for egg to hatch";
      $agility = 0;
      $strength = 0;

    } else {
      $current_status = "something better..";

      // it can be a fox, so it's basically alive and has some kind of strength of brething etc..
      // assign a random value for strength n agility.
      $min = 1;
      $max = 76;
      $agility = rand($min,$max);
      $strength = rand($min,$max);
    }
    // create a new project

    try {
      $response = array();

      $animal_path = animal_translator($creature);

      // choose animal version
      $version = rand(1,$versions);

      // convert to string
      $version = strval($version);

      $version = 'v' . $version;

      //$src_path = "img/animals/" . $animal_path . "/" . $version;
      $src_path = sprintf('img/animals/%s/%s/stage_',$animal_path, $version);

      //creature: chosen in Switch Case function, "bird", "fox", or something
      //egg_Creature, basically a bool, where 1 is true and 0 is false. In case of Fox, the egg_create is false 0
      $qry = mysqli_query($conn,"INSERT INTO projects
        (creature,
        egg_creature,
        name,
        player1,
        player2,
        date_created,
        current_status,
        strength,
        agility,
        date_latest_fed_food,
        date_latest_fed_water,
        date_latest_update,
        stage,
        src_path,
        src_version)
        values (
        '$creature',
        '$egg_creature',
        '$name',
        '$player1',
        '$player2',
        '$now',
        '$current_status',
        '$strength',
        '$agility',
        '$now',
        '$now',
        '$now',
        '$stage',
        '$src_path',
        '$version')");

        if ($qry) {
          echo json_encode(array('success' => 1));
          //printjson(array('path' => $src_path, 'strength' => $strength, 'agility' => $agility, 'current_status' => $current_status, 'creature' => $creature, 'versions_available' => $versions, 'version_picked' => $version), true);
          die();
        } else {
          echo json_encode(array('success' => 0 , 'error_log' => mysqli_error($conn)));
          die();
        }

    } catch (Exception $e) {
      echo json_encode(array('success' => 0));
      die();
    }

  } else if($creature_selection === 2) { // plant
    # code...
  }





 ?>
