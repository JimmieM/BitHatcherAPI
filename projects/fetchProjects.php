<?php
// username



// TODO use JSON class from modules in PROD

$_POST = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');
require_once('../config/dbconfig.php');

$conn = get_connection();


// $username = mysqli_real_escape_string($conn, $_POST['username']);

$username = mysqli_real_escape_string($conn, $_POST['username']);

$projectid = mysqli_real_escape_string($conn, $_POST['project_id']);

if (!empty($username)) {
  update_stats($username, $projectid, $conn);
} else {
  echo json_encode(array('success' => 0, 'error_log' => 'missing username'));
  die();
}




// simple constructor to merge the global_execute() array with another array

// DO THIS: $global_execute = execute_arr($global_execute,['src_path' => $src_path]);

// NOT THIS: $global_execute[] = ['x' => 1]; -> different indexes
function execute_arr($arr_, $obj) {
  return array_merge($arr_, $obj);
}

function update_stats($username, $projectid, $conn) {
  include('../config/modules.php');


  if (!empty($projectid)) {
    // fetch specific
    $fetch = mysqli_query($conn,
      "SELECT *
      FROM projects
      WHERE (player1 = '$username' AND id = $projectid)
      OR (player2 = '$username' AND id = $projectid)");

  } else {
    // fetch all
    $fetch = mysqli_query($conn, "SELECT *
    FROM projects WHERE (player1 = '$username' AND dead = 0)
    OR (player2 = '$username' AND dead = 0)");

  }




  // initiate global array for stat constructor to return the new stats in..
  $global_array = array();

  // global array to be sent to client.
  $global_execute = array('success' => 1);

  // do a stat evaluation for each collected project from Database..
  $runs = 0;
  if ($fetch->num_rows > 0) {
    while($row = mysqli_fetch_array($fetch)) {

      // define variables
      $player1 = $row['player1'];
      $player2 = $row['player2'];
      $project_name = $row['name'];
      $project_id = (int)$row['id'];

      $runs++;



      $dead = $row['dead'];
      $in_battle = (int)$row['in_battle'];


      // stuff from DB to use in constructor as math shtuff.
      $latest_server_update = $row['date_latest_update']; // latest script request..
      $latest_fed = $row['date_latest_fed_food'];
      $latest_water = $row['date_latest_fed_water'];
      $latest_treat = $row['date_latest_fed_treat'];
      $strength = $row['strength'];
      $agility = (int)$row['agility'];
      $energy = (int)$row['energy'];
      $health = (int)$row['health'];
      $happiness = (int)$row['happiness'];
      $overfeeding = (int)$row['overfeeding'];
      $hrs_slept = $row['hours_slept'];
      $sleep_until = $row['sleep_until'];

      /*
        create function for the slice value of stats_config

        Depending on current strength & Health -lower stats more..
      */

      // check if the creature is sleeping... If so, reduce amount of HP / agility etc taken away..
      // the creature has to sleep 5-6 HRS per day.. Let these hours be randomly when the user fetch projects..

      /*
        create task to run each 24hr to reset DB row (hours_slept)


      */

      // $now = date("Y-m-d H:i:s");
      //
      // $date_eval_overfeed = round((strtotime($now) - strtotime($latest_server_update_date)) /60);
      //
      // // how many times 1 minutes has passed.
      // $tmin_overfeeding = $date_eval_overfeed / 1;
      //
      // if ($tmin_overfeeding >= 1) {
      //   $tmin_overfeeding;
      //
      // }



      include_once('../config/modules.php');


      if ($hrs_slept < 6) {

        $waken_since = round((strtotime($now) - strtotime($sleep_until)) /60);

        // get how many times 30 minutes has passed.
        $waken_minutes = $waken_since / rand(265,625); // 370 min has to pass before attempting to sleep again..

        // check if sleep_until has passed atleast 3hrs.. Since otherwise this will loop
        if ($waken_minutes >= 1 ) {
          # code...
        }

        $sleepHours = rand(0, 230);

        // do sleep maybe..

        $currentDate = strtotime($now);
        $sleep_calculator = $currentDate+(60*$sleepHours);
        $sleep_until = date("Y-m-d H:i:s", $sleep_calculator);
      }

      /* TODO:
        module for reducing overfed value.
        Over time, overfed will decrease..

        Take time now / latest fed. For each x hr passed, reduce overfed by 1.


        */



      if (($strength <= 20) && ($energy <= 20) && ($agility <= 20)) {
          stats_config(7, $latest_fed, 2 , 'health', $health,$overfeeding, $row, $project_name, 'food', $project_id, $username, $global_array, $latest_server_update);
          stats_config(3, $latest_fed, 5, 'energy', $energy,$overfeeding,$row, $project_name, 'food', $project_id, $username, $global_array, $latest_server_update);
      } else {
        // reduce health
        stats_config(15, $latest_fed, 3, 'health', $health,$overfeeding,$row, $project_name, 'food', $project_id, $username, $global_array, $latest_server_update);

        // calc energy based on strength
        stats_config(15, $latest_fed, 2, 'energy', $energy,$overfeeding,$row, $project_name, 'food', $project_id, $username, $global_array, $latest_server_update);
        // 30 min, date of latest fed, slice by 2, insert into health.
        //stats_config(100, $latest_water, 3, 'water', $water, $rows);
      }



          // TODO: Last of all, check latest treat_date, and evaluate the level of happiness for the animal.

          // Happiness will sink if latest treat was maybe 10 hrs ago, and sink with 15%.
          // but it will sink even more, if the latest_water and food was around 5-10 hrs ago.
          // :D

          // if health is below 30, the happiness will sink even more,,, ETC:

          // TODO: $feed_Type in stats_config remains unused.


      // based on health
      if ($health < 30) {
        stats_config(10, $latest_fed, 4, 'strength', $strength,$overfeeding,$row, $project_name, 'food', $project_id, $username, $global_array, $latest_server_update);

        // happiness
        stats_config(20, $latest_treat, 3, 'happiness', $happiness,$overfeeding,$row, $project_name, 'food', $project_id, $username, $global_array, $latest_server_update);
      } else if ($health > 70){
        stats_config(10, $latest_fed, 3, 'strength', $strength,$overfeeding,$row, $project_name, 'food', $project_id, $username, $global_array, $latest_server_update);

        /// happiiness
        stats_config(5, $latest_treat, 4, 'happiness', $happiness,$overfeeding,$row, $project_name, 'food', $project_id, $username, $global_array, $latest_server_update);
      } else {
        stats_config(5, $latest_fed, 2, 'strength', $strength,$overfeeding,$row, $project_name, 'food', $project_id, $username, $global_array, $latest_server_update);

        // happiness
        stats_config(70, $latest_treat, 5, 'happiness', $happiness,$overfeeding,$row, $project_name, 'food', $project_id, $username, $global_array, $latest_server_update);
      }


      // AGILITY
      // send in $latest_fed then agility, and construct a percentage of agilirt reduce depening on the last fed date.
      stats_config(25, $latest_fed, 5, 'agility', $agility,$overfeeding, $row, $project_name, 'food', $project_id, $username, $global_array, $latest_server_update);

      // every hour passed since latest water given, reduce agility sliced by 4.
      stats_config(60, $latest_water, 2, 'agility', $agility,$overfeeding,$row, $project_name, 'water', $project_id, $username, $global_array, $latest_server_update);




    } ### while loop end. ###


    //echo json_encode(array('runs' => $runs));die();
    execute_projects($global_execute, $username, $conn, $projectid);

  }


}

function src_path($stage) {
  switch ($stage) {
    case 0: // only for bird as egg.
        return 'egg';
      break;

    case 1: // only for bird as egg.
        return 'small';
      break;

    case 2: // only for bird as egg.
        return 'medium';
      break;

    case 3: // only for bird as egg.
        return 'large';
      break;

  }
}

// constructor for health & water.
function stats_config($min, $latest_fed_type, $sliced, $rowType, $origin_stat,$overfeeding,$vanilla_array, $project_name, $feed_type, $project_id, $username, $global_array, $latest_server_update_date) {
  // TODO: this param info needs to be updated...

  // rowtype: What you wanna update in DB
  // $min == per 30min or something
  // $feed_type == water, food, etc. in DATE from DB
  // $sliced == /2 or /3 while /3 returns a smaller value. 70/3 = 23.3 while 70/2 = 35. The returning value will be decreased to the HP.
  // A smaller value will - smaller value on HP.
  // 70/2 = 35. HP - 35.

  // ini_set('display_errors', 1);
  // ini_set('display_startup_errors', 1);
  // error_reporting(E_ALL);
  //
  // $_POST = json_decode(file_get_contents('php://input'), true);
  //
  // header('Content-Type: application/json');

  require_once('../config/dbconfig.php');

  date_default_timezone_set('UTC');

  $now = date("Y-m-d H:i:s");

  // get amount of minutes by both dates..
  $date_eval_server = round((strtotime($now) - strtotime($latest_server_update_date)) /60);
  $date_eval_feed_type = round((strtotime($now) - strtotime($latest_fed_type)) /60);



      //echo json_encode(array('now' => $now, 'server_date' => $latest_server_update_date, 'latest_fed' => $latest_fed_type, 'date_eval_serv' => $date_eval_server, 'date_eval_feed' => $date_eval_feed_type));die();

  $date_calc;
  // if servers minutes difference from current date is LESS than latest fed..
  if ($date_eval_server < $date_eval_feed_type) {
    // calc on latest_server_update instead of latest fed with food/water..
    $date_calc = $latest_server_update_date;
  } else {
    // calc on latest fed variables.
    $date_calc = $latest_fed_type;
  }

  // get amount of minutes passed between latest "fed" and current date.
  // fed, water, etc.2
  $timediffer = round((strtotime($now) - strtotime($date_calc)) /60);

  // get how many times 30 minutes has passed.
  $tmin = $timediffer / $min;

  // if atleast 30 minutes has passed once..
  // while 30 min is $min which can be anything... 25min, 60 min.. Depending on $min that are passed into the function as param..
  // date_latest_update cannot be updated if tmin hasnt passed once.
  if ($tmin >= 1) {

    /*
      Agility, strength and energy overfeeding module

    */

    // check if the creature is being overfed..

    // overfed > 4 - minimal cause

    // only strength, energy and agility can be affected of overfeeding.
    if (($rowType === 'strength') || ($rowType === 'energy') || ($rowType === 'agility')) {
      if ($overfeeding >= 2) {
        // overfeeding impact
        $sliced = $sliced  - 1.4; // increase the amount of $rowType being decreased.
      } else if($overfeeding >= 5) {
        $sliced = $sliced -2.2; // increase the amount of $rowType being decreased.
      }
      // else, keep at 0 / null
    }

    // while the pets stats will be sliced down to even bigger numbers.. + -10% or somehting..



    // module || the new stat for the creature
    $stat = $origin_stat * ((100-$tmin/$sliced) / 100);

    // change value of the stat to $rows

    // round the new stat up. 24.123 will be 24. etc..
    $roundStat = ceil($stat);

    if ($roundStat >= 99) {
      $roundStat = 100;
    } elseif ($roundStat <= 0) {
      $roundStat = 0;
    }

    // stat at index in fetched array is now the new stat =)
    $vanilla_array[$rowType] = $roundStat;

    // you have to push the new stuff now to DB
    try {
      $conn = get_connection();

      // if try doesn't work.. Re-do it in catch()
      $qry = mysqli_query($conn, "UPDATE projects SET $rowType = '$roundStat' , date_latest_update = '$now' WHERE id LIKE '$project_id'");


    } catch (Exception $e) {
      // TODO: this error log reporting should be checked if it works..

      // log dis shiet.
      $s = "ERROR: UPDATE " . $rowType . " TO " . $roundStat . " \n Project name: " .$project_name . " \nproject id: " . $project_id;

      $f = mysqli_query($conn, "INSERT INTO hatch_requests
      (username_request, date, type, error_log)
      VALUES
      ('$username', '$now', '$s', '$e')");

      $fBoolean;
      if ($f) {
        $fBoolean = true;
      }

      // return message to application about server / conn failure...
      $error_log;
      if ($fBoolean) {
        $error_log = "Error log in hatch_requests has been created." . "\n\n Username: " . $username . "\n at time" . $now . " Project name: ". $project_name . "project id: " . $project_id;
      } else {
        $error_log = "Error log failed! exception: " . $e . " \n\n Username: " . $username . "\n at time" . $now . " Project name: ". $project_name . "project id: " . $project_id;
      }
      $global_array[] = ['stat_update' => 0 , "stat_update:error" => $error_log];

      echo json_encode($global_array);
    }
  }
}




// function to be executed after stats evaluation
function execute_projects($global_execute,$username,$conn,$projectid){

  require('../config/translators.php');
  date_default_timezone_set('UTC');

  $now = date("Y-m-d H:i:s");

  $fetch = '';

  if (!empty($projectid)) {
    // fetch specific
    $fetch = mysqli_query($conn, "SELECT *
      FROM projects
      WHERE (player1 = '$username' AND id = $projectid)
      OR (player2 = '$username' AND id = $projectid)");
  } else {
    // fetch all
    $fetch = mysqli_query($conn, "SELECT *
      FROM projects
      WHERE player1 LIKE '$username'
      OR player2 LIKE '$username'");
    //$fetch = mysqli_query($conn, "SELECT * FROM projects WHERE player1 = '$username' OR player2 = '$username'");
  }

  $rows = array();
  while($row = mysqli_fetch_array($fetch)) {
    // define variables
    $project_id = (int)$row['id'];
    $player1 = $row['player1'];
    $player2 = $row['player2'];

    $egg_creature = (int)$row['egg_creature'];
    $creature = $row['creature'];
    $health = $row['health'];
    $energy = $row['energy'];
    $agility = $row['agility'];
    $strength = $row['strength'];
    $in_battle = (int)$row['in_battle']; // boolean 1 or 0
    $in_battle_battle_id = (int)$row['in_battle_battle_id']; // the ID of the battles the project is in,
    $in_battle_battle_id = (int)$row['in_battle_battle_id'];

    $stage = (int)$row['stage'];
    $src_version = $row['src_version'];
    $src_path = $row['src_path'];

    $overfeeding = $row['overfeeding'];
    $latest_fed = $row['date_latest_fed_food'];
    $latest_water = $row['date_latest_fed_water'];
    $date_created = $row['date_created'];
    $life_span = $row['life_span_minutes'];



    // TODO IMPORT MODULES maby.

    try {
      // check if project is in battle
      if ((($in_battle == 1) && ($in_battle_battle_id != 0))) {


        // find the current battle the project is involved in.
        // also grab info about the winner. Name etc.

        // only get active battles. Whhich has 2 opponents.



        // PVP
        $active_battles = mysqli_query($conn,"SELECT battles.battle_pvp_pve,battles.battle_id,battles.battle_player1_project_id, battles.battle_player2_project_id, battles.battle_player1_id, battles.battle_player2_id, battles.battle_until_date, battles.battle_winner_project_id, battles.battle_winner_reward_pet, battles.battle_winner_reward_bitfunds
          FROM battles, projects
          WHERE battles.battle_id = $in_battle_battle_id
          AND battles.battle_winner_project_id NOT LIKE 0
          AND battles.battle_finished = 0
          AND battles.battle_pvp_pve = 'pvp'
          LIMIT 1");

        // PVE!
        $active_pve_battles = mysqli_query($conn,"SELECT battles.battle_pvp_pve,battles.battle_id,battles.battle_player1_project_id, battles.battle_player2_project_id, battles.battle_player1_id, battles.battle_player2_id, battles.battle_until_date, battles.battle_winner_project_id, battles.battle_winner_reward_pet, battles.battle_winner_reward_bitfunds
          FROM battles, projects
          WHERE battles.battle_id = $in_battle_battle_id
          AND battles.battle_finished = 0
          AND battles.battle_pvp_pve = 'pve'
          LIMIT 1");


        // PVP
        $battle_looking_for_opponent = mysqli_query($conn, "SELECT battles.battle_pvp_pve, battles.battle_id,battles.battle_player1_project_id, battles.battle_player2_project_id, battles.battle_player1_id, battles.battle_player2_id, battles.battle_until_date, battles.battle_winner_project_id, battles.battle_winner_reward_pet, battles.battle_winner_reward_bitfunds
          FROM battles, projects
          WHERE battles.battle_id = $in_battle_battle_id
          AND battles.battle_player2_project_id = 0
          AND battles.battle_finished = 0
          AND battles.battle_pvp_pve = 'pvp'
          LIMIT 1");

        if (($active_battles->num_rows > 0) || ($active_pve_battles->num_rows > 0)) {

          // TODO TEST THIS!
          $array_fetch = '';
          if ($active_battles->num_rows > 0) {
            $array_fetch = $active_battles;
          } else if($active_pve_battles->num_rows > 0) {

            $array_fetch = $active_pve_battles;
          } else {
            $global_execute = execute_arr($global_execute, ['battle_error_log' => 'Failed to get correct active battle!']);
          }

          while ($battle_rows = mysqli_fetch_array($array_fetch)) {

            $battle_pvp_pve = $battle_rows['battle_pvp_pve'];
            $player1_id = (int)$battle_rows['battle_player1_id'];
            $player1_project_id = (int)$battle_rows['battle_player1_project_id'];
            $player2_id = (int)$battle_rows['battle_player2_id'];
            $player2_project_id = (int)$battle_rows['battle_player2_project_id'];
            $battle_winner_project_id = (int)$battle_rows['battle_winner_project_id']; // the winner is set by "enter_battle.php" on entering the battle. Just get the value and print if the battle is done.
            $battle_winner_project_name = '';
            $battle_looser_project_id = '';
            $battle_looser_project_name = '';
            $battle_until_date = $battle_rows['battle_until_date'];

            // rewards.
            $battle_winner_reward_pet = (int)$battle_rows['battle_winner_reward_pet'];
            $battle_winner_reward_bitfunds = (int)$battle_rows['battle_winner_reward_bitfunds'];

            $bind_project_variables = mysqli_query($conn,
            "SELECT id,name FROM projects
            WHERE id IN ($player1_project_id, $player2_project_id)");

            $arr = array();

            if ($bind_project_variables->num_rows > 0) {
              while ($project_variables = mysqli_fetch_array($bind_project_variables)) {
                // if this is your project on INDEX
                if ((int)$project_variables['id'] === $project_id) {
                  $battle_project_name = $project_variables['name'];
                } else {
                  $battle_opponent_project_name = $project_variables['name'];
                }
                // get winners project name.
                if ((int)$project_variables['id'] === $battle_winner_project_id) {
                  $battle_winner_project_name = $project_variables['name'];
                } else {
                  $battle_looser_project_name = $project_variables['name'];
                  $battle_looser_project_id = $project_variables['id'];
                }
              }

            } else {
              $global_execute = execute_arr($global_execute, ['battle_error_log' => 'failed to fetch projects' , 'battle_player1_project_id' => $battle_player1_project_id, 'battle_player2_project_id' => $battle_player2_project_id]);
            }



            // check if battle is done.
            if (strtotime($now) >= strtotime($battle_until_date)) {

              // release project from battle. TODO UNMARK!
              $return = mysqli_query($conn,
                "UPDATE projects
                SET in_battle = 0, in_battle_battle_id = 0
                WHERE id = $project_id");

              //$return = true;

              // // battles_won should be incremented for the winner.
              // // TODO FIX!
              //
              // // usernames of both project participants.
              // $project_player1 = '';
              // $project_player2 = '';
              //
              // $opponent_project_player1 = '';
              // $opponent_project_player2 = '';
              //
              // // get both players involved in the project that won battle.
              //
              // $battles_played = mysqli_query($conn, "SELECT UPDATE users SET player_battles_entered = player_battles_entered + 1 WHERE () ");
              //
              // $project_winner = mysqli_query($conn, "SELECT projects.player1, projects.player2 FROM projects WHERE id IN ($battle_winner_project_id, $battle_looser_project_id) JOIN  ");
              // if ($project_winner->num_rows > 0) {
              //   while ($players = mysqli_fetch_array($project_winner)) {
              //     $project_player1 = $players['player1'];
              //     $project_player2 = $players['player2'];
              //   }
              // }
              //
              // $update_battles = mysqli_query($conn, "UPDATE users
              //   SET player_battles_won = player_battles_won + 1
              //   WHERE player_username
              //   IN ('$project_player1', '$project_player2')");


              // if project could be released from in_battle status.
              if ($return) {

                // check if user that is fetching, project won the battle.
                if ($battle_winner_project_id == (int)$project_id) {
                  // if your project won

                  // give the resource bundle.
                  include('gain_resources_project.php');
                  $resources = gain_resources_project($project_id);

                  // give user BitFunds
                  $bitfunds = mysqli_query($conn, "UPDATE users
                    SET bitfunds = bitfunds + $battle_winner_reward_bitfunds WHERE player_username = '$username'");

                  // initate boolean, for IF the awards has been given.
                  $battle_awards = '0';

                  // if new pet function from shop returned true.
                  if ($bitfunds) {
                    $battle_awards = '1';
                    // give the rewards to the winner.
                    include('../shop/shop.php');

                    if ($battle_winner_reward_pet !== 0) {
                      $new_pet = purchase_item($now, $conn, $battle_winner_reward_pet, $username, false); // The pet, the username, pay = false.

                      if ($new_pet['success' == 1]) {
                        $battle_awards = '1';
                      } else {
                        $battle_awards = '0';
                      }
                    }


                    // $get_winner = mysqli_query($conn, "SELECT project_name
                    //   FROM projects
                    //   WHERE id = $battle_winner_project_id");


                  } else {
                    // TODO this should be logged.
                    // run queries again?
                  }
                  $global_execute = execute_arr($global_execute,
                  ['battle_pvp_pve' => $battle_pvp_pve,
                  'battle_winner' => '1',
                  'battle_winner_project_name_opponent' => $battle_looser_project_name,
                  'battle_done' => '1',
                  'battle_awards' => $battle_awards ,
                  'battle_reward_resources' => $resources,
                  'battle_reward_bitfunds' => $battle_winner_reward_bitfunds,
                  'battle_reward_pet' => $battle_winner_reward_pet]);

                } else {
                  // your project lost. Terminate.
                  // TODO UNCHECK THIS!
                  $dead = 0;

                  $die_rand = rand(0,99);
                  if ($die_rand < 66) {
                    $dead = 1;
                    $kill = mysqli_query($conn, "UPDATE projects SET dead = 1 WHERE id = $project_id");
                  }

                  //$kill = mysqli_query($conn, "DELETE FROM projects WHERE id = $project_id");
                  $global_execute = execute_arr($global_execute, ['battle_pvp_pve' => $battle_pvp_pve, 'battle_looser_died' => $dead ,'battle_winner' => '0', 'battle_winner_project_name_opponent' => $battle_winner_project_name,'battle_done' => '1']);
                }
              } else {

                $global_execute = execute_arr($global_execute, ['battle_error_log' => 'Could not release project from battle: ' . mysqli_error($conn)]);
              }
              // set battle as finished TODO

            } else {
              // battle is not done.
              $battle_until_date_cur = gmdate('d.m.Y H:i', strtotime($battle_until_date));

              $time = ($now - $battle_until_date)/60;

              $global_execute = execute_arr($global_execute, ['battle_pvp_pve' => $battle_pvp_pve, 'time' => $time,'battle_player2_joined' => 1,'battle_done' => '0','battle_until_date' => $battle_until_date_cur, 'battle_until_date_server' => $battle_until_date , 'server_time' => $now , 'battle_winner' => $battle_winner, 'in_battle_opponent' => $battle_opponent_project_name]);
            }
          }

        } else if($battle_looking_for_opponent->num_rows > 0) {
          $global_execute = execute_arr($global_execute, ['battle_player2_joined' => 0]);

        } else {
          // something is wrong..
          // release both projects from their battles. AND set the battle as Done / deleted
          $release = mysqli_query($conn, "UPDATE projects SET in_battle = 0, in_battle_battle_id = 0 WHERE id = $project_id");
          $global_execute = execute_arr($global_execute, ['battle_error_log' => 'Battle could not be found!', 'query' => $string]);

        }
      }
    } catch (Exception $e) {
      $global_execute = execute_arr($global_execute, ['error_catch' => $e]);
    }




    // calculate minutes
    $timespan = round((strtotime($now) - strtotime($date_created)) /60);

    $stages = 0;

    $max_stage = 33.3333333333;
    if ($egg_creature == 1) {
        $max_stage = 25;
    }

    // stage 1 = 25, stage 2 = 50, stage 3 = 75, stage 4 = 100
    for ($i = 0; $i < $stage; $i++) {
      $stages = $stages + $max_stage; // one-fourth
    }

    // add some params to $row
    if ($timespan >= $life_span) {
      // DEAD!
      $kill = mysqli_query($conn, "UPDATE projects SET dead = 1 WHERE id = $project_id");
    } else {
      // stage
      // calc $stage which COULD be 50. So if current minutes is higher than 50% of lifespan, then pursue next stage. duh..
      $next_stage = ($stages / 100) * $life_span;

      if ($timespan >= $next_stage) {
        // time to level up bro.
        $stage = $stage + 1;
        $query =  "UPDATE projects SET stage = $stage WHERE id = $project_id";
        $staging = mysqli_query($conn, $query);

        // if its an egg, and just turned into a bird, aka stage 1.
        if ($egg_create == 1 || $stage == 1) {
          // update agility,strength,health,happiness.
          $stats = mysqli_query($conn, "UPDATE projects SET health = 55, agility = 55, strength = 55, happiness = 20 WHERE id =  $project_id");

        }

      }
      // nextstage = lived until now / when to die.. IN mins

      /*
      check if next stage is higher than 1/4, if so level to next stage. then check if 2/4 etc.

      // born -> now
      */

    }
    //$src_size = src_path($stage);

    $creature = animal_translator($creature);

    $extension = '.png';
    if (($stage === 1) && ($egg_creature === 1)) {
      $extension = '.gif';
    }
    $src_path = $src_path . $stage . $extension;

    //$global_execute[] = ['src_path2' => $src_path];

    $global_execute = execute_arr($global_execute,['src_path' => $src_path]);

    //

    $current_status = '';

    $status_array = array();

    // check if the stat updates killed the creature..
    if ($health <= 0) {
      try {
        $global_execute = execute_arr($global_execute,['dead' => 1]);
        $kill = mysqli_query($conn, "UPDATE projects SET dead = 1 WHERE id = $project_id");
      } catch (Exception $e) {
        // a cold
      }

    // if ($global_execute['battle_looser_died'] == 1) {
    //   $status_array = array('current_status' => 'Your pet died in battle'); // dead.
    // } else {
    //   $status_array = array('current_status' => 'Your pet has died by aging'); // dead.
    // }

    } else {
      // calculate current_status by status variables with the new updated stats.
      if ($energy < 20) {
        # needs more food

        $status_array[] = "Your creature needs energy. Try giving it a treat.";
      }

      if ($strength < 30) {
        $status_array[] =  "Your creature is lacking strength. You'll have to feed it.";
      }
      if ($overfeeding >= 3) {
        $status_array[] = "Your creature is being overfed!";
      }

      // get an estimate of how many hours has passed since latest fed.
      $timediffer_fed = round((strtotime($now) - strtotime($latest_fed)) /60);
      $latest_fed_min = $timediffer_fed / 60;
      // check if 24 hrs has passed..
      if ($latest_fed_min >= 24) {
        $status_array[] =  "Your creature needs food.";
      }


      // get an estimate of how many hours has passed since latest fed with water.
      $timediffer_water = round((strtotime($now) - strtotime($latest_water)) /60);
      $latest_water_min = $timediffer_water / 60;
      // check if 24 hrs has passed..
      if ($latest_water_min >= 24) {
        // evaluate if latest fed in minutes is higher than water. Since food is more important.
        if ($latest_fed_min <= $latest_water_min) {
          $status_array[] = "Your creature needs water to survive.";
        }
      }

      $current_status = $status_array[array_rand($status_array)];

      $status_array = array('current_status' => $current_status);
    }


    // if you are player 1
    if ($username === $player1) {
      #  // check if you have a player 2 connected to your project..
      if (empty($player2)) {
        // since you're alone, you have no secondary player connected to the project..
        // just push the Project data.
        $rows[] = array_merge($row,$status_array, $global_execute);

      } else {
        // fetch the Player 2's information
        $fetch_player2 = mysqli_query($conn, "SELECT player_avatar, player_level, player_id
          FROM users
          WHERE player_username LIKE '$player2'");

        while ($player2_details = mysqli_fetch_array($fetch_player2)) {
          // push the selected information to rows.

          $rows[] = array_merge($row, $player2_details , ["request_sent_by_player1" => "1"], $status_array, $global_execute);

        }
      }
      // you're player2
      // as player 2, you always have a connected player to the project.
    } else {

      $fetch_player1 = mysqli_query($conn, "SELECT player_avatar, player_level FROM users WHERE player_username LIKE '$player1'");

      while ($player1_details = mysqli_fetch_array($fetch_player1)) {
        $rows[] = array_merge($row, $player1_details, ["request_sent_by_player1" => "0"], $status_array, $global_execute);
      }
    }

    // clean $global_execute..
    // this array HAS to be cleaned before attempting to initiate another project.
    $global_execute = [];
  }

  // execute projects
  echo json_encode($rows);

  mysqli_close($conn);
}
 ?>
