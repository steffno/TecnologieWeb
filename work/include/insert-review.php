<?php

require "dbms.inc.php";

global $mysqli;

$status = '';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['message']) && isset($_POST['val-prod']) && isset($_POST['id-prod']))
    {
        $msg = $_POST['message'];
        $user_id = $_SESSION['utente']['id'];
        $product_rat = intval($_POST['val-prod']);
        $product_id = intval($_POST['id-prod']);

        $stmt = $mysqli->prepare("INSERT INTO `recensione` (`id`, `testo`, `valore`, `id_prodotto`, `id_utente`) 
                               VALUES (NULL, ?, ?, ?, ?)");

        $stmt->bind_param("siii", $msg, $product_rat, $product_id, $user_id);
        $stmt->execute();
    }

    $status = 'inserito';
}

print_r($status);
