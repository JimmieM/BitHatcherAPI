<?php

require_once(realpath($_SERVER['DOCUMENT_ROOT']).'/api/app/v2/achievements/earn_achievement.php');
require_once(realpath($_SERVER['DOCUMENT_ROOT']).'/api/app/v2/achievements/available_achievements.php');

class experience {

  private $required_xp = 0;
  private $conn;
  public $now;

  public function get_player($player) {
    $qry = mysqli_query($this->conn, "SELECT player_experience, player_level FROM users WHERE player_username = '$player'");

    while ($fetch = mysqli_fetch_array($qry)) {
      $current_experience = $fetch['player_experience'];
      $current_level = $fetch['player_level'];
      return array('level' => $current_level, 'experience' => $current_experience);
    }
  }

  public function __construct($conn) {
    $this->conn = $conn;
  }

  public function earn_achievement($player, $current_level) {
      $achievements = new available_achievements();

      $achievement = $achievements->achievements_sorted('player', 'level');

      for ($i=0; $i < count($achievement); $i++) {
        // echo json_encode($achievement[$i]);
        //
        // echo $current_level . "   ALSO:   " .  (int)$achievement[$i]['achievement']['level'];
        if ($current_level >= (int)$achievement[$i]['achievement']['level']) {
          $earn = new earn_achievement($player, $achievement[$i]['achievement_id'], $this->conn);

          $i = $earn->auth_achievement();

          if ($i['success']) {
            return $earn->return_template();
          } else {
            return array('achievment_earned' => false);
          }
        }
      }


      return array('achievment_earned' => false);
  }

  public function gain_experience($player, $_experience) {

    $this->conn = get_connection();

    $current = $this->get_player($player);

    // foreach level you have, increase the XP required to level
    for ($i=0; $i < $current['level']; $i++) {
      $this->required_xp += 400;
    }

    $current_experience = $current['experience'];
    $gained_experience = $current['experience'] + $_experience;
    $current_level = $current['level'];

    if ($gained_experience > $this->required_xp) {
      $current_level++;

      $overflow = $this->required_xp - $current_experience;
      // get overlapsing exp

      $apply = mysqli_query($this->conn, "UPDATE users
      SET player_experience = 0, player_level = $current_level, player_experience_range = $this->required_xp
      WHERE player_username = '$player'");
    } else {
      $apply = mysqli_query($this->conn, "UPDATE users
      SET player_experience = $gained_experience, player_experience_range = $this->required_xp
      WHERE player_username = '$player'");
    }

    if ($apply) {
      $achievement = $this->earn_achievement($player, $current_level);

      return array('achievements' => array(0 => $achievement), 'success' => true);
    }

    return array('achievements' => null, 'success' => false);
  }
}





 ?>
