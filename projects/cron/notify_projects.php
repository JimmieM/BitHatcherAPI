<?php
/*
- script to run in CRON

--- TASKS ---
1. Loop all projects Where dead = 0
1.1. check if project Owner/owners HAS device TOKEN!, else QUIT,
2. Check if the pet has reached a new stage.
3. Check if battle is done.
4. Check if it has Low HP or stats.
 */


class cron_pets
{
  // push message.
  public $message;

  // includes.
  public $conn;
  public $push;
  public $modules;
  public $db;
  public $helpers;
  public $translators;
  public $stat_usage;
  public $now;
  public $date_time;

  // user stuff.
  public $player1;
  public $player2;

  public $device_tokens;

  // project vars.
  public $project_id;

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

  function __construct()
  {
    $this->device_tokens = array();
    require('../../config/dbconfig.php');
    $this->conn = get_connection();
    $this->push = require_once('../ApnsPHP/new_push.php');

    $this->modules = include_once('../config/modules.php');
    $this->date_time = date_default_timezone_set('UTC');
    $this->now = date("Y-m-d H:i:s");

    $get_x = mysqli_query($this->conn, "SELECT * FROM projects WHERE dead = 0");
    while ($row = mysqli_fetch_array($get_x)) {
      $this->project_id = $row['id'];

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

      $this->player1 = $projects['player1'];
      $this->player2 = $projects['player2'];

      $has_token = $this->has_token();

      if (!$has_token) {
        return false;
      }
    }
  }

  public function new_stage()
  {
    # code...
  }

  public function low_stats()
  {
    # code...
  }

  public function battle_status() {
    // PVP, get a battle which is in progress.
    $active_battles = mysqli_query($this->conn,"SELECT battles.battle_pvp_pve,battles.battle_id,battles.battle_player1_project_id, battles.battle_player2_project_id, battles.battle_player1_id, battles.battle_player2_id, battles.battle_until_date, battles.battle_winner_project_id, battles.battle_winner_reward_pet, battles.battle_winner_reward_bitfunds
      FROM battles, projects
      WHERE battles.battle_id = $in_battle_battle_id
      AND battles.battle_winner_project_id NOT LIKE 0
      AND battles.battle_finished = 0
      AND battles.battle_pvp_pve = 'pvp'
      LIMIT 1");

      // PVE, get a battle which is in progress.
      $active_pve_battles = mysqli_query($this->conn,"SELECT battles.battle_pvp_pve,battles.battle_id,battles.battle_player1_project_id, battles.battle_player2_project_id, battles.battle_player1_id, battles.battle_player2_id, battles.battle_until_date, battles.battle_winner_project_id, battles.battle_winner_reward_pet, battles.battle_winner_reward_bitfunds
        FROM battles, projects
        WHERE battles.battle_id = $in_battle_battle_id
        AND battles.battle_finished = 0
        AND battles.battle_pvp_pve = 'pve'
        LIMIT 1");

      if (($active_battles->num_rows > 0) || ($active_pve_battles->num_rows > 0)) {

        $array_fetch = '';
        if ($active_battles->num_rows > 0) {
          $array_fetch = $active_battles;
        } else if($active_pve_battles->num_rows > 0) {

          $array_fetch = $active_pve_battles;
        }

        while ($battle_rows = mysqli_fetch_array($array_fetch)) {

          $battle_id = (int)$battle_rows['battle_id'];
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

          // push notifications
          $player1_notified = (int)$battle_rows['battle_player1_notified'];
          $player2_notified = (int)$battle_rows['battle_player2_notified'];

          if ($this->project_id !== $player1_project_id) {
            // this is player2.
            // get battle_players2s players id.

            $get = mysqli_query($this->conn, "SELECT player1, player2 FROM projects WHERE id = $player1_project_id");
            if ($get->num_rows > 0) {
              $players = mysqli_fetch_array($get);

              // player1 in battle.
              $player1 = $players['player1'];
              // player2 in battle.
              $player2 = $players['player2'];

              // get both device tokens.

              $player1_device_token = $this->push->retrieve_device_token($this->conn,$player1);
              if ($player1_device_token['success'] == 1) {
                $this->device_tokens[] = $player1_device_token['device_token'];
              }

              $player2_device_token = $this->push->retrieve_device_token($this->conn,$player2);
              if ($player2_device_token['success'] == 1) {
                $this->device_tokens[] = $player2_device_token['device_token'];
              }

            }
          }

          // if ($player1_project_id === $this->project_id) {
          //   // your pet is player1_project_id
          //   if ($player1_notified == 0) {
          //     $notify = 1;
          //     $notify_player = 'battle_player1_notified';
          //   }
          // } else {
          //   if ($player2_notified == 0) {
          //     $notify = 1;
          //     $notify_player = 'battle_player2_notified';
          //   }
          // }

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
            # code...
          }

          // check if battle is done.
          if (strtotime($this->now) >= strtotime($battle_until_date)) {

            if (mysqli_query($this->conn, "UPDATE battles SET battle_player2_notified = 1, battle_player1_notified = 1 WHERE battle_id = $battle_id")) {

              // check if user that is fetching, project won the battle.
              if ($battle_winner_project_id == (int)$project_id) {
                // if your project won

                $this->message = "Your pet " . $battle_winner_project_name . " won against " . $battle_looser_project_name;

              } else {
                // your project lost. Terminate.
                // TODO UNCHECK THIS!
                $dead = 0;

                $die_rand = rand(0,99);
                if ($die_rand < 66) {
                  $dead = 1;
                  $kill = mysqli_query($this->conn, "UPDATE projects SET dead = 1 health = 0, energy = 0, strength = 0, agility = 0 WHERE id = $project_id");
                }

                $this->message = "Your pet " . $battle_project_name . " has lost against " . $battle_winner_project_name;
              }
            }
          }
        }
      }
  }

  public function has_token() {


      $this->device_token1 = $this->push->retrieve_device_token($this->conn,$this->player1);

      if ($this->device_token1['success'] == 1) {
        $this->device_tokens[] = $this->device_token1['device_token'];
      }

      $this->device_token2 = $this->push->retrieve_device_token($this->conn,$this->player2);

      if ($this->device_token2['success'] == 1) {
        $this->device_tokens[] = $this->device_token2['device_token'];
      }

      if ($this->device_token1['success'] == 1 || $this->device_token2['success'] == 1) {
        return true;
      }
     return false;
  }
}
