<?php
require('../../config/modules.php');
require('../../config/dbconfig.php');
require('../../config/translators.php');
require('../../projects/project_attacks/attack_rules.php');

$post = new json_post();

$post->__construct(false);

$class = new battle_action;


/**
 *
 */
class battle_action
{

  private $project_id;
  private $project_opponent_id;
  private $battle_id;
  private $attack_id;
  private $username_request;

  private $status;

  private $my_pet;
  private $opponent_pet;

  function __construct() {
    $this->initiate();

    $this->status = [
      1 => 'Nearly dead',
      2 => 'Close to death',
      3 => 'About half',
      4 => 'Prime state',
      5 => 'Great'
    ];
  }

  // get variables

  // stuff TODO:
  /*

  0-20% > nearly dead
  20-35 > Close to death
  35-50 > About half
  50-75 > Prime state
  75-100 > Great


    Function to evaluate how much percentage of the opponents HP should be reduced.

    do request to opponent for Push notification.

    return the HP of yours and opponents pets if any of them isnt dead.
  */


  /*
  @param int - $pet_health
  */
  public function health_status($pet_health) {
    $i = 0;
    if ($pet_health >= 0 && $pet_health <= 20) {
      $i = 1;
    } else if($pet_health >= 20 && $pet_health <= 35) {
      $i = 2;
    } else if($pet_health >= 35 && $pet_health <= 50) {
      $i = 3;
    } else if($pet_health >= 50 && $pet_health <= 75) {
      $i = 4;
    } else if($pet_health >= 85 && $pet_health <= 100) {
      $i = 5;
    }

    return $this->status[$i];
  }

  public function get_pets() {
    $x = mysqli_query($conn, "SELECT health, energy, agility, strength, in_battle, overfeeding FROM projects WHERE id = $this->project_id");
    $y = mysqli_query($conn, "SELECT health, energy, agility, strength, in_battle, overfeeding FROM projects WHERE id = $this->project_opponent_id");

    if ($x && $y) {
      $this->my_pet = mysqli_fetch_array($x);
      $this->opponent_pet = mysqli_fetch_array($y);

      return true;
    }

    return false;

  }

  public function evaluate() {
    // new instance
    $attack_rules = new attack_rules;

    // return array with all rules
    $rules = $attack_rules->return_rules();

    // pick your attack_id of the array
    $attack = $rules[$this->attack_id];

    $opponent_health = $this->opponent_pet['health'];


  }

  public function initiate() {
    $this->conn = get_connection();
    $this->project_id = clean_input($conn, 'project_id');
    $this->project_opponent_id = clean_input($conn, 'project_opponent_id');
    $this->battle_id = clean_input($conn, 'battle_id');
    $this->attack_id = clean_input($conn, 'attack_id');
    $this->username_request = clean_input($conn, 'username_request');

    if (!$this->get_pets()) {
      print_json(array('success' => false, 'error' => "Couldn't get pets"), true);
    } else {
      $this->evaluate();
    }

  }
}



 ?>
