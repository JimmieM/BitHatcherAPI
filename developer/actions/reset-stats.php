<?php
require('../../config/dbconfig.php');
require('../../config/translators.php');


$conn = get_connection();

mysqli_query($conn, "UPDATE projects SET health = 100, strength = 100, agility = 100, happiness = 100, energy = 100 WHERE player1 = 'Reiya'");


 ?>
