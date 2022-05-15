<?php

//$content = trim(file_get_contents("php://input"));

//$data = json_decode($content, true);
//$data['success'] = true;

if (!empty($_POST)) {
    $data = $_POST;
  }
  else {
  //  $data = json_decode(stripslashes(file_get_contents("php://input")), true);
  $data = $_GET;
  }

require "class-pic-sell-stream.php";

$stream = new VideoStream($data['url']);
$stream->start();
//echo json_encode($data);
exit();