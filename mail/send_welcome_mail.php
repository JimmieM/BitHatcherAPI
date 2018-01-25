<?php



require('new_mail.php');


require('../config/dbconfig.php');

$send = send();

if ($send) {
  echo "Success";
}

function send() {
  $conn = get_connection();

  $sel = mysqli_query($conn, "SELECT * FROM mail WHERE has_been_sent = 0");

  if ($sel->num_rows > 0) {
    while ($mails = mysqli_fetch_array($sel)) {
      $subject = $mails['subject'];
      $message = $mails['message'];
      $to = $mails['to_address'];
      $from = $mails['from_address'];
      $id = $mails['id'];

      if(mysqli_query($conn, "UPDATE mail SET has_been_sent = 1 WHERE id = $id")) {

        $new = new_email($subject,
        $to,
        $message,
        "Thanks");

        new_email('New user has registered for BitHatcher!',
        'jimmie.magnusson@hotmail.com',
        'A new user just registered with following email: ' . $to . '! \n\n Greetings, BitHatcher',
        "Thanks");

        if ($new) {
          return true;
        }

      }
      return false;




    }
  } else {
    die();
  }
}



 ?>
