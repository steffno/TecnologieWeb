<?php
require "dbms.inc.php";
require "tags/utility.inc.php";

DEFINE('ERROR_SCRIPT_PERMISSION', 100);
DEFINE('ERROR_USER_NOT_LOGGED', 200);
DEFINE('ERROR_OWNERSHIP', 200);


session_start();

function login($nome, $pass) {
    global  $mysqli;

    $pass = utility::cryptify($pass);

    $user_data = $mysqli->query("SELECT * FROM utente 
                                  WHERE username = '$nome'
                                  AND password = '$pass'")->fetch_array(MYSQLI_ASSOC);

    if (!$user_data) {
        header("location: login.php?error=2");

    }else {
        $_SESSION['utente'] = $user_data;
        $_SESSION['auth'] = true;

        $oid = $mysqli->query("SELECT DISTINCT service.script FROM utente 
                                    LEFT JOIN user_has_ugroup ON user_has_ugroup.id_utente = utente.id 
                                    LEFT JOIN ugroup_has_service ON ugroup_has_service.ugroup_id = user_has_ugroup.id_ugroup 
                                    LEFT JOIN service ON service.id = ugroup_has_service.service_id 
                                    WHERE username = '$nome'");

        if (!$oid)
        {
            trigger_error("Generic error, level 39", E_USER_ERROR);
        }

        $scripts = null;
        do
        {
            $data = $oid->fetch_assoc();
            if ($data) $scripts[$data['script']] = true;
            if ($data['script'] === 'dashboard.php') $_SESSION['previous_page'] = $data['script'];

        } while($data);

        $_SESSION['utente']['script'] = $scripts;

        if (isset($_SESSION['previous_page']))
        {
            if (strstr($_SESSION['previous_page'], "login.php") !== false)
            {
                $_SESSION['previous_page'] = "index.php";
            }
            $loc = $_SESSION['previous_page'];
            $_SESSION['previous_page'] = null;
            header("location: $loc");

        }
        else
        {
            $_SESSION['previous_page'] = null;
            header("location: index.php");
        }
    }
}

function isOwner($resource) {
    if(!isset( $_SESSION['utente'])){
        header("location: login.php");
        die();
    }

    if ($_SESSION['utente']['script'][$resource] === null ||
        $_SESSION['utente']['script'][$resource] === false)
    { header('location: error.php'); }
    return true;

}

