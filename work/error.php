<?php

include "include/template2.inc.php";

if(isset($_GET["error"]) && $_GET["error"] == '404') {
    $forb = new Template("skins/error3.html");
}
else
{
    if (rand() % 2 === 0)
        $forb = new Template("skins/error.html");
    else
        $forb = new Template("skins/error2.html");
}

$forb->close();;

?>
