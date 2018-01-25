<?php
/*
 API to fetch chosen project on select within application..

 TODO: Integrate TEST.php with creature stats stuff as well.a

 Used to fetch Current Project, and project selection
*/

  error_reporting(E_ALL);
  ini_set('display_errors', 1);


  $_POST = json_decode(file_get_contents('php://input'), true);
  header('Content-Type: application/json');

  require('../config/dbconfig.php');



  $conn = get_connection();

  // username
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $project_id = mysqli_real_escape_string($conn, $_POST['project_id']);

  // fetch all
  $fetch = mysqli_query($conn, "SELECT * FROM hatch_projects WHERE ID LIKE $project_id AND player1 LIKE '$username' OR player2 LIKE '$username' ");

  $rows = array();
  while($row = mysqli_fetch_array($fetch)) {
    // define variables
    $player1 = $row['player1'];
    $player2 = $row['player2'];

    // if you are player 1
    if ($username === $player1) {
      #  // check if you have a player 2 connected to your project..
      if (empty($player2)) {
        // since you're alone, you have no secondary player connected to the project..
        // just push the Project data.
        $rows[] = $row;

      } else {
        // fetch the Player 2's information
        $fetch_player2 = mysqli_query($conn, "SELECT player_avatar, player_level FROM hatch_users WHERE player_username LIKE '$player2'");

        while ($player2_details = mysqli_fetch_array($fetch_player2)) {
          // push the selected information to rows.

          $rows[] = array_merge($row, $player2_details);

        }
      }
      // you're player2
      // as player 2, you always have a connected player to the project.
    } else {
      $fetch_player1 = mysqli_query($conn, "SELECT player_avatar, player_level FROM hatch_users WHERE player_username LIKE '$player1'");

      while ($player1_details = mysqli_fetch_array($fetch_player1)) {
        $rows[] = $row;
        $rows[] = $player1_details;
      }
    }
  }
  // execute JSON
  echo json_encode($rows);

  mysqli_close($conn);
 ?>
