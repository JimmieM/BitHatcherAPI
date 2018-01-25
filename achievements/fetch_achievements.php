<?php

require(__DIR__.'/available_achievements.php');
require('../config/dbconfig.php');
require('../config/modules.php');
require('../config/translators.php');

$post = new json_post();

$post->__construct(false);

$conn = get_connection();

$achievements = new available_achievements(true);

$username = clean_input($conn, 'username_request');

echo json_encode($achievements->unearned_achievements($username));
die();

 ?>
