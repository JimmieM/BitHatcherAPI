<?php


  //require_once('dbconfig.php');


  // disabe in prod
  //show_errors();


  function write_to_file($filename, $text) {

    $path = path('request_files/' . $filename . '.txt');

    if (file_exists($path)) {

      $current = file_get_contents($path);

      // Append a new person to the file
      $current .= $text;
      // Write the contents back to the file
      file_put_contents($path, $current);

      return true;
    }

    return false;

  }
  function path($path) {
    $root = realpath($_SERVER['DOCUMENT_ROOT']);

    return $root . "/api/app/v2/" . $path;
  }

  function show_errors() {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
  }

  function replace_string($value, $find = ' ', $replace = '_') {
    return str_replace($find,$replace, $value);
  }

  function log_request($conn, $username_request, $type, $error_log = null) {
    // log request
    $now = now();
    if (mysqli_query($conn, "INSERT INTO requests (username_request, type, date, error_log) VALUES ('$username_request', '$type', '$now', '$error_log')")) {
      return true;
    }

   return false;
  }

  /*
  function for error report to client..
  */
  function error_report($username, $table, $function, $vars) {
    $now = date("Y-m-d H:i:s");

    try {

      $conn = get_connection();

      $repo = mysqli_query($conn, "INSERT INTO error_reporting(username, table, function, vars, date) VALUES ('$username', '$table', '$vars', '$now')");

      if ($repo) {
        return (array('failure' => 1, 'error_reporting(bool):success' => 1));
      } else {
        return (array('failure' => 1, 'error_reporting(bool):success' => 0));
      }
    } catch (Exception $e) {

    }


  }

  function now() {
    date_default_timezone_set('UTC');
    return $now = date("Y-m-d H:i:s");
  }

  function set_time($time) {
    // $time can be "+2 hours" or "+2 minutes"
    date_default_timezone_set('UTC');

    $hrs = (strtotime($time));

    return date('Y-m-d H:i:s',$hrs);
  }

  date_default_timezone_set('UTC');

  $now = date("Y-m-d H:i:s");

  function create_token($length = 28) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  function update_client_incoming($username,$token,$conn,$request) {

    $conn = get_connection();

    if ($request === 'apply') {
      $qry = "SELECT username, tokens FROM client_incoming WHERE username = '$username'";
      $result = $conn->query($qry);


      $sql = "INSERT INTO client_incoming (tokens, username) VALUES ('$token','$username')";


      if ($result->num_rows > 0) {
        // do update
        $token = "," . $token;
        $sql = "UPDATE client_incoming SET tokens = CONCAT(tokens, '$token') WHERE username = '$username'";
      }

      if($client_incoming = $conn->prepare($sql)) {

        if ($client_incoming->execute()) {
          return 1;
        } else {
          return 0;
        }
      } else {
        echo "error:" . $conn->error;
      }


    } else if($request === 'remove') {

      // fetch all tokens
      $qry = "SELECT tokens FROM client_incoming WHERE username = '$username'";
      $result = $conn->query($qry);

      // see if there's any tkens
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

          // assign the tokens to a var
          $tokens = $row['tokens'];

          // remove the param token from all tokens.
          $token = "," . $token;
          $new_token = chop($tokens,$token);

          // re-apply to DB:
          $stmt = $conn->prepare("UPDATE client_incoming SET tokens = ? WHERE username = ?");
          $stmt->bind_param("ss", $new_token, $username);

          if ($stmt->execute()) {
             return 1;
          } else {
             return 0;
          }
        }
      }
    }
  }

  // custom function to print JSON array
  function printjson($string, $die = false) {

    header('Content-Type: application/json');

    echo json_encode($string);
    if ($die) {
      die();
    }
  }

  function return_player_resource($type) {
    return "player_" . $type;
  }



  function clean_input($conn, $POST) {
    $POST = mysqli_real_escape_string($conn, $_POST[$POST]);
    $POST = strip_tags($POST);

    return $POST;
  }

  class json_post
  {
    public $_POST;
    function __construct($post_allowed)
    {
        if (($_SERVER['REQUEST_METHOD'] !== 'POST') && ($post_allowed === false)) {

          printjson(array('post_request' => false),true);
        } else {
          $_POST = json_decode(file_get_contents('php://input'), true);

          require_once('dbconfig.php');
          $conn = get_connection();

          if (!empty($_POST['username_request'])) {
            $username_request = mysqli_real_escape_string($conn, $_POST['username_request']);
            online($conn, $username_request);
          }
          $token = $_POST['token'];

          if ($token === '30617466141a15e9f8224fbca3fd7a4f2378746a1c66084811c2b99f55b28ef6') {

            header('Content-Type: application/json');
          } else {
            printjson(array('post_request' => true, 'token_false' => true, 'token_recieved' => $token), true);
          }
        }
    }
  }

  function get_creature_by_id($conn,$project_id) {
    $x = mysqli_query($conn, "SELECT creature FROM projects WHERE id = $project_id LIMIT 1");

    $c = mysqli_fetch_array($x);
    return $c['creature'];
  }

  function online($conn, $username) {
    $now = now();

    $apply = mysqli_query($conn, "UPDATE users SET player_record_latest_date = '$now' WHERE player_username = '$username'");
  }


  function die_resp($resp) {
    die(['success' => 0, 'error_log' => 'die']);
  }


 ?>
