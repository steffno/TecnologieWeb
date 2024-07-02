<?php


function setup_frame_public(&$template)
{
    session_start();

    if (isset($_SESSION['auth']) && $_SESSION['auth']) {
        $template->setContent("islogged", "profile.php");
        $template->setContent("can_access_cart", "cart.php");
        $template->setContent("can_access_order", "order.php");
    } else {
        $redirector = "login.php?location=" . urlencode($_SERVER['REQUEST_URI']);
        $template->setContent("islogged", "$redirector");
        $template->setContent("can_access_cart", "$redirector");
        $template->setContent("can_access_order", "$redirector");
    }
}


function check_auth()
{
    if(!$_SESSION['auth']){
        header("location: login.php");
        exit();
    }
}

