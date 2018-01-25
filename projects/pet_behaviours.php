<?php
/*

// get the pet in int.

return traits. such as "food prefferd", stats-usage during time, battle_winning_chances.


*/

function pet_behaviours($pet) {
  $behaviours = array();

  $stat_usage = array('energy' => 0, 'agility' => 0, 'strength' => 0);
  $behaviours['stat_usage'] = $stat_usage;

  $winning_chance = array('stage_2' => 50, 'stage_3' => 50, 'stage_4' => 50);
  $behaviours['winning_chance'] = $winning_chance;

  switch (variable) {
    case 0: // bird
        $behaviours['food_preffered'] = array(3.0, 2.0, 2.1, 4.0);
        $behaviours['stat_usage']['energy'] = 10;
        $behaviours['stat_usage']['agility'] = 10;
        $behaviours['stat_usage']['strength'] = 10;

        $behaviours['winning_chance']['stage_2'] = 40;
        $behaviours['winning_chance']['stage_3'] = 50;
        $behaviours['winning_chance']['stage_4'] = 69;
      break;

    case 1: // fox
      $behaviours['food_preffered'] = array(0.1,1.1,6.0);
      $behaviours['stat_usage']['energy'] = 10;
      $behaviours['stat_usage']['agility'] = 10;
      $behaviours['stat_usage']['strength'] = 10;

      $behaviours['winning_chance']['stage_2'] = 30;
      $behaviours['winning_chance']['stage_3'] = 45;
      break;

    case 2: // rabbit
      $behaviours['food_preffered'] = array(2.0,2.1,4.0);
      $behaviours['stat_usage']['energy'] = 15;
      $behaviours['stat_usage']['agility'] = 15;
      $behaviours['stat_usage']['strength'] = 12;

      $behaviours['winning_chance']['stage_2'] = 50;
      $behaviours['winning_chance']['stage_3'] = 56;

      break;

    case 3: // red_panda
      $behaviours['food_preffered'] = array(3.0, 2.0, 2.1, 4.0, 1.1);
      $behaviours['stat_usage']['energy'] = 40;
      $behaviours['stat_usage']['agility'] = 20;
      $behaviours['stat_usage']['strength'] = 20;

      $behaviours['winning_chance']['stage_2'] = 41;
      $behaviours['winning_chance']['stage_3'] = 60;

      break;

    case 4: // squirell.
      $behaviours['food_preffered'] = array(3.0);
      $behaviours['stat_usage']['energy'] = 10;
      $behaviours['stat_usage']['agility'] = 10;
      $behaviours['stat_usage']['strength'] = 10;

      $behaviours['winning_chance']['stage_2'] = 50;
      $behaviours['winning_chance']['stage_3'] = 70;
      break;

    case 5: // turtle.
      $behaviours['food_preffered'] = array(3.0, 2.0, 2.1, 4.0);
      $behaviours['stat_usage']['energy'] = 3;
      $behaviours['stat_usage']['agility'] = 2;
      $behaviours['stat_usage']['strength'] = 1;

      $behaviours['winning_chance']['stage_2'] = 70;
      $behaviours['winning_chance']['stage_3'] = 88;
      break;
  }
  return $behaviours;
}



 ?>
