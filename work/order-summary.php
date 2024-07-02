<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

    $main = new Template("NiceAdmin/dtml/frame_private.html");
    $body = new Template("NiceAdmin/dtml/order-summary.html");
    $main->setContent("username", $_SESSION['utente']['username']);

    global $mysqli;

    $oid = $mysqli->query("SELECT ordine.id, ordine.stato, ordine.totale, 
    ordine.codice, ordine.data, utente.nome, utente.cognome, utente.username, 
    utente.email, indirizzo_spedizione.via, indirizzo_spedizione.numero, indirizzo_spedizione.citta, 
    indirizzo_spedizione.cap, indirizzo_spedizione.regione, indirizzo_spedizione.provincia, indirizzo_spedizione.telefono,
    corriere.tipologia, corriere.azienda, corriere.prezzo as costo
    FROM ordine 
    LEFT JOIN utente ON ordine.id_utente = utente.id
    LEFT JOIN corriere ON ordine.id_corriere = corriere.id
    LEFT JOIN indirizzo_spedizione ON ordine.id_spedizione = indirizzo_spedizione.id
    WHERE ordine.id = {$_GET['key']}");

        if(!$oid){
            header("location: error.html");
            echo "Error {$mysqli->errno}: {$mysqli->error}"; exit;
        }

        

        $data = $oid->fetch_assoc();
        if(empty($data)){
            header("location: NiceAdmin/error.html");
            exit;
        }
        if ($data) {
            foreach($data as $key => $value) {
                $body->setContent($key, $value);
            }
        }
        $body->setContent("id_ordine", $_GET['key']);
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