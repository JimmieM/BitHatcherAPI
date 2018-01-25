<?php

// un commennt TODO!
  // require('../config/modules.php');

  // $post = new json_post();
  //
  // $post->__construct(false);


  // ini_set('display_errors', 1);
  // ini_set('display_startup_errors', 1);
  // error_reporting(E_ALL);

  $projects = new projects();

  class stats_target
  {
    public $array;

    public $min; // amount of min which has to pass before the inserted stat can be affected.
    public $sliced; // amount of decreasion for the stat. TBH I can't remember what it does.

    // used to recognize stat for query. "UPDATE projects SET $stats_string = $stat"
    public $stats_string;
    public $stat;

    public $latest_fed_type; // apply either food or water.

    function __construct($min, $sliced, $stats_string, $stat, $latest_fed_type)
    {
      $this->min = $min;
      $this->sliced = $sliced;
      $this->stats_string = $stats_string;
      $this->stat = $stat;
      $this->latest_fed_type = $latest_fed_type;

      return;

    }
  }



 class helpers
 {

   public $stage;

   function __construct()
   {
     # code...
   }


   function src_path($stage) {
     switch ($stage) {
       case 0: // only for bird as egg.
           return 'egg';
         break;

       case 1:
           return 'small';
         break;

       case 2:
           return 'medium';
         break;

       case 3:
           return 'large';
         break;
     }
   }
 }


 class prod extends projects
 {
   public $now;
   function __construct()
   {
     $this->username = 'jimmie';




     $this->reset_projects();
   }
 }




class projects
{

  public $username; // $_POST incoming from client
  public $standalone_project_id; // $_POST incoming from client

  // project variables fetched from BitHatcher tables MySQL
  public $player1;
  public $player2;
  public $creature;
  public $project_id; //
  public $project_name;

  public $in_battle;

  public $latest_server_update;
  public $latest_fed;
  public $latest_water;
  public $latest_treat;

  public $overfeeding;
  public $hrs_slept;
  public $sleep_until;
  public $dead;
  public $stage;

  public $strength;
  public $agility;
  public $energy;
  public $health;
  public $happiness;

  // containers
  public $global_array; // used to contain global data within execution of class
  public $global_execute; // used to pass data into array to be executed to client.
  public $rows; // rows are executed in exection function.
  public $achievements_container; // returnable from earn_achievement funciton

  // includes.
  public $modules;
  public $db;
  public $attacks;
  public $helpers;
  public $translators;
  public $stat_usage;

  // connections
  public $conn;

  // POST modules.
  public $_POST;

  // time
  public $now;
  public $date_time;

  // errors
  public $errors;

  public function reset_projects() {
    $qry = mysqli_query($this->conn, "UPDATE projects SET dead = 0, health = 100, energy = 100, agility = 100, strength = 100, date_latest_fed_food = '$this->now'");
  }

  public function execute_arr($arr_, $obj) {
    return array_merge($arr_, $obj);
  }

  public function now() {
    /*
    construct NOW.
    */

    $this->now = $this->date_time;
  }

  function __construct()
  {
    // initialize class.
    header('Content-Type: application/json');
    $this->modules = include_once('../config/modules.php');

    $this->db = include_once('../config/dbconfig.php');
    $this->conn = get_connection();

    $this->translators = include_once('../config/translators.php');
    // $this->helpers = helpers();


    $this->_POST = json_decode(file_get_contents('php://input'), true);

    $this->date_time = date_default_timezone_set('UTC');

    $this->now = date("Y-m-d H:i:s");

    // initialize Arrayssssss
    $this->global_execute = array();
    $this->achievements_container = array();

    // get POSTS.
    $this->username = mysqli_real_escape_string($this->conn, $this->_POST['username']);

    $this->standalone_project_id = mysqli_real_escape_string($this->conn, $this->_POST['project_id']);

    //$this->reset_projects();

      //show_errors();
    // init
    if (!empty($this->username) or ($this->username == 'jimmie')) {
      // call member funtion to init process.

      write_to_file('project_fetches', $this->username . " began to fetch projects at: " . now() . " \n\n");

      $this->update_stats();
    } else {
      printjson(array('success' => 0, 'error_log' => 'missing username ' . $this->username));

    }
  }



