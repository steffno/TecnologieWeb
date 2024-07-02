<?php
require "dbms.inc.php";
require "tags/utility.inc.php";


function register($credenziali) {
    global  $mysqli;

    $uname = $credenziali[0];
    $upass = utility::cryptify($credenziali[1]); // aka password 
    $email = $credenziali[2];
    $nome  = $credenziali[3];
    $cognome = $credenziali[4];

    $cannot_singin = $mysqli->query("SELECT * FROM utente WHERE utente.username = '$uname' AND utente.email = '$email'")->fetch_assoc();

    if ($cannot_singin)
    {
        header("location: register.php?error=1");

    }
    else
    {
        $oid = $mysqli->prepare("INSERT INTO utente(`nome`,`cognome`,`username`,`email`,`password`)
                                 VALUES (?, ?, ?, ?, ?)");
        $oid->bind_param("sssss", $nome, $cognome, $uname, $email, $upass);

        if($oid->execute()){
            $user_data = $mysqli->query("SELECT * FROM utente 
                                  WHERE username = '$uname'
                                  AND password = '$upass'")->fetch_array(MYSQLI_ASSOC);

            if (!$user_data) {
                header("location: login.php?error=2");
                exit();
            }

            session_start();
            $_SESSION['utente'] = $user_data;
            $_SESSION['auth'] = true;

            header("location: index.php?reg=true");
        }
    }
}

