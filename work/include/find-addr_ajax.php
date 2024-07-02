<?php

require "dbms.inc.php";

session_start();
global $mysqli;

$result = "";

if (isset($_POST['_count']))
{
    $ind = $_POST['_count'];

    $query = "SELECT `indirizzo_spedizione`.`via`, 
                     `indirizzo_spedizione`.`numero`, 
                     `indirizzo_spedizione`.`citta`, 
                     `indirizzo_spedizione`.`cap`,
                     `indirizzo_spedizione`.`regione`,
                     `indirizzo_spedizione`.`provincia`,
                     `indirizzo_spedizione`.`telefono`,
                     `indirizzo_spedizione`.`partita_iva`
                FROM `indirizzo_spedizione` 
                WHERE `indirizzo_spedizione`.`id` = '$ind'";

    $res = $mysqli->query($query);

    $addrs = $res->fetch_assoc();

    $tmp = array();
    $tmp['nom'] = $_SESSION['utente']['nome'] . ' ' . $_SESSION['utente']['cognome'];
    $tmp['via'] = $addrs['via'].', '.$addrs['numero'];
    $tmp['oth'] = $addrs['citta'] .' '. $addrs['provincia'] .' '. $addrs['cap'] .' '. $addrs['regione'];

    if ($addrs['partita_iva'] === '')
        $tmp['tiv'] = $addrs['telefono'];
    else
        $tmp['tiv'] = $addrs['telefono'] .', parita iva: '. $addrs['partita_iva'];

    $result = json_encode($tmp);
}

echo $result;