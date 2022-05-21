<?php
require_once("./../../../../wp-load.php");

$url = !empty($_POST)?sanitize_text_field($_POST["url"]):sanitize_text_field($_GET["url"]);

if(is_file($url)){
    require PIC_SELL_PATH_INC . "class-pic-sell-stream.php";
    $stream = new PIC_VideoStream($url);
    $stream->start();

    exit();
  }else{
    die("This file not exist.");
  }

