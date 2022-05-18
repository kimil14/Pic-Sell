<?php

//$content = trim(file_get_contents("php://input"));

//$data = json_decode($content, true);
//$data['success'] = true;

if (!empty($_POST)) {
    $data = stripslashes_deep($_POST);
  }
  else {
  //  $data = json_decode(stripslashes(file_get_contents("php://input")), true);
  $data["url"] = stripslashes($_GET["url"]);
  }

require "class-pic-sell-stream.php";

$stream = new PIC_VideoStream($data['url']);
$stream->start();
exit();