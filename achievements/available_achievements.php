<?php
/**
 *
 */


class available_achievements {
  private $achievements;
  private $tree_roots;

  function __construct($divided = false) {
    require_once(realpath($_SERVER['DOCUMENT_ROOT']).'/api/app/v2/config/dbconfig.php');
    $this->conn = get_connection();

    $this->tree_roots = array('pets', 'player');
    $this->achievements = $this->return_achievements($divided);
  }

  public function achievement_id($part, $attribute) {

    if (is_array($part)) {
      for ($i=0; $i < count($part); $i++) {

      }
    }
    $this->achievements[$part];

  }


  // will return a partion of selected root / sub root
  public function achievements_sorted($root, $subroot) {
    $this->achievements = $this->return_achievements(true);

    $container = array();

    $partion = $this->achievements['achievements'][$root];

    for ($i=0; $i < count($partion); $i++) {
      if ($partion[$i]['sub_root'] === $subroot) {
        $container[] = $partion[$i];
      }
    }

    return $container;

  }

  public function unearned_achievements($username) {

    $return = array();

    $qry = mysqli_query($this->conn, "SELECT player_achievements FROM users WHERE player_username = '$username' LIMIT 1");

    if ($qry->num_rows > 0) {
      $rows = mysqli_fetch_array($qry);

      $player_achievements = $rows['player_achievements'];
      $all_achievements = $this->return_achievements(false);

      $this->current_achievements = $player_achievements;
      $this->current_achievements_array = json_decode($player_achievements);

      if (!empty($player_achievements)) {
        $player_achievements = json_decode($player_achievements, true);
        // check if user has achievement already!

        $player_ids = array();
        $returnable = array();

        for ($x=0; $x < count($player_achievements); $x++) {
          $player_ids[] = $player_achievements[$x]['achievement_id'];
        }

        //echo json_encode($player_ids) . "\n\n\n";

        for ($i=0; $i < count($all_achievements); $i++) {

          foreach ($player_ids as $achievement_id) {

            //echo $all_achievements[$i]['achievement_id'] . "  WITH  " . $achievement_id . " \n\n\n";
            if ($all_achievements[$i]['achievement_id'] === $achievement_id) {
              $all_achievements[$i]['earned'] = true;
            }
          }
        }

        $return['achievements'] = $this->divide_tree_root($all_achievements);
        $return['success'] = true;
        return $return;

      } else {
        // return ALL achievements
        $return['achievements'] = $this->return_achievements(true);
        $return['success'] = true;
        return $return;
      }
    } else {
      $return['success'] = false;
      $return['error_log'] = 'Failed to find user';
    }


    return $return;
  }

  /*
  divides achievements into tree-roots.
  pets and player.

  divided - boolean
  */
  public function divide_tree_root($achievements) {
    $divided_array = array();
    $this->achievements = $achievements;
    foreach ($this->tree_roots as $root) {
      for ($i=0; $i < count($this->achievements) ; $i++) {

        if ($this->achievements[$i]['tree_root'] === $root) {
          $divided_array['achievements'][$root][] = $this->achievements[$i];
        }
      }
    }

    return $divided_array;
  }


  /*
  Divided = boolean
  */
  public function return_achievements($divided) {
    $this->achievements = [
      0 => [
        'name' => 'Getting started',
        'description' => 'Win a PvP battle',
        'achievement' => [
          'amount' => 1
        ],
        'reward' => [
          'bitfunds' => true,
          'bitfunds_amount' => 20

        ],
        'earned' => false,
        'achievement_id' => '24234',
        'unlocked' => true,
        'icon' => 15,
        'tree_root' => 'pets',
        'sub_root' => 'battles'
      ],
      1 => [
        'name' => "That wasn't hard!",
        'description' => 'Win a PvE battle',
        'achievement' => [
          'amount' => 1
        ],
        'reward' => [
          'bitfunds' => true,
          'bitfunds_amount' => 20

        ],
        'earned' => false,
        'achievement_id' => '234',
        'unlocked' => true,
        'icon' => 15,
        'tree_root' => 'pets',
        'sub_root' => 'battles'
      ],
      2 => [
        'name' => "A challenging combatant",
        'description' => 'Defeat a stage 3 pet with a stage 2 pet of yours.',
        'achievement' => [
          'amount' => 1,
          'stage_required' => 2,
          'staged_against' => 3
        ],
        'reward' => [
          'title' => true,
          'title_name' => 'Challenger'

        ],
        'earned' => false,
        'achievement_id' => '21333',
        'unlocked' => true,
        'icon' => 15,
        'tree_root' => 'pets',
        'sub_root' => 'battles'
      ],
      3 => [
        'name' => 'Rabbit or humanoid?',
        'description' => 'Win against 3 rabbits in pet battles',
        'achievement' => [
          'pet_type' => 2,
          'amount' => 3
        ],
        'reward' => [
          'attack' => true,
          'attack_name' => 'Chew',
          'attack_id' => 5

        ],
        'earned' => false,
        'achievement_id' => '22221111',
        'unlocked' => true,
        'icon' => 50,
        'tree_root' => 'pets',
        'sub_root' => 'battles'
      ],
      4 => [
        'name' => "Freakin' squirells",
        'description' => 'Win against 5 squirells in pet battles',
        'achievement' => [
          'pet_type' => 4,
          'amount' => 5
        ],
        'reward' => [
          'bitfunds' => true,
          'bitfunds_amount' => 40
        ],
        'earned' => false,
        'achievement_id' => '9198',
        'unlocked' => true,
        'icon' => 30,
        'tree_root' => 'pets',
        'sub_root' => 'battles'
      ],
      5 => [
        'name' => 'Calm Settlement',
        'description' => 'Raise 3 pets to stage 3',
        'achievement' => [
          'stage' => 3,
          'amount' => 3
        ],
        'reward' => [
          'attack' => true,
          'attack_name' => 'Chew',
          'attack_id' => 5
        ],
        'earned' => false,
        'achievement_id' => '110322',
        'unlocked' => true,
        'icon' => 25,
        'tree_root' => 'pets',
        'sub_root' => 'stages'
      ],
      6 => [
        'name' => 'Insane progression',
        'description' => 'Reach level 10',
        'achievement' => [
          'level' => 10,
        ],
        'earned' => false,
        'achievement_id' => '238722',
        'unlocked' => true,
        'icon' => 10,
        'tree_root' => 'player',
        'sub_root' => 'level'
      ],
      7 => [
        'name' => 'Sprinting between levels',
        'description' => 'Reach level 25',
        'achievement' => [
          'level' => 25,
        ],
        'earned' => false,
        'achievement_id' => '998822',
        'unlocked' => true,
        'icon' => 15,
        'tree_root' => 'player',
        'sub_root' => 'level'
      ],
      8 => [
        'name' => "Becoming insane",
        'description' => 'Reach level 40',
        'achievement' => [
          'level' => 40,
        ],
        'earned' => false,
        'achievement_id' => '9982445',
        'unlocked' => true,
        'icon' => 25,
        'tree_root' => 'player',
        'sub_root' => 'level'
      ],
      9 => [
        'name' => "Just a brief start",
        'description' => 'Reach level 5',
        'achievement' => [
          'level' => 5,
        ],
        'earned' => false,
        'achievement_id' => '998223115',
        'unlocked' => true,
        'icon' => 25,
        'tree_root' => 'player',
        'sub_root' => 'level'
      ],
      10 => [
        'name' => "Veteran Nanny",
        'description' => 'Reach level 70',
        'achievement' => [
          'level' => 5,
        ],
        'earned' => false,
        'achievement_id' => '9244423',
        'unlocked' => true,
        'icon' => 25,
        'tree_root' => 'player',
        'sub_root' => 'level'
      ],
      11 => [
        'name' => "Experienced Winner",
        'description' => 'Win 25 battles with a pet to unlock the feature of using 3 attacks in battle',
        'achievement' => [
          'amount' => 25,
        ],
        'reward' => [
          'attack' => true,
          'attack_name' => 'Use 3 attacks in battles'
        ],
        'earned' => false,
        'achievement_id' => '124423',
        'unlocked' => true,
        'icon' => 50,
        'tree_root' => 'pets',
        'sub_root' => 'battles'
      ]
    ];


    // $this->achievements['pets']['battles']['won'] = [
    //   0 => [
    //     'name' => 'Getting started',
    //     'description' => 'Win a PvP battle',
    //     'achievement' => [
    //       'amount' => 1
    //     ],
    //     'reward' => [
    //       'bitfunds' => true,
    //       'bitfunds_amount' => 20
    //
    //     ],
    //     'earned' => false,
    //     'achievement_id' => '',
    //     'unlocked' => true,
    //     'icon' => 15
    //   ],
    //   1 => [
    //     'name' => "That wasn't hard!",
    //     'description' => 'Win a PvE battle',
    //     'achievement' => [
    //       'amount' => 1
    //     ],
    //     'reward' => [
    //       'bitfunds' => true,
    //       'bitfunds_amount' => 20
    //
    //     ],
    //     'earned' => false,
    //     'achievement_id' => 0.4,
    //     'unlocked' => true,
    //     'icon' => 15
    //   ],
    //   2 => [
    //     'name' => "A challenging combatant",
    //     'description' => 'Defeat a stage 3 pet with a stage 2 pet of yours.',
    //     'achievement' => [
    //       'amount' => 1,
    //       'stage_required' => 2,
    //       'staged_against' => 3
    //     ],
    //     'reward' => [
    //       'title' => true,
    //       'title_name' => 'Challenger'
    //
    //     ],
    //     'earned' => false,
    //     'achievement_id' => 0.4,
    //     'unlocked' => true,
    //     'icon' => 15
    //   ],
    // ];
    //
    // $this->achievements['pets']['battles']['won']['type'] = [
    //   0 => [
    //     'name' => 'Rabbit or humanoid?',
    //     'description' => 'Win against 3 rabbits in pet battles',
    //     'achievement' => [
    //       'pet_type' => 2,
    //       'amount' => 3
    //     ],
    //     'reward' => [
    //       'attack' => true,
    //       'attack_name' => 'Chew',
    //       'attack_id' => 5
    //
    //     ],
    //     'earned' => false,
    //     'achievement_id' => 0.1,
    //     'unlocked' => true,
    //     'icon' => 50
    //   ],
    //   1 => [
    //     'name' => "Freakin' squirells",
    //     'description' => 'Win against 5 squirells in pet battles',
    //     'achievement' => [
    //       'pet_type' => 4,
    //       'amount' => 5
    //     ],
    //     'reward' => [
    //       'bitfunds' => true,
    //       'bitfunds_amount' => 40
    //     ],
    //     'earned' => false,
    //     'achievement_id' => 0.2,
    //     'unlocked' => true,
    //     'icon' => 30
    //   ]
    // ];
    //
    // $this->achievements['pets']['stages'] = [
    //   0 => [
    //     'name' => 'Calm settlement',
    //     'description' => 'Raise 3 pets to stage 3',
    //     'achievement' => [
    //       'stage' => 3,
    //       'amount' => 3
    //     ],
    //     'reward' => [
    //       'attack' => true,
    //       'attack_name' => 'Chew',
    //       'attack_id' => 5
    //     ],
    //     'earned' => false,
    //     'achievement_id' => 1.1,
    //     'unlocked' => true,
    //     'icon' => 25
    //   ]
    // ];
    //
    // $this->achievements['player']['level'] = [
    //   0 => [
    //     'name' => 'Insane progression',
    //     'description' => 'Reach level 10',
    //     'achievement' => [
    //       'level' => 10,
    //     ],
    //     'earned' => false,
    //     'achievement_id' => 2.1,
    //     'unlocked' => true,
    //     'icon' => 10
    //   ],
    //   1 => [
    //     'name' => 'Sprinting between levels',
    //     'description' => 'Reach level 25',
    //     'achievement' => [
    //       'level' => 25,
    //     ],
    //     'earned' => false,
    //     'achievement_id' => 2.2,
    //     'unlocked' => true,
    //     'icon' => 15
    //   ]
    // ];



    //echo array_search(2.2, $this->achievements);
    if ($divided) {
      return $this->divide_tree_root($this->achievements);
    }
    return $this->achievements;
  }
}


 ?>
