<?php

const BLANK_T = "";

    require "include/template2.inc.php";
    require "include/auth.inc.php";
    require "include/tags/frame-public-utils.php";

    if (isset($_SESSION['auth']) && $_SESSION['auth'])
        header('location: profile.php');

    $main = new Template("skins/dtml/frame_public.html");
    $login = new Template("skins/dtml/login.html");


    setup_frame_public($main);

    if (isset($_GET['location']))
    {
        $array = explode("/", $_GET['location']);
        $_SESSION['previous_page'] = end($array);
    }


    if (isset ($_GET['error'])) {

        switch ($_GET['error']) {
            case 1:
                $error = "Compila tutti i campi!";
                break;
            case 2:
                $error = "Username e/o password sbagliati!";
                break;
        }
        session_abort();
        $login->setContent("error", $error);
    }
    else $login->setContent("error", BLANK_T);

    $main->setContent("dynamic_content", $login->get());

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST['name']) || $_POST['name'] == '' ||
            !isset($_POST['password']) || $_POST['password'] == '') {
            header("Location: login.php?error=1");

        } else {
            login($_POST['name'],  $_POST['password']);
        }
    }


    $main->close();

?>

