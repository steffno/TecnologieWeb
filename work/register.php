<?php
    DEFINE("BLANK_T", "");

    require "include/template2.inc.php";
    require "include/register.inc.php";
    require "include/tags/frame-public-utils.php";

    $main = new Template("skins/dtml/frame_public.html");
    $register = new Template("skins/dtml/register.html");

    setup_frame_public($main);

    if (isset ($_GET['error']))
    {
        switch ($_GET['error'])
        {
            case 1:
            $error = "Utente già esistente!";
            break;
        }
        $register->setContent("error", $error);
    }
    else { $register->setContent("error", BLANK_T); }

    $main->setContent("dynamic_content", $register->get());

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        if (isset($_POST['username']) ||
            isset($_POST['password']) ||
            isset($_POST['confirm_password']) ||
            isset($_POST['email']) ||
            isset($_POST['name']) ||
            isset($_POST['surname']))
        {
            register([$_POST['username'], $_POST['password'], $_POST['email'], $_POST['name'], $_POST['surname']]);
        }
    }

    $main->close();

?>

