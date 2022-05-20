<?php

$url="";
if (!empty($_POST)) {
    $url = $_POST["url"];
}else {
  //  $data = json_decode(stripslashes(file_get_contents("php://input")), true);
    $url = $_GET["url"];
}

if(is_file($url)){
    require "class-pic-sell-stream.php";
    $stream = new PIC_VideoStream($url);
    $stream->start();
    exit();
  }else{
    die("This file not exist.");
  }

