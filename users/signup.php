<?php



require('../config/dbconfig.php');
require('../config/modules.php');
require('../config/translators.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

$email = mysqli_real_escape_string($conn, $_POST['email']);
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$platform = mysqli_real_escape_string($conn, $_POST['platform']);
$gender = clean_input($conn, 'gender');

if (empty($username) or empty($password)) {
  die_resp();
}

// TODO remove..

$password = hash("sha256", $password);

$verify = mysqli_query($conn, "SELECT player_username FROM users WHERE player_username LIKE '$username'");

if ($verify->num_rows == 0) {
  // no matches, create da account yas!

  if (!preg_match("/^[a-zA-Z\d]+$/", $username))
  {
    $response[] = ['success' => 0 ,'user_exist' => 1];
    echo json_encode($response);
    die();
  }

  // success -> true
  // create a token

  try {

    $gender_img = '';

    switch ($gender) {
      case 0: // male
          $gender_img = 'male';
        break;

      case 1: // femalre
          $gender_img = 'female';
        break;

      case 2: // other

          $gender_img = 'other';
        break;

      default: //
          $gender_img = 'other';
        break;
    }

    $qry = mysqli_query($conn, "INSERT INTO users (player_username, player_password, player_email, player_avatar, player_account_created, player_platform_os) VALUES ('$username', '$password', '$email', '$gender_img', '$now', '$platform')");

    $response = array();
    if ($qry) {

      $sth = mysqli_query($conn,"SELECT player_username, player_level, player_avatar FROM users WHERE player_username = '$username' LIMIT 1");

      if ($sth->num_rows > 0) {
        while($r = mysqli_fetch_assoc($sth)) {
            $response[] = array_merge($r,['success' => 1]);
        }
      }  else {
        $response[] = array_merge(['success' => 0, 'relog' => 1]);
      }



    } else {
      $response[] = ['success' => -1 ,'error_log' => mysqli_error($conn) ];
    }

  } catch (Exception $e) {
      $response[] = ['success' => -1, 'error_log' => $e];
  }

  // return
  echo json_encode($response);

  if ($response[0]['success'] === 1) {
    $init_mail = mysqli_query($conn, "INSERT INTO mail (to_address, from_address, subject, message) VALUES ('$email', 'bithatcher.com@gmail.com', 'Welcome to BitHatcher!','<h2 style=text-align:center;>Welcome to BitHatcher</h2> <br />, $username .<br /><br /> Kindest regards, <br />BitHatcher.')");
    //mysqli_query($conn, "INSERT INTO mail (to_address, from_address, subject, message) VALUES ('$email', 'bithatcher.com@gmail.com', 'Welcome to BitHatcher!','<h2 style=text-align:center;>Welcome to BitHatcher</h2> <br />, $username .<br /><br /> Kindest regards, <br />BitHatcher.')");
  }

  // return then do email
  // if ($response[0]['success'] === 1) {
  //   require('../mail/new_mail.php');
  //   new_email("Welcome to BitHatcher!",
  //  $email,
  //  "<h2 style='text-align:center;'>Welcome to BitHatcher!</h2> <br /> Here are some details regarding your account, " . $username .".<br /><br /> Kindest regards, <br />BitHatcher.",
  //  "Thanks");
  //
  //  new_email("New user for BitHatcher!",
  // 'jimmie.magnusson@hotmail.com',
  // "<h2 style='text-align:center;'>A new user just registered!</h2> <br /> Here are the details: Username " . $username .".<br /><br /> Kindest regards, <br />BitHatcher.",
  // "Thanks");
  //
  //
  //
  //
  // }

  die();

} else {
  // user exists..
  $response[] = ['success' => 0 ,'user_exist' => 1];
  echo json_encode($response);

  die();
  // jimmie
  // yo
}


mysqli_close($conn);
 ?>
