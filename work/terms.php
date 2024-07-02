<?php

require "include/template2.inc.php";
require "include/tags/frame-public-utils.php";


$main = new Template("skins/dtml/frame_public.html");
$terms = new Template("skins/dtml/terms.html");

setup_frame_public($main);

$main->setContent("dynamic_content", $terms->get());

$main->close();