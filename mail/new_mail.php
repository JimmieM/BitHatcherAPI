<?php
  function new_email($subject, $email, $bodytext, $alt_bodytext) {
    // email
    require '../../../PHPMailer/PHPMailerAutoload.php';
    $mail = new PHPMailer;

    //$mail->SMTPDebug = 3;                               // Enable verbose debug output

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'bithatcher.com@gmail.com';                 // SMTP username
    $mail->Password = 'whamma12345';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to

    $mail->setFrom('bithatcher.com@gmail.com', $subject);
    $mail->addAddress($email);     // Add a recipient        // Name is optional
    $mail->addReplyTo('bithatcher.com@gmail.com', 'reply');

    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = $subject;
    $mail->Body    = $bodytext;


    // $mail->Body    = "Thank you, ${name}! <br /><br /> You've successfully signed up for BitHatcher Alpha. You'll get a notice if you have been invited. <br /><br /> Kindest regards, BitHatcher crew.";
    $mail->AltBody = $alt_bodytext;

    if(!$mail->send()) {
      return false;

    } else {
      return true;
    }
  }
  ?>
