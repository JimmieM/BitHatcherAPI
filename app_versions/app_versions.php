<?php

if (($_SERVER['REQUEST_METHOD'] === 'POST')) {

  if (!empty($_POST['app_version'])) {
    $app_version = $_POST['app_version'];
  }
  $app_versions = get_app_versions(null,$app_version);

  echo json_encode($app_versions);
  die();
}


function get_app_versions($key = null, $app_version = null) {

  $root = realpath($_SERVER['DOCUMENT_ROOT']);
  require_once($root . '/api/app/v2/' . 'config/modules.php');
  require_once(path('config/dbconfig.php'));

  $conn = get_connection();


  if ($key !== 'admin') {

    $post = new json_post();
    $post->__construct(false);

    $username_request = clean_input($conn, 'username_request');
  } else {
    $username_request = 'BitHatcher';
  }

  try {
    log_request($conn, $username_request, 'Requesting to view app_versions', null);

    $x = mysqli_query($conn, "SELECT * FROM app_versions");

    if (!empty($app_version)) {
      $x = $x . 'WHERE version_number = ' . $app_version;
    }



    $versions = array();
    while ($app_versions = mysqli_fetch_array($x)) {

      $app_version['version_body'] = nl2br($app_version['version_body']);

      $versions[] = $app_versions;
    }

    return array('success' => 1, 'versions' => $versions);
  } catch (Exception $e) {
    return array('success' => 0, 'error' => $e);
  }




}

 ?>
