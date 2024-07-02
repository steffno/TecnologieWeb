<?php

DEFINE("NOTSET", "Non impostato");

    require "include/template2.inc.php";
    require "include/dbms.inc.php";
    require "include/tags/frame-public-utils.php";

    $main = new Template("skins/dtml/frame_public.html");
    $profile = new Template("skins/dtml/profile.html");

    setup_frame_public($main);
    check_auth();

    $profile->setContent("user_name", $_SESSION['utente']['nome'] . " " . $_SESSION['utente']['cognome']);
    $profile->setContent("username", $_SESSION['utente']['username']);
    $profile->setContent("email", $_SESSION['utente']['email']);

    $uid = $_SESSION['utente']['id'];


    global $mysqli;

    $query = "SELECT `indirizzo_spedizione`.`id`, 
                    `indirizzo_spedizione`.`via`, 
                    `indirizzo_spedizione`.`numero`, 
                    `indirizzo_spedizione`.`citta`, 
                    `indirizzo_spedizione`.`cap`,
                    `indirizzo_spedizione`.`regione`,
                    `indirizzo_spedizione`.`provincia`,
                    `indirizzo_spedizione`.`telefono`,
                    `indirizzo_spedizione`.`partita_iva`
                FROM `possiede` 
                JOIN `indirizzo_spedizione` ON `possiede`.`id_utente` = '$uid'
                AND `indirizzo_spedizione`.id = `possiede`.`id_indirizzo`";

    $res = $mysqli->query($query)->fetch_all(MYSQLI_ASSOC);


    $i = 0;
    foreach ($res as $r)
    {
        $addr_area = new Template("skins/dtml/dtml_items/profile-addr.html");

        $data = "";
        $data = $data . $r['via'] . ", ". $r['numero'] . ", " . $r['citta'] . '<br>';
        $data = $data . $r['regione'] . ", ". $r['provincia'] . ", ". $r['cap'] .'<br>';
        $data = $data . $r['telefono'] . '<br>';
        $data = $data . $r['partita_iva'] . '<br>';


        $addr_area->setContent("num", ++$i);
        $addr_area->setContent("indirizzo", $data);
        $addr_area->setContent("code", $r['id']);

        $profile->setContent("addr_item", $addr_area->get());
    }

    $main->setContent("dynamic_content", $profile->get());

    $main->close();
?>

