<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";
session_start();

global $mysqli;

    $main = new Template("NiceAdmin/dtml/frame_private.html");
    $body = new Template("NiceAdmin/dtml/main.html");
    $hot = new Template("NiceAdmin/dtml/hot.html");
    
    $query = $mysqli->query("SELECT prodotto_ordine.img, prodotto_ordine.nome,
                                    prodotto_ordine.codice,
                                    prodotto_ordine.prezzo,
                                    SUM(prodotto_ordine.quantita)
                                    FROM prodotto_ordine
                                    GROUP BY prodotto_ordine.nome
                                    ORDER BY SUM(prodotto_ordine.quantita) DESC LIMIT 5");
    do {
        $data = $query->fetch_assoc();
        if ($data) {
            foreach($data as $key => $value) {
                $hot->setContent($key, $value);
            }
        }

    } while ($data);
    $body->setContent("hot", $hot->get());


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

