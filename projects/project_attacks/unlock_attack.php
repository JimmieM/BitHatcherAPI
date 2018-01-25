<?php

$unlock_attack = new unlock_attack();
$unlock = $unlock_attack->__construct();


/**
 *
 */
class unlock_attack
{

  public $username;
  public $sql_name;
  public $project_id;

  public $conn;

  function __construct()
  {
    include_once('../../config/modules.php');
    include_once('../../config/translators.php');
    include_once('../../config/dbconfig.php');
    include_once('attack_rules.php');
    include_once('../../shop/validate_bitfunds.php');
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    $post = new json_post();


    $post->__construct(false);

    $this->conn = get_connection();

    $this->project_id = clean_input($this->conn,'project_id');
    $this->sql_name = clean_input($this->conn,'sql_name');
    $this->username = clean_input($this->conn, 'username');
    if ((!empty($this->username)) && (!empty($this->sql_name)) && (!empty($this->project_id)) ) {
      printjson( $this->unlock_attack(), true);

    } else {
      printjson(array('success' => 0, 'error_log' => 'missing parameters'), true);

    }
  }


  public function unlock_attack() {

    $price = $this->validate_price();

    if ($price) {
      $withdraw = mysqli_query($this->conn, "UPDATE users SET player_bitfunds = player_bitfunds - $price WHERE player_username = '$this->username'");

      if (!$withdraw) {
        return array('success' => 0, 'error_log' => 'mysqli', 'mysqli_error' => mysqli_error($this->conn));
      } else {

        $find = mysqli_query($this->conn, "SELECT $this->sql_name FROM project_attacks WHERE attacks_project_id = $this->project_id LIMIT 1");

        $query_string = '';
        if ($find->num_rows > 0) {
          $rows = mysqli_fetch_array($find);


          if ($rows[$this->sql_name] === 1) {
              return array('success' => 0, 'error_log' => 'You already own this attack');
          }

          $query_string = "UPDATE project_attacks SET $this->sql_name = 1 WHERE attacks_project_id = $this->project_id";
        } else {
          $query_string = "INSERT INTO project_attacks ($this->sql_name, attacks_project_id) VALUES (1, $this->project_id)";
        }

        $update = mysqli_query($this->conn, $query_string);
        if ($update) {
          return array('success' => 1);
        } else {
          return array('success' => 0, 'error_log' => mysqli_error($this->conn));
        }
      }
    } else {
      return array('success' => 0, 'error_log' => $price);
    }
  }


  public function validate_price() {
    $attack_rules = new attack_rules($this->conn, $this->username, $this->project_id);
    $attacks = $attack_rules->attack_rules();

    $price = 0;

    for ($i=0; $i < count($attacks); $i++) {
      if ($attacks[$i]['sql_name'] === $this->sql_name) {
        $price = $attacks[$i]['cost'];
        if ($price > 0) {
          $validate = validate_bitfunds($this->conn, $price, $price, $this->username);

          if ($validate['success'] === 1) {
            return true;
          }
          return false;
        } else {
          // its an achievement!
          if (!$price) {
            $attacks[$i]['achievement'];
            // check if unlocked etc etc..
          }
        }

      }
    }
  }
}


 ?>
