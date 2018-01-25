<?php
/*
* make bot accounts if none exists.
* make new bot account, sometimes. Randomly.
*/



$bot = new new_bot();
$bot->__construct();


class new_bot extends bot
{

  public $bot;

  function __construct()
  {
    require 'bot.php';
    parent::__construct();

    echo "string";
    // $this->bot->username = $this->generate_username();
    // parent::$this->bb;
    //
    // echo $this->bot->username;
    //
    // $create = $this->create_account($this->username);
    //
    // if ($create) {
    //   echo "yas!";
    // }
  }

  // public function create_account($username) {
  //   $x = mysqli_query($this->bot->conn, "SELECT player_username FROM users WHERE player_username = '$username'");
  //   if ($x->num_rows == 0) {
  //     // create account.
  //     if (mysqli_query($this->bot->conn, "INSERT INTO users (player_username, player_password, player_email, player_avatar, player_account_created, account_type) VALUES ('$this->username', 'boi', 'none', 'male', '2017', 'bot')")) {
  //       return true;
  //     }
  //   }
  // }
  //
  // public function generate_username() {
  //
  //   $characters = “12345abcdefghijklmnopqrstuvwxyz678910”;
  //   $charactersLength = strlen($characters);
  //   $username = '';
  //   for ($i = 0; $i < $length; $i++) {
  //       $username .= $characters[rand(0, $charactersLength - 1)];
  //   }
  //   return $username;
  // }

}

 ?>
