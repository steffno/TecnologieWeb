<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/tags/frame-public-utils.php";
require "include/php-utils/price-formatter.php";

global $mysqli;

$main = new Template("skins/dtml/frame_public.html");
$order = new Template("skins/dtml/order.html");

setup_frame_public($main);
check_auth();


$userid = $_SESSION['utente']['id'];
$orders_query = "SELECT o.codice, o.data, o.totale, o.stato, ind.via, ind.numero, ind.cap FROM ordine o
                 LEFT JOIN utente u ON u.id = o.id_utente
                 LEFT JOIN indirizzo_spedizione ind ON ind.id = o.id_spedizione
                 WHERE u.id = {$_SESSION['utente']['id']}
                 ORDER BY o.codice DESC";




$oid = $mysqli->query($orders_query);
$orders = $oid->fetch_all(MYSQLI_ASSOC);

foreach ($orders as $p) {
    $order_item = new Template("skins/dtml/dtml_items/order-detail.html");

    $order_item->setContent("ADDR", $p['cap'].", ". $p['via'].", ". $p['numero']);
    $order_item->setContent("DATE", $p['data']);

    $order_item->setContent("TOTAL", format_prices($p['totale']));
    $order_item->setContent("CODE", $p['codice']);
    $order_item->setContent("STATO", $p['stato']);

    $order->setContent("order_item", $order_item->get());
}

$main->setContent("dynamic_content", $order->get());
$main->close();