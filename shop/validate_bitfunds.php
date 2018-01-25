<?php
// if the user should pay, call this function.
function validate_bitfunds($conn, $server_cost, $client_cost, $username) {

    // check if totalcost from client side is correct
    if ($server_cost != $client_cost) {
      return array('success' => 0,'error_log' => 'invalid price' . ' client_cost:' . $client_cost . ' server_cost: ' . $server_cost);
    }
    // check if user has the amount of BitFunds in inventory
    $check = mysqli_query($conn, "SELECT player_bitfunds FROM users WHERE player_username = '$username' AND player_bitfunds >= $client_cost");

    if ($check) {
      if ($check->num_rows > 0) {
        return array('success' => 1);

      } else {
        return array('success' => 0,'can_afford' => 0, 'username' => $username);
      }
    } else {
      return array('success' => 0, 'error_log' => 'mysqli', 'mysqli_error' => mysqli_error($conn));
    }

}
 ?>