  public function update_stats() {

    // fetch all
    $fetch = mysqli_query($this->conn, "SELECT *
    FROM projects
    WHERE (player1 = '$this->username' AND dead = 0)
    OR (player2 = '$this->username' AND dead = 0)");

    if (!empty($this->standalone_project_id)) {
      // fetch specific
      $fetch = mysqli_query($this->conn,
        "SELECT *
        FROM projects
        WHERE (player1 = '$this->username' AND id = $this->standalone_project_id)
        OR (player2 = '$this->username' AND id = $this->standalone_project_id)");
    }

    // initiate global array for stat constructor to return the new stats in..
    $this->global_array = array();

    if ($fetch->num_rows > 0) {
      while($row = mysqli_fetch_array($fetch)) {

        // define variables collected from database.
        $this->player1 = $row['player1'];
        $this->player2 = $row['player2'];
        $this->creature = (int)$row['creature'];
        $this->project_name = $row['name'];
        $this->project_id = (int)$row['id'];
        $this->dead = (int)$row['dead'];
        $this->in_battle = (int)$row['in_battle'];
        $this->latest_server_update = $row['date_latest_update']; // latest script request..
        $this->latest_fed = $row['date_latest_fed_food'];
        $this->latest_water = $row['date_latest_fed_water'];
        $this->latest_treat = $row['date_latest_fed_treat'];
        $this->strength = (int)$row['strength'];
        $this->agility = (int)$row['agility'];
        $this->energy = (int)$row['energy'];
        $this->health = (int)$row['health'];
        $this->happiness = (int)$row['happiness'];
        $this->overfeeding = (int)$row['overfeeding'];
        $this->hrs_slept = $row['hours_slept'];
        $this->sleep_until = $row['sleep_until'];

        if (($this->strength <= 20) && ($this->energy <= 20) && ($this->agility <= 20)) {

            $conf = $this->stat_config(7, $this->latest_fed, 2 , 'health', $this->health);
            $conf = $this->stat_config(3, $this->latest_fed, 5, 'energy', $this->energy);
        } else {
          $conf = $this->stat_config(15, $this->latest_fed, 3, 'health', $this->health);
          $conf = $this->stat_config(15, $this->latest_fed, 2, 'energy', $this->energy);
        }

        if ($this->health < 30) {
          $conf = $this->stat_config(10, $this->latest_fed, 4, 'strength', $this->strength);

          // happiness
          $conf = $this->stat_config(20, $this->latest_treat, 3, 'happiness', $this->happiness);
        } else if ($this->health > 70){
          $conf = $this->stat_config(10, $this->latest_fed, 3, 'strength', $this->strength);

          /// happiiness
          $conf = $this->stat_config(5, $this->latest_treat, 4, 'happiness', $this->happiness);
        } else {
          $conf = $this->stat_config(5, $this->latest_fed, 2, 'strength', $this->strength);

          // happiness
          $conf = $this->stat_config(70, $this->latest_treat, 5, 'happiness', $this->happiness);
        }

        $conf = $this->stat_config(25, $this->latest_fed, 5, 'agility', $this->agility);
        // $this->stat_config(60, $latest_water, 2, 'agility', $agility,$health,$overfeeding,$row, $project_name, 'water', $project_id, $username, $global_array, $latest_server_update);


      } ### while loop end. ###

      /*
      function to be called after all projects has gone through $this->stat_config();
      */
      $this->execute_projects($this->project_id);

    }
  }

    public function parse_overfeeding($current_overfeed,$date_latest_fed, $project_id) {
      // parse how many minutes passed since latest fed.
      $date_latest_fed;

      // get current overfeeding value.
      $current_overfeed;

      // use current stats, agility, strength, energy etc to reduce overfeeding.
      if ($date_latest_fed > 1) {

      }



    }

    // constructor for health & water.
    public function stat_config($min, $latest_fed_type, $sliced, $rowType, $origin_stat) {
      // get amount of minutes by both dates..
      $date_eval_server = round((strtotime($this->now) - strtotime($this->latest_server_update)) /60);
      $date_eval_feed_type = round((strtotime($this->now) - strtotime($latest_fed_type)) /60);

      // if servers minutes difference from current date is LESS than latest fed..
      $date_calc = $latest_fed_type;
      if ($date_eval_server < $date_eval_feed_type) {
        // calc on latest_server_update instead of latest fed with food/water..
        $date_calc = $this->latest_server_update;
      }

      // get amount of minutes passed between latest "fed" and current date.
      // fed, water, etc.2
      $timediffer = round((strtotime($this->now) - strtotime($date_calc)) /60);

      // get how many times 30 minutes has passed.
      $tmin = $timediffer / $min;

      // if atleast 30 minutes has passed once..
      // while 30 min is $min which can be anything... 25min, 60 min.. Depending on $min that are passed into the function as param..
      // date_latest_update cannot be updated if tmin hasnt passed once.
      if ($tmin >= 1) {

        // include('pet_behaviours.php');
        // $boy = pet_behaviours($this->creature);


        // only strength, energy and agility can be affected of overfeeding.
        if (($rowType === 'strength') || ($rowType === 'energy') || ($rowType === 'agility')) {
          if ($this->overfeeding >= 2) {
            // overfeeding impact
            $sliced = $sliced  - 1.4; // increase the amount of $rowType being decreased.
          } else if($this->overfeeding >= 5) {
            $sliced = $sliced -2.2; // increase the amount of $rowType being decreased.
          }
          // else, keep at 0 / null
        }

        // module || the new stat for the creature
        $stat = $origin_stat * ((100-$tmin/$sliced) / 100);

        // change value of the stat to $this->rows

        // round the new stat up. 24.123 will be 24. etc..
        $roundStat = ceil($stat);

        if ($roundStat >= 99) {
          $roundStat = 100;
        } elseif ($roundStat <= 0) {
          $roundStat = 0;
        }

        $reduce_overfeeding = $this->parse_overfeeding($this->overfeeding,$date_eval_feed_type, $this->project_id);

        $query = "UPDATE projects SET $rowType = '$roundStat', date_latest_update = '$this->now' WHERE id LIKE '$this->project_id'";

        if ($rowType !== 'health') {
          $health_increase = $roundStat/5;

          $query = "UPDATE projects SET $rowType = $roundStat, date_latest_update = '$this->now', health = health + $health_increase WHERE id LIKE '$this->project_id'";
        }


        try {

          // if try doesn't work.. Re-do it in catch()
          $qry = mysqli_query($this->conn, $query);

          return array('success' => true);


        } catch (Exception $e) {
          // TODO THIS IS A BAD CODE PIECE

          printjson(array('success' => 0));
          return array('error' => true);
        }
      }
    }

    public function earn_achievement($player, $achievement_id, $sub_root) {
      include('../achievements/earn_achievement.php');

      $achievements = new available_achievements();

      $achievement = $achievements->achievements_sorted('pets', $sub_root);

        // echo json_encode($achievement[$i]);
        //
        // echo $current_level . "   ALSO:   " .  (int)$achievement[$i]['achievement']['level'];
        $earn = new earn_achievement($player, $achievement_id, $this->conn);

        $i = $earn->auth_achievement();

        if ($i['success']) {

          $this->achievements_container[] = $earn->return_template();
        } else {
          $this->achievements_container[] = array('achievment_earned' => false);
        }
    }


    public function calm_settlement() {
      $qry_string = "SELECT *
        FROM projects
        WHERE
        (
          player1 LIKE '$this->username'
          AND bought_as_stage = 1
          AND stage = 3
        )
        OR
        (
          player2 LIKE '$this->username'
          AND bought_as_stage = 1
          AND stage = 3
        )";

        $query = mysqli_query($this->conn, $qry_string);

        if($query->num_rows >= 2) {
          $this->earn_achievement($this->username, '110322', 'stages');
        }
    }

    public function give_rewards($project_id, $battle_winner_reward_pet) {
      // give the resource bundle.
      include('gain_resources_project.php');

      $resources = gain_resources_project($project_id);

      // give user BitFunds
      $bitfunds = mysqli_query($this->conn, "UPDATE users
        SET bitfunds = bitfunds + $battle_winner_reward_bitfunds WHERE player_username = '$this->username'");

      // initate boolean, for IF the awards has been given.
      $battle_awards = '0';

      // if new pet function from shop returned true.
      if ($bitfunds) {
        $battle_awards = '1';
        // give the rewards to the winner.
        include('../shop/shop.php');

        if ($battle_winner_reward_pet !== 0) {
          $new_pet = purchase_item($this->now, $this->conn, $battle_winner_reward_pet, $this->username, false); // The pet, the username, pay = false.

          if ($new_pet['success' == 1]) {
            return '1';
          }
            return '0';
        }


        // $get_winner = mysqli_query($this->conn, "SELECT project_name
        //   FROM projects
        //   WHERE id = $battle_winner_project_id");


      } else {
        return '0';
      }
    }

    // public function get_attacks($project_id, $creature) {
    //   $current_attacks = pet_attacks($conn, $project_id);
    // }

    // function to be executed after stats evaluation
    public function execute_projects(){

      // fetch all
      $fetch = mysqli_query($this->conn, "SELECT *
        FROM projects
        WHERE player1 LIKE '$this->username'
        OR player2 LIKE '$this->username'");

      if (!empty($this->standalone_project_id)) {
        // fetch specific
        $fetch = mysqli_query($this->conn, "SELECT *
          FROM projects
          WHERE (player1 = '$this->username' AND id = $this->standalone_project_id)
          OR (player2 = '$this->username' AND id = $this->standalone_project_id)");
      }

      $this->rows = array();
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

        try {
          // check if project is in battle
          if ((($in_battle == 1) && ($in_battle_battle_id != 0))) {


            // find the current battle the project is involved in.
            // also grab info about the winner. Name etc.

            // only get active battles. Which has 2 opponents.

            // PVP, get a battle which is in progress.
            $active_battles = mysqli_query($this->conn,"SELECT battles.battle_pvp_pve,battles.battle_id,battles.battle_player1_project_id, battles.battle_player2_project_id, battles.battle_player1_id, battles.battle_player2_id, battles.battle_until_date, battles.battle_winner_project_id, battles.battle_winner_reward_pet, battles.battle_winner_reward_bitfunds
              FROM battles, projects
              WHERE battles.battle_id = $in_battle_battle_id
              AND battles.battle_winner_project_id NOT LIKE 0
              AND battles.battle_finished = 0
              AND battles.battle_pvp_pve = 'pvp'
              LIMIT 1");

            // PVE!
            $active_pve_battles = mysqli_query($this->conn,"SELECT battles.battle_pvp_pve,battles.battle_id,battles.battle_player1_project_id, battles.battle_player2_project_id, battles.battle_player1_id, battles.battle_player2_id, battles.battle_until_date, battles.battle_winner_project_id, battles.battle_winner_reward_pet, battles.battle_winner_reward_bitfunds
              FROM battles, projects
              WHERE battles.battle_id = $in_battle_battle_id
              AND battles.battle_finished = 0
              AND battles.battle_pvp_pve = 'pve'
              LIMIT 1");


            // PVP
            $battle_looking_for_opponent = mysqli_query($this->conn, "SELECT battles.battle_pvp_pve, battles.battle_id,battles.battle_player1_project_id, battles.battle_player2_project_id, battles.battle_player1_id, battles.battle_player2_id, battles.battle_until_date, battles.battle_winner_project_id, battles.battle_winner_reward_pet, battles.battle_winner_reward_bitfunds
              FROM battles, projects
              WHERE battles.battle_id = $in_battle_battle_id
              AND battles.battle_player2_project_id = 0
              AND battles.battle_finished = 0
              AND battles.battle_pvp_pve = 'pvp'
              LIMIT 1");

            if (($active_battles->num_rows > 0) || ($active_pve_battles->num_rows > 0)) {

              $array_fetch = '';
              if ($active_battles->num_rows > 0) {
                $array_fetch = $active_battles;
              } else if($active_pve_battles->num_rows > 0) {

                $array_fetch = $active_pve_battles;
              } else {
                $this->global_execute = $this->execute_arr($this->global_execute, ['battle_error_log' => 'Failed to get correct active battle!']);
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

                $bind_project_variables = mysqli_query($this->conn,
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
                  $this->global_execute = $this->execute_arr($this->global_execute, ['battle_error_log' => 'failed to fetch projects' , 'battle_player1_project_id' => $battle_player1_project_id, 'battle_player2_project_id' => $battle_player2_project_id]);
                }

                // check if battle is done.
                if (strtotime($this->now) >= strtotime($battle_until_date)) {

                  // if project could be released from in_battle status.
                  if (mysqli_query($this->conn,
                    "UPDATE projects
                    SET in_battle = 0, in_battle_battle_id = 0
                    WHERE id = $project_id")) {

                    // check if user that is fetching, project won the battle.
                    if ($battle_winner_project_id == (int)$project_id) {
                      // if your project won


                      if($battle_pvp_pve === 'pvp') {
                         $achievement_id = '24234';
                      } else {
                        $achievement_id = '234';
                      }


                      $this->earn_achievement($this->username, $achievemend_id, 'battles');
                      $battle_awards = $this->give_rewards($project_id, $battle_winner_reward_pet);


                      //
                      // include('gain_resources_project.php');
                      // $resources = gain_resources_project($project_id);
                      //
                      // // give user BitFunds
                      // $bitfunds = mysqli_query($this->conn, "UPDATE users
                      //   SET bitfunds = bitfunds + $battle_winner_reward_bitfunds WHERE player_username = '$this->username'");
                      //
                      // // initate boolean, for IF the awards has been given.
                      // $battle_awards = '0';
                      //
                      // // if new pet function from shop returned true.
                      // if ($bitfunds) {
                      //   $battle_awards = '1';
                      //   // give the rewards to the winner.
                      //   include('../shop/shop.php');
                      //
                      //   if ($battle_winner_reward_pet !== 0) {
                      //     $new_pet = purchase_item($this->now, $this->conn, $battle_winner_reward_pet, $this->username, false); // The pet, the username, pay = false.
                      //
                      //     if ($new_pet['success' == 1]) {
                      //       $battle_awards = '1';
                      //     } else {
                      //       $battle_awards = '0';
                      //     }
                      //   }
                      //
                      //
                      //   // $get_winner = mysqli_query($this->conn, "SELECT project_name
                      //   //   FROM projects
                      //   //   WHERE id = $battle_winner_project_id");
                      //
                      //
                      // } else {
                      //   // TODO this should be logged.
                      //   // run queries again?
                      // }

                      $this->global_execute = $this->execute_arr($this->global_execute,
                      ['battle_pvp_pve' => $battle_pvp_pve,
                      'battle_winner' => '1',
                      'battle_winner_project_name_opponent' => $battle_looser_project_name,
                      'battle_done' => '1',
                      'battle_awards' => $battle_awards ,
                      'battle_reward_resources' => $resources,
                      'battle_reward_bitfunds' => $battle_winner_reward_bitfunds,
                      'battle_reward_pet' => $battle_winner_reward_pet,
                      ]);

                    } else {
                      // your project lost. Terminate.
                      // TODO UNCHECK THIS!
                      $dead = 0;

                      $die_rand = rand(0,99);
                      if ($die_rand < 66) {
                        $dead = 1;
                        $kill = mysqli_query($this->conn, "UPDATE projects SET dead = 1 health = 0, energy = 0, strength = 0, agility = 0 WHERE id = $project_id");
                      }

                      //$kill = mysqli_query($this->conn, "DELETE FROM projects WHERE id = $project_id");

                      $this->global_execute = $this->execute_arr($this->global_execute, ['battle_pvp_pve' => $battle_pvp_pve, 'battle_looser_died' => $dead ,'battle_winner' => '0', 'battle_winner_project_name_opponent' => $battle_winner_project_name,'battle_done' => '1']);
                    }
                  } else {

                    $this->global_execute = $this->execute_arr($this->global_execute, ['battle_error_log' => 'Could not release project from battle: ' . mysqli_error($this->conn)]);
                  }
                  // set battle as finished TODO

                } else {
                  // battle is not done.
                  $battle_until_date_cur = gmdate('d.m.Y H:i', strtotime($battle_until_date));

                  $time = ($this->now - $battle_until_date)/60;

                  $this->global_execute = $this->execute_arr($this->global_execute, ['battle_pvp_pve' => $battle_pvp_pve, 'time' => $time,'battle_player2_joined' => 1,'battle_done' => '0','battle_until_date' => $battle_until_date_cur, 'battle_until_date_server' => $battle_until_date , 'server_time' => $now , 'battle_winner' => $battle_winner, 'in_battle_opponent' => $battle_opponent_project_name]);
                }
              }

            } else if($battle_looking_for_opponent->num_rows > 0) {
              $this->global_execute = $this->execute_arr($this->global_execute, ['battle_player2_joined' => 0, 'battle_queued' => 1]);

            } else {
              // something is wrong..
              // release both projects from their battles. AND set the battle as Done / deleted
              $release = mysqli_query($this->conn, "UPDATE projects SET in_battle = 0, in_battle_battle_id = 0 WHERE id = $project_id");
              $this->global_execute = $this->execute_arr($this->global_execute, ['battle_error_log' => 'Battle could not be found!', 'query' => $string]);

            }
          }
        } catch (Exception $e) {
          $this->global_execute = $this->execute_arr($this->global_execute, ['error_catch' => $e]);
        }



        // calculate minutes
        $timespan = round((strtotime($this->now) - strtotime($date_created)) /60);

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
          $kill = mysqli_query($this->conn, "UPDATE projects SET dead = 1 WHERE id = $project_id");
        } else {
          // stage
          // calc $stage which COULD be 50. So if current minutes is higher than 50% of lifespan, then pursue next stage. duh..
          $next_stage = ($stages / 100) * $life_span;

          if ($timespan >= $next_stage) {
            // time to level up bro.
            $stage = $stage + 1;
            $query =  "UPDATE projects SET stage = $stage WHERE id = $project_id";

            // UNCOMMENT!
            //$staging = mysqli_query($this->conn, $query);

            // check if the pet is battle-ready.
            if ($stage == 2) {
              //$this->global_execute = $this->execute_arr($this->global_execute,['battle_ready' => '1','next_stage' => $stage, 'leveled_up' => '1']);
            } else if($stage == 3) {
              // check achievement 'Calm Settlement'
              $this->calm_settlement();

            } else {
              //$this->global_execute = $this->execute_arr($this->global_execute,['next_stage' => $stage, 'leveled_up' => '1']);
            }

            // if its an egg, and just turned into a bird, aka stage 1.
            if ($egg_create == 1 || $stage == 1) {
              // update agility,strength,health,happiness.
              $stats = mysqli_query($this->conn, "UPDATE projects SET health = 55, agility = 55, strength = 55, happiness = 20 WHERE id = $project_id");

            }

          }
          // nextstage = lived until now / when to die.. IN mins

          /*
          check if next stage is higher than 1/4, if so level to next stage. then check if 2/4 etc.

          // born -> now
          */

        }
        //$src_size = src_path($stage);

        include_once('../config/translators.php');

        $creature = animal_translator($creature);

        $extension = '.png';
        if (($stage === 1) && ($egg_creature === 1)) {
          $extension = '.gif';
        }
        $src_path = $src_path . $stage . $extension;

        //$global_execute[] = ['src_path2' => $src_path];

        $this->global_execute = $this->execute_arr($this->global_execute,['src_path' => $src_path]);

        $current_status = '';

        $status_array = array();

        // get attacks
        include_once('project_attacks/attack_rules.php');

        $attacks = new attack_rules($this->conn, $this->username, $project_id);

        $current_attacks = $attacks->pet_attacks();

        $this->global_execute = $this->execute_arr($this->global_execute, ['attacks' => $current_attacks]);

        // check if the stat updates killed the creature..
        if ($health <= 0) {
          $this->global_execute = $this->execute_arr($this->global_execute, ['dead' => 1]);
          $kill = mysqli_query($this->conn, "UPDATE projects SET dead = 1 WHERE id = $project_id");

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
          $timediffer_fed = round((strtotime($this->now) - strtotime($latest_fed)) /60);
          $latest_fed_min = $timediffer_fed / 60;
          // check if 24 hrs has passed..
          if ($latest_fed_min >= 24) {
            $status_array[] =  "Your creature needs food.";
          }


          // get an estimate of how many hours has passed since latest fed with water.
          $timediffer_water = round((strtotime($this->now) - strtotime($latest_water)) /60);
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
        if ($this->username === $player1) {
          #  // check if you have a player 2 connected to your project..
          if (empty($player2)) {
            // since you're alone, you have no secondary player connected to the project..
            // just push the Project data.
            $this->rows[] = array_merge($row,$status_array, $this->global_execute);

          } else {
            // fetch the Player 2's information
            $fetch_player2 = mysqli_query($this->conn, "SELECT player_avatar, player_level, player_id
              FROM users
              WHERE player_username LIKE '$player2'");

            while ($player2_details = mysqli_fetch_array($fetch_player2)) {
              // push the selected information to rows.

              $this->rows[] = array_merge($row, $player2_details , ["request_sent_by_player1" => "1"], $status_array, $this->global_execute);

            }
          }
          // you're player2
          // as player 2, you always have a connected player to the project.
        } else {

          $fetch_player1 = mysqli_query($this->conn, "SELECT player_avatar, player_level, player_id FROM users WHERE player_username LIKE '$player1'");

          while ($player1_details = mysqli_fetch_array($fetch_player1)) {
            $this->rows[] = array_merge($row, $player1_details, ["request_sent_by_player1" => "0"], $status_array, $this->global_execute);

          }
        }

        // clean $global_execute..
        // this array HAS to be cleaned before attempting to initiate another project.
        $this->global_execute = [];

      }

      // execute projects
      printjson($this->rows, true);




      mysqli_close($this->conn);
    }

}



 ?>
