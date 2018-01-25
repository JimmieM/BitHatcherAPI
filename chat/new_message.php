<?php


$new = new_message();

echo json_encode($new);


function new_message() {

  require('../config/dbconfig.php');
  require('../config/modules.php');


  require_once '../vendor/autoload.php';

  require_once '../ably-php/ably-loader.php';

  $post = new json_post();

  $post->__construct(false);

  $conn = get_connection();

  $from = clean_input($conn, 'from'); // Jimmie
  $to = clean_input($conn, 'to'); // Alex
  $message = clean_input($conn, 'message');

  $now = now();

  if (mysqli_query($conn, "INSERT INTO
    chat
    (to_user_id,
    from_user_id,
    to_username,
    from_username,
    message,
    date)
    VALUES
    (null,
    null,
    '$to',
    '$from',
    '$message',
    '$now')")) {

      if ($to == 'BitHatcher') {

        $bot_message = array();
        $bot_message = ['Hey', 'hello','whats up?','so how do u like BitHatcher so far?', 'im good 2day, how r u?','okay..', 'didnt ask', 'What are you doing?', 'Nice weather eh?','Brb','Im looking for a wig to buy, what about a red one?', 'are you sure?',
        'Why are cats mean?', 'What??','Whaddupp', 'BitHatcher seems fun..','Did you know, you can actually WIN a green fox?', 'A secret, BitHatcher is actually.. NVM','Wanna hear a secret?',
        'How are you today ' . $from .'?', $from . ', that is a nice name!', 'I met a girl online once, but she lives 30 miles from here.. Interesting fact eh.', 'No, im not canadian..', 'Hi!', 'I urge', 'LOL!',
        'I dont know what to reply..', 'Oki.', 'Be right back, gonna get a drink real quick!', 'I am just a bot.. What do you expect?'];

        $msg = array_rand($bot_message, 1);

        $message = strtoupper($bot_message[$msg]);

        $_bot = mysqli_query($conn, "INSERT INTO
            chat
            (to_user_id,
            from_user_id,
            to_username,
            from_username,
            message,
            date)
            VALUES
            (null,
            null,
            '$from',
            '$to',
            '$message',
            '$now')");
      }

        // new token.
        require_once('tokens.php');
        require_once('../ApnsPHP/new_push.php');
        $new_token = new_chat_token($conn, $from, $to);

        if ($new_token['success'] == 1) {
          $token = $new_token['token'];

          $device_token = retrieve_device_token($conn,$to);

          if ($device_token['success'] == 1) {
            //echo json_encode(['success' => 1]);
              new_push('New message from ' . $from . '!', 1, $device_token['device_token']);
          }
          try {

              $ably = new Ably\AblyRest('YWxmHw.T0c4Gg:9UQUZRXTeUv30RVL');
              $channel = $ably->channel($to);

              $channel->publish('event', 'New chat message from ' . $from);

          } catch (Exception $e) {
            $return[] = ['success' => 0, 'error' => $e];
          }
          // return
          require_once('load_chat.php');

          $load_chat = load_chat($conn, '', $from, $to, true);

          $return = array(['chat' => $load_chat, 'success' => 1, 'token' => $token]);

        } else {
          $return[] = ['success' => 0, 'error' => $new_token['error']];
        }


    return $return;


  } else {
    return array(['success' => 0]);
  }
}




 ?>
