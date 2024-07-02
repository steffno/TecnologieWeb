<?php

    $host = "localhost";
    $user = "root";
    $pass = "";
    $name = "circuitdigest";
    $mysqli = new mysqli($host, $user, $pass, $name);

    $mysqli->query('DELETE FROM offerta  WHERE scadenza < CURRENT_DATE()');

?>