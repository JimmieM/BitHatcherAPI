<?php

/*
* parent class for bot.
*/

$bot = new bot();
$bot->__construct();

class bot
{
  public $conn;
  public $modules;
  public $translators;

  public $username;
  public $password;

  public $_POST;

  function __construct($args)
  {
    echo "bot";
    // require '../config/dbconfig.php';
    //
    // $this->conn = get_connection();
    // $this->modules = require '../config/modules.php';
    //
    // if ($_POST['']) {
    //   # code...
    // }

  }

  public function callback() {

  }
}



 ?>
