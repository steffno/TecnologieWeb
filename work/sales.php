<?php

    
    require "include/template2.inc.php";
    require "include/dbms.inc.php";
    include "include/auth.inc.php";
     session_start();

    $main = new Template("NiceAdmin/dtml/frame_private.html");
    $body = new Template("NiceAdmin/dtml/sales.html");
   
    $main->setContent("username", $_SESSION['utente']['username']);
    if(isset($_REQUEST['message'])){
        switch ($_REQUEST['message']) {
            case 30:
                $body->setContent("message", "30");
                break;
            case 31:
                $body->setContent("message", "31");
                break;
        }
    }
    if (isOwner("dashboard.php"))
    {
        $main->setContent("body", $body->get());
        if( !isset($_SESSION['utente']['username']))
            $main->setContent("username", "errore");
        else
            $main->setContent("username", $_SESSION['utente']['username']);

       
    }
   
  
    $main->close();

?>