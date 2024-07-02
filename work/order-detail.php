<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/php-utils/price-formatter.php";
require "include/tags/frame-public-utils.php";

global $mysqli;

$main = new Template("skins/dtml/frame_public.html");
$body = new Template("skins/dtml/order-list-detail.html");

setup_frame_public($main);
check_auth();

$userid = $_SESSION['utente']['id'];

$oid = $mysqli->query("SELECT ordine.id, ordine.stato, ordine.totale, ordine.codice, ordine.data, utente.nome, 
                                     utente.cognome, utente.username, utente.email, indirizzo_spedizione.via, 
                                     indirizzo_spedizione.numero, indirizzo_spedizione.citta, indirizzo_spedizione.cap, 
                                     indirizzo_spedizione.regione, indirizzo_spedizione.provincia, 
                                     indirizzo_spedizione.telefono, corriere.tipologia, corriere.azienda, 
                                     corriere.prezzo as costo
                              FROM ordine 
                              LEFT JOIN utente ON ordine.id_utente = utente.id
                              LEFT JOIN indirizzo_spedizione ON ordine.id_spedizione = indirizzo_spedizione.id
                              LEFT JOIN corriere ON ordine.id_corriere = corriere.id
                              WHERE ordine.codice = '{$_GET['key']}' ");

if (!$oid) {
    header("location: error.html");
    echo "Error {$mysqli->errno}: {$mysqli->error}";
    exit;
}

$data = $oid->fetch_assoc();
if ($data) {
    foreach ($data as $key => $value) {
        if ($key === 'totale')
        {
            $tot = format_prices($value);
            $body->setContent($key, $tot);
        }
        else $body->setContent($key, $value);
    }
}

//print_r($data['id']); exit();

// INIZIO PRODOTTI

$query2 = "SELECT * FROM prodotto_ordine WHERE prodotto_ordine.id_ordine = {$data['id']}";



$oid2 = $mysqli->query($query2);
$products = $oid2->fetch_all(MYSQLI_ASSOC);

foreach ($products as $p) {

    $order_item = new Template("skins/dtml/dtml_items/order-item.html");

    $order_item->setContent("PRODHREF", "single-product_shop.php?code=" . $p['codice']);
    $order_item->setContent("IMMAGINE", $p['img']);
    $order_item->setContent("NOME", $p['nome']);
    $order_item->setContent("PREZZO", format_prices($p['prezzo']));
    $order_item->setContent("CODICE", $p['codice']);
    $order_item->setContent("QUANTITA", $p['quantita']);

    $body->setContent("order_item", $order_item->get());
}

$main->setContent("dynamic_content", $body->get());
$main->close();

?>

