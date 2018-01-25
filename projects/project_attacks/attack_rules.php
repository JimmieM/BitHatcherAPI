<?php



/**
 *
 */
//  ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


$root = realpath($_SERVER['DOCUMENT_ROOT']);

$path = $root . "/api/app/v2/";

include_once($path .'achievements/earn_achievement.php');

class attack_rules
{
  public $conn;
  public $username;
  public $project_id;
  private $attacks;

  public $available_achievements;

  function __construct($conn, $username, $project_id) {


    $this->conn = $conn;
    $this->username = $username;
    $this->project_id = $project_id;

    $img = "https://vignette.wikia.nocookie.net/wowwiki/images/d/dd/Ability_physical_taunt.png/revision/latest/scale-to-width-down/32?cb=20050504113421";

    //$this->attack_rules();

    $this->attacks = [
      0 => ['name' => 'Bite','sql_name' => 'attacks_bite', 'cost' => 40, 'available' => false, 'damage' => 20, 'protection' => 5, 'description' => 'Main attack for melee damage dealers', 'procc_per_battle' => 4, 'img_src' => $img],
      1 => ['name' => 'Swirl', 'sql_name' => 'attacks_swirl','cost' => false,'achievement' => 'Aggressive turnaround', 'achievement_id' => 2, 'available' => false, 'damage' => 40, 'protection' => 10, 'description' => 'High damage whirlwind', 'procc_per_battle' => 2, 'img_src' => $img],
      2 => ['name' => 'Claw', 'sql_name' => 'attacks_claw','cost' => false, 'achievement' => 'Calm settlement', 'achievement_id' => 0, 'available' => false, 'damage' => 35, 'protection' => 10, 'description' => 'Base ability', 'procc_per_battle' => 2, 'img_src' => $img],
      3 => ['name' => 'Feast', 'sql_name' => 'attacks_feast', 'cost' => 70, 'available' => false, 'damage' => 40, 'protection' => 11, 'description' => 'Feasts on the target and produces a high amount of damage', 'procc_per_battle' => 1, 'img_src' => $img],
      4 => ['name' => 'Shell Protection', 'sql_name' => 'attacks_shell_protection', 'cost' => 70, 'available' => false, 'damage' => 15, 'protection' => 70, 'High chance of protecting your petand reduces the amount taken by 70%', 'procc_per_battle' => 1, 'img_src' => $img],
      5 => ['name' => 'Chew', 'sql_name' => 'attacks_chew', 'cost' => false, 'achievement' => 'Rabbit or humanoid?', 'achievement_id' => 1 , 'available' => false, 'damage' => 20, 'protection' => 40, 'description' => 'Chews on the target and producing a high amount of damage', 'procc_per_battle' => 3, 'img_src' => $img],
      6 => ['name' => 'Wind Slam', 'cost' => 70, 'sql_name' => 'attacks_wind_slam', 'available' => false, 'damage' => 20, 'protection' => 10, 'description' => 'Produces a high damage by birds wings.', 'procc_per_battle' => 4, 'img_src' => $img],
      7 => ['name' => 'Enrage', 'sql_name' => 'attacks_enrage', 'cost' => 70, 'available' => false, 'damage' => 20, 'protection' => 50, 'description' => 'Enrages the pet and dealing a high amount of damage and increasing protection by 30%.', 'procc_per_battle' => 2, 'img_src' => $img],
      8 => ['name' => 'Tail Slam', 'sql_name' => 'attacks_tail_slam', 'cost' => 70, 'available' => false, 'damage' => 20, 'protection' => 0, 'description' => 'Available for pets bla', 'procc_per_battle' => 3, 'img_src' => $img],
      9 => ['name' => 'Rake', 'sql_name' => 'attacks_rake', 'cost' => 0, 'available' => true, 'damage' => 12, 'protection' => 5, 'description' => 'Basic attack', 'procc_per_battle' => 3, 'img_src' => $img],
    ];
  }


  /*
  find all achievements that unlock attacks
  */
  public function find_achievements() {


    // get all achievements;
    $achievements = new available_achievements(false);

    // add to array.
    $this->available_achievements = $achievements->return_achievements(false);


    // init container to save your attacks.
    $owned_achievement_attacks = array();


    for ($i=0; $i < count($this->available_achievements); $i++) {

      if ($this->available_achievements[$i]['reward']['attack'] && $this->available_achievements[$i]['reward']['attack'] !== undefined) {

        $owned = $this->find_achievement_based_attack($this->available_achievements[$i]['achievement_id']);

        // if find_achievement_based_attack returns a boolean of true by auth_achievement by earn_achievement class
        if ($owned['owned']) {
          // add the whole object to array, then return
          $owned_achievement_attacks[] = $this->available_achievements[$i];
        }
      }

    }

    return $owned_achievement_attacks;
  }

  /*
  if user has an achievement that unlocks an attack
  */
  public function find_achievement_based_attack($achievement_id) {

    $earn = new earn_achievement($this->username, $achievement_id, $this->conn);

    $i = $earn->auth_achievement(false);

    // if user has the achievement
    if ($i['owned']) {
      return $i;
    } else {
      return ['owned' => false];
    }

  }

  function pet_attacks() {
    $query = "SELECT * FROM project_attacks WHERE attacks_project_id = $this->project_id LIMIT 1";
    $x = mysqli_query($this->conn, $query);

    if ($x->num_rows > 0) {
      $attacks = mysqli_fetch_assoc($x);
      return $attacks;
    }

    return null;
  }

  /*
  return array with attacks with true or false values depening on the user has it.
  */
  public function return_sorted_attacks($value='') {

  }

  public function return_rules($value='')
  {
    return $this->attacks;
  }

  // send in a pet value, bird, redpanda, etc.

  function attack_rules() {

    include_once('../../config/translators.php');
    $animal = get_creature_by_id($this->conn, $this->project_id);




    // array of usable attacks.
    // $this->attacks = array(
    // 0 => false,
    // 'swirl' => false,
    // 2 => false,
    // 3 => false,
    // 'shell_protection' => false,
    // 5 => false,
    // 'wind_slam' => false,
    // 7 => false,
    // 'tail_slam' => false,
    // );

    switch ($animal) {
      case 0: // bird
          $this->attacks[0]['available'] = true;
          $this->attacks[2]['available'] = true;
          $this->attacks[3]['available'] = true;
          $this->attacks[6]['available'] = true;
        break;
      case 1: // fox
          $this->attacks[0]['available'] = true;
          $this->attacks[2]['available'] = true;
          $this->attacks[3]['available'] = true;
          $this->attacks[8]['available'] = true;
        break;
      case 2: // rabbit
          $this->attacks[0]['available'] = true;
          $this->attacks[5]['available'] = true;
          $this->attacks[3]['available'] = true;
        break;
      case 3: // red panda
          $this->attacks[0]['available'] = true;
          $this->attacks[5]['available'] = true;
          $this->attacks[2]['available'] = true;
          $this->attacks[7]['available'] = true;
          $this->attacks[8]['available'] = true;
        break;
      case 4: // squirell
          $this->attacks[8]['available'] = true;
          $this->attacks[0]['available'] = true;
          $this->attacks[2]['available'] = true;
          $this->attacks[3]['available'] = true;
        break;
      case 5: // turtle
          $this->attacks[3]['available'] = true;
          $this->attacks[7]['available'] = true;
          $this->attacks[4]['available'] = true;
          $this->attacks[1]['available'] = true;
        break;
    }

    return $this->attacks;
  }

}




 ?>
