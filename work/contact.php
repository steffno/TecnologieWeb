<?php

require "include/template2.inc.php";
require "include/tags/frame-public-utils.php";

$main = new Template("skins/dtml/frame_public.html");
$cont = new Template("skins/dtml/contact.html");

setup_frame_public($main);

$main->setContent("dynamic_content", $cont->get());
$main->close();
