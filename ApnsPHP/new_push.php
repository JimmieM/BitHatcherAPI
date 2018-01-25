<?php
function retrieve_device_token($conn,$username) {
  $str = "SELECT player_ios_device_token FROM users WHERE player_username = '$username' AND player_ios_device_token IS NOT NULL";
  $get = mysqli_query($conn, $str);

  if ($get->num_rows > 0) {
    $token = mysqli_fetch_array($get);
    return array('success' => 1, 'device_token' => $token['player_ios_device_token']);
  } else {
    return array('success' => 0);
  }
}

function new_push($text, $badge, $device_token) {
  // Adjust to your timezone
  date_default_timezone_set('Europe/Rome');
  // Report all PHP errors
  //error_reporting(-1);
  // Using Autoload all classes are loaded on-demand
  $root = realpath($_SERVER['DOCUMENT_ROOT']) . "/api/app/v2/";
  require_once $root.'ApnsPHP/ApnsPHP/Autoload.php';
  // Instantiate a new ApnsPHP_Push object
  $push = new ApnsPHP_Push(
  	ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
  	$root.'certificates/server_certificates_bundle_prod.pem'
  );
  // Set the Provider Certificate passphrase
  $push->setProviderCertificatePassphrase('hej123');
  // Set the Root Certificate Autority to verify the Apple remote peer
  $push->setRootCertificationAuthority($root.'certificates/entrust_root_certification_authority.pem');
  // Connect to the Apple Push Notification Service
  $push->connect();
  // Instantiate a new Message with a single recipient
  $message = new ApnsPHP_Message($device_token);
  // Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
  // over a ApnsPHP_Message object retrieved with the getErrors() message.
  $message->setCustomIdentifier("Message-Badge-3");
  // Set badge icon to "3"
  $message->setBadge($badge);
  // Set a simple welcome text
  $message->setText($text);
  // Play the default sound
  $message->setSound();
  // Set a custom property
  $message->setCustomProperty('acme2', array('bang', 'whiz'));
  // Set another custom property
  $message->setCustomProperty('acme3', array('bing', 'bong'));
  // Set the expiry value to 30 seconds
  $message->setExpiry(1200);
  // Add the message to the message queue
  $push->add($message);
  // Send all messages in the message queue
  $push->send();
  // Disconnect from the Apple Push Notification Service
  $push->disconnect();
  // // Examine the error message container
  $aErrorQueue = $push->getErrors();
  // if (!empty($aErrorQueue)) {
  // 	var_dump($aErrorQueue);
  // }
}
 ?>
