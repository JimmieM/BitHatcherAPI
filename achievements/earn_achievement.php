<?php

require(realpath($_SERVER['DOCUMENT_ROOT']).'/api/app/v2/achievements/available_achievements.php');
require_once(realpath($_SERVER['DOCUMENT_ROOT']).'/api/app/v2/config/dbconfig.php');
require_once(realpath($_SERVER['DOCUMENT_ROOT']).'/api/app/v2/config/modules.php');
// $conn = get_connection();

class earn_achievement extends available_achievements
{
  private $username;
  private $conn;
  private $achievement_id;
  private $current_achievements;
  private $current_achievements_array;

  public $achievement_name;
  public $achievement_icon;
  public $achievement_description;


  function __construct($username, $achievement_id, $conn) {

    $this->username = $username;
    $this->conn = $conn;
    $this->achievement_id = (string)$achievement_id;
  }

  // template to return to client - output with printjson module
  public function return_template() {
    return array(
     'achievement_earned' => true,
     'achievement_name' => $this->achievement_name,
     'achievement_icon' => $this->achievement_icon,
     'achievement_description' => $this->achievement_description
   );
  }

  private function achievement_template($achievement_id) {

    $details = $this->find_achievement($achievement_id);

    if ($details['success']) {

      $this->achievement_name = $details['achievement']['name'];
      $this->achievement_icon = $details['achievement']['icon'];
      $this->achievement_description = $details['achievement']['description'];

      // return template to insert into mysql
      return array('template' => array(
        'achievement_id' => $achievement_id,
        'earned' => true,
        'date' => now(),
        'achievement_name' => $details['achievement']['name'],
        'achievement_description' => $details['achievement']['description'],
        'icon' => $details['achievement']['icon']
      ),
      'success' => true);
    } else {
      return array(
        'success' => false,
        'error_log' => $details['error_log']
      );
    }
  }

  // finds achievement details by {$achievement_id}
  private function find_achievement($achievement_id) {
    $achievements = $this->return_achievements(false);

    for ($i=0; $i < count($achievements); $i++) {
      if ($achievements[$i]['achievement_id'] === (string)$achievement_id) {
        return array('success' => true, 'achievement' => $achievements[$i]);
      }
    }
    return array('success' => false, 'error_log' => 'Could not find achievement by identifier provided!');
  }

  // public function if_user_has_achievement($achievement_id) {
  //
  // }

  private function earn_achievement($achievement_id) {
    $template = $this->achievement_template($achievement_id);

    //echo json_encode($template);

    // if the template could be created.
    if ($template['success']) {
      $container = array();
      if (empty($this->current_achievements)) {

        $container[] = $template['template'];

        return $this->update_user($container);

      } else {
        $this->current_achievements_array[] = $template['template'];

        return $this->update_user($this->current_achievements_array);
      }
    } else {
      return array(
        'success' => false,
        'error_log' => $template['error_log']
      );
    }

  }

  private function update_user($template) {

    $template = json_encode($template);
    $template = mysqli_escape_string($this->conn, $template);

    $query_string = sprintf('UPDATE users SET player_achievements = "%s" WHERE player_username = "%s"', $template, $this->username);

    $qry = mysqli_query($this->conn, $query_string);

    if ($qry) {
      return array('success' => true);
    }
    return array('success' => false, 'error_log' => mysqli_error($this->conn));
  }

  /*
  $earn = if the user should earn it.

  False, if you just want to check if the user has the achievement, basiacally.
  */
  public function auth_achievement($earn = true) {

    $return = array();

    $qry = mysqli_query($this->conn, "SELECT player_achievements FROM users WHERE player_username = '$this->username' LIMIT 1");

    if ($qry->num_rows > 0) {
      $rows = mysqli_fetch_array($qry);

      $player_achievements = $rows['player_achievements'];

      $this->current_achievements = $player_achievements;
      $this->current_achievements_array = json_decode($player_achievements);

      if (!empty($player_achievements)) {
        $player_achievements = json_decode($player_achievements, true);
        // check if user has achievement already!



        write_to_file('achievement_earned', "--------------------------------------------------\n\n");
        // doesnt have  it.
        $return['owned'] = false;
        for ($i=0; $i < count($player_achievements); $i++) {
          $return['success'] = false;

          write_to_file('achievement_earned', "\n\n" . $this->username . ", currently looping through his achievements by ID: " . $player_achievements[$i]['achievement_id'] . '. Logged at: ' . now() . " <hr>\n\n ");

          write_to_file('achievement_earned', "COMPARING: " .  (string)$player_achievements[$i]['achievement_id']  . '   and  ' . (string)$this->achievement_id . " \n\n");

          if ($player_achievements[$i]['achievement_id'] === $this->achievement_id) {
            // already has it.

            write_to_file('achievement_earned', $this->username . ", already owns achievement: " . $this->achievement_id . ' | ' . $player_achievements[$i]['achievement_id'] . '. Logged at: ' . now() . " \n\n");

            $return['owned'] = true;
          } else {

            write_to_file('achievement_earned', "\n\n" . $this->username . ", Doesnt own " . $player_achievements[$i]['achievement_id'] . " | User should earn:  " . $this->achievement_id .  '... Whole JSON'. json_encode($player_achievements[$i]) . ' Logged at: ' . now() . " <hr>\n\n\n ");
          }
        }
        write_to_file('achievement_earned', "Loop OVER. Found : " . $i . ' achievements by ' . $this->username .  "\n\“");
        if (!$return['owned']) {
            if ($earn) {

              write_to_file('achievement_earned', $this->username . ", just earned achievement_id: " . $this->achievement_id . '. . by first Query. at: ' . now() . " \n\n");
                // does not have it
              $earn = $this->earn_achievement($this->achievement_id);
              if ($earn['success']) {
                $return['success'] = true;
              } else {

                $return['success'] = false;
                $return['error_log'] = $earn['error_log'];
              }
            } else {
              $return['success'] = false;
              $return['owned'] = false;
            }
        }
        write_to_file('achievement_earned', "--------------------------------------------------");
      } else {

        if ($earn) {
          // user has no achievements, aka not earned.
          $earn = $this->earn_achievement($this->achievement_id);
          if ($earn['success']) {
            write_to_file('achievement_earned', $this->username . ", just earned achievement_id: " . $this->achievement_id . ' by second Query. at: ' . now() . " \n\n");
            $return['success'] = true;
          } else {
            $return['success'] = false;
            $return['error_log'] = $earn['error_log'];
          }
        } else {
          $return['success'] = false;
          $return['owned'] = false;
        }

write_to_file('achievement_earned', "--------------------------------------------------");
      }
    } else {
      $return['success'] = false;
      $return['error_log'] = 'Failed to find user';
    }


    return $return;
  }
}

// $earn = new earn_achievement('jimmie', '9198', $conn);
//
// echo $earn->user_achievements();

 ?>
