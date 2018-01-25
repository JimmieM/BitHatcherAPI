<?php
/**
 *
 */
class achievements extends achievement_rules
{

  public $player_id;
  public $player_username;
  public $qry_where;

  // imports.
  public $conn;
  public $modules;
  public $translators;

  // amount of pets owned.
  public $player_pets;
  // count amount of battles won.
  public $player_battles_won;
  // count amount of battles entered.
  public $player_battles_entered;



  public $achievement_rules;

  function __construct($args)
  {
    /*
    ** check if the user is gaining an achievements,
    */

    // create connection.
    require('../config/dbconfig.php');
    $this->conn = get_connection();

    // iniate requirements.
    $this->modules = require('../config/modules.php');
    $this->translators = require('../config/translators.php');

    $this->player_data();
    // build string.
    $this->qry_where = "WHERE player_id=" . $this->player_id;

    // init rules.
    $this->achievement_rules = new achievement_rules;
    $this->achievement_rules->__construct();
  }

  function player_data() {
    $query = mysqli_query($this->conn, "SELECT
      player_username
      FROM
      users
      WHERE player_id = $this->player_id");
    if ($query->num_rows > 0) {
      $row = mysqli_fetch_array($query);
      $this->player_username = $row['player_username'];
    }
  }

  // use function to collect current stages onto achievements.
  function collect_current() {
    $this->player_pets = $this->count_pets();
  }

  // function to check if User meet any requirements to gain an achievement.
  public function compare_achievements() {

  }

  public function earn_achievement($achievement) {
    // check if achievement exists.
    $sql = "SHOW COLUMNS FROM achievements";
    $result = mysqli_query($this->conn, $sql);
    while($row = mysqli_fetch_array($result));

    for ($i=0; $i < $row ; $i++) {
      if ($row[$i] == $achievement) {
        // update.
        $qry = mysqli_query($this->conn, "UPDATE ");
      }
    }
  }

  public function count_pets() {
    return mysqli_query($conn, "SELECT count(*) as count FROM projects WHERE player1 = '$player_username' OR player2 = '$player_username'");

  }

  public function count_battles() {

  }

  public function count_battles_won() {

  }

}

/**
 *
 */
class achievement_rules
{

  /*
  * list all possible achievements.
  */

  // transform into objects.

  # 2,5,10,25
  public $gained_pets;

  # amount of time in days. 3, 8, 18, 28
  public $kept_pet_alive;

  public $battles_won;

  function __construct($args)
  {
    $this->transform_rules();
  }

  // function to use achievement variables and transform into object sets.
  public function transform_rules($value='')
  {
    $this->achievements_pets();

    $this->achievements_battles();
  }

  // all achievements in pet category
  public function achievements_pets() {

      $this->gained_pets = [
        'Owned two pets' => 2,
        'Owned five pets' => 5,
        'Owned ten pets' => 10,
        'Owned twenty-five pets' => 25
      ];

      $this->kept_pet_alive = [
        'Kept pet alive for 3 days' => 3,
        'Kept pet alive for 8 days' => 8,
        'Kept pet alive for 18 days' => 18,
        'Kept pet alive for 28 days' => 28,
      ];

      return;
  }

  public function achievements_battles() {
    $this->battles_won = [
      'One battle won' => 1,
      'Five battles won' => 5,
      'Ten battles won' => 10,
      'Forty battles won' => 40
    ];
  }

  public function compare_achievements() {
  }

}





 ?>
