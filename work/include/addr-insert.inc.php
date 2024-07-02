<?php

require "dbms.inc.php";

function insert_address($data) {
    global  $mysqli;

    session_start();

    $provincia = $data[0];
    $regione   = $data[1];
    $citta     = $data[2];
    $indirizzo = $data[3];
    $num_civ   = $data[4];
    $cap       = $data[5];
    $tel_num   = $data[6];
    $comp      = $data[7];


    $stmt = $mysqli->prepare("INSERT INTO `indirizzo_spedizione` (`via`, `numero`, `citta`, `cap`, `regione`, `provincia`, `telefono`, `partita_iva`)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssissss", $indirizzo, $num_civ, $citta, $cap, $regione, $provincia, $tel_num, $comp);


    if ($stmt->execute())
    {
        $last_id = $mysqli->insert_id;
        $uid = intval($_SESSION['utente']['id']);

        $query = "INSERT INTO `possiede` (`id`, `id_utente`, `id_indirizzo`)
                  VALUES (NULL, '$uid', '$last_id')";

        if ($mysqli->query($query) === FALSE)
        {
            header("location: address-setup.php?error=5");
            exit();
        }
    }

}



function update_addr($data)
{
    global  $mysqli;

    session_start();

    $provincia = $data[0];
    $regione   = $data[1];
    $citta     = $data[2];
    $indirizzo = $data[3];
    $num_civ   = $data[4];
    $cap       = $data[5];
    $tel_num   = $data[6];
    $comp      = $data[7];
    $code      = $data[8];

    $qry = "UPDATE `indirizzo_spedizione` SET `via` = ?, 
                                              `numero` = ?, 
                                              `citta` = ?, 
                                              `cap` = ?, 
                                              `regione` = ?, 
                                              `provincia` = ?, 
                                              `telefono` = ?, 
                                              `partita_iva` = ? 
                              WHERE `indirizzo_spedizione`.`id` = ?";

    $stmt = $mysqli->prepare($qry);

    $stmt->bind_param("sssissssi", $indirizzo, $num_civ, $citta, $cap, $regione, $provincia, $tel_num, $comp, $code);

    $stmt->execute();

    $stmt->close();
}


function remove_addr($code)
{
    global $mysqli;

    $qry = "DELETE FROM possiede WHERE `possiede`.`id_indirizzo` = ?";

    $stmt = $mysqli->prepare($qry);

    $stmt->bind_param("i", $code);

    $stmt->execute();

    $stmt->close();
}

