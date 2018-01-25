<?php
/*
  this script is used for client to call to check if any new resources, experience etc has been erarned..

  scripts such as 'cron/gain_resources.php' will run every few hours and grab a few players and randomly give them resources.
  The user doesn't get noticed though, which this script are for. By using token keys to search through tables which are meant to be log-tables.

  returning a message to client "You've earned new resouces" , "You've earned experience".
*/

/**
 *
 */
class attach_tokens
{

  function __construct()
  {
    require_once('../config/dbconfig.php');
    require_once('../config/modules.php');
    require_once('../config/translators.php');

    $conn = get_connection();

    $qry = "SELECT tokens FROM client_incoming WHERE username = '$username'";
    $result = $conn->query($qry);

    if ($result->num_rows > 0) {

      $finished_requests = array();

      while ($row = $result->fetch_assoc()) {
        // request different modules to see if a request has been sent.

        $tokens = $row['tokens'];
        $token_as_token = explode(',', $tokens);

        foreach ($token_as_token as $token) {

          $report = 'false';

          // moar requests such as experience, levels, etc..
          $requests = array('gain_resources_requests');

          // foreach request in array, call token_usage() and do shit.
          foreach ($requests as $request) {

            try {
              $return_rows = token_usage($request, $token, $username);

              // return a boolean to users saying like HAY! YOU GOT SUM EXPERIENCE WHILE U WERE GONE DUDE:
              // only if token_usage returns higher than 0 ofc.
              if ($return_rows > 0) {
                // add 2 array to send to client.
                $finished_requests[] = array($request => $return_rows);
              }
            } catch (Exception $e) {

               $build_vars = "token: " .  $token;
               $report = error_report($username, $request, 'main.php fetch client_incoming', $token);

               // report -> array w/ encode resources.
             }
          }
        }
        return (array('attach_tokens' => 1, $finished_requests, 'error_log' => $report));
      }
    } else {
      echo json_encode(array('attach_tokens' => 0));
    }
  }

  function token_usage($table, $token, $username) {

    // this code is shit.
    $select = "SELECT * FROM $table WHERE username = '$username' AND token = '$token'";
    $result = $conn->query($table);

    // modules.php / client_incoming.func. with param of delete.

    // since the user SHOULD not get a notice of the same request again, then delete the key yo.
    $delete = update_client_incoming($username,$token,$conn,'delete');

    return $table->num_rows;


    // wtf was i thinking.

    // if($table->num_rows > 0) {
    //   return num_rows;
    // } else {
    //   return 0;
    // }
  }
}





 ?>
