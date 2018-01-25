<?php

function num_foods() {
  // return all foodtypes in numbers obviously
  return array(0.1,0.2,1.1,1.2,2.0,2.1,3.0,4.0,5.0,5.1,6.0,8.0); // when potiontypes are added, add them here // TODO!
}

// global for all foodtypes.

/*
  translate numbers that mysql can read.
*/
function food_translator($foodtype) {
  switch ($foodtype) {
    // food
    case 0.1:
        return 'foodtype_cooked_steak';
      break;
    case 0.2:
        return 'foodtype_raw_steak';
      break;


    case 1.1:
        return 'foodtype_cooked_chicken';
      break;
    case 1.2:
        return 'foodtype_raw_chicken';
      break;

    case 2.0:
        return 'foodtype_carrot';
      break;
    case 2.1:
        return 'foodtype_mini_carrot';
      break;
    case 3.0:
        return 'foodtype_bird_seed';
      break;
    case 4.0:
        return 'foodtype_apple';
      break;

    // drinks
    case 5.0:
       return 'foodtype_water';

    case 5.1:
      return 'foodtype_feeding_bottle';
      break;

    // treats
    case 6.0:
      return 'treattype_dogbone';
      break;
    //
    //   // cook types
    // case 7.0:
    //     return 'cooktype_spices_spicy';
    //   break;
    //
    // case 7.1:
    //     return 'cooktype_spices_barbeque';
    //   break;
    //
    // case 7.2:
    //     return 'cooktype_charcoal';
    //   break;

    // potion types
    case 8.0:
        return 'potiontype_revival';
      break;
    // case 8.0:
    //     return 'potiontype_health';
    //   break;
    //
    // case 8.1:
    //     return 'potiontype_agility';
    //   break;
    //
    // case 8.2:
    //     return 'potiontype_strength';
    //   break;
    //
    // case 8.3:
    //     return 'potiontype_happiness';
    //   break;
    //
    // case 8.4:
    //     return 'potiontype_energy';
    //   break;

    default:
      # code...
      break;
  }
}

// mainly used to push back the name to client
function food_translator_names($foodtype) {
  switch ($foodtype) {
    // food
    case 0.1:
        return 'cooked steak';
      break;
    case 0.2:
        return 'foodtype_raw_steak';
      break;


    case 1.1:
        return 'cooked chicken';
      break;
    case 1.2:
        return 'foodtype_raw_chicken';
      break;

    case 2.0:
        return 'carrot';
      break;
    case 2.1:
        return 'mini carrot';
      break;
    case 3.0:
        return 'bird seed';
      break;
    case 4.0:
        return 'apple';
      break;

    // drinks
    case 5.0:
       return 'water';
    case 5.1:
      return 'feeding bottle';
      break;

    // treats
    case 6.0:
      return 'dogbone';
      break;

      // cook types
    case 7.0:
        return 'spicy spice';
      break;

    case 7.1:
        return 'barbeque spice';
      break;

    case 7.2:
        return 'charcoal';
      break;

    // potion types
    case 8.0:
        return 'health potion';
      break;

    case 8.1:
        return 'agility potion';
      break;

    case 8.2:
        return 'strength potion';
      break;

    case 8.3:
        return 'happiness potion';
      break;

    case 8.4:
        return 'energy potion';
      break;

    default:
      # code...
      break;
  }
}

// get ID by name
function achievement_translator($achievement_name) {

}

function animal_translator($animal) {
  switch ($animal) {
    case 0: // bird
        return 'bird';
      break;
    case 1: // fox
        return 'fox';
      break;

    case 2: // rabbit
        return 'rabbit';
       break;
    case 3: // red panda
        return 'red_panda';
       break;
    case 4:
      return 'squirell';
      break;
    case 5:
      return 'turtle';
      break;

    default:
      return false;
      break;
  }
}
 ?>
