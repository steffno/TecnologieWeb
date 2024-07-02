<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/tags/frame-public-utils.php";
require "include/php-utils/price-formatter.php";



$main = new Template("skins/dtml/frame_public.html");
$cart = new Template("skins/dtml/cart.html");

global $mysqli;

setup_frame_public($main);
check_auth();


/*
 *
 * #### QUERIES
 *
 */
$userid = $_SESSION['utente']['id'];
$products_query = "SELECT p.id, p.nome, p.prezzo, p.codice, p.img, pc.quantita, o.percentuale, po.prezzo as sconto
                    FROM prodotto_carrello pc 
                    JOIN prodotto p ON p.id = pc.id_prodotto
                    LEFT JOIN prodotto_offerta po ON po.id_prodotto = p.id
                    LEFT JOIN offerta o ON po.id_offerta = o.id                           
                    WHERE pc.id_utente = $userid";

$warehouse_query = "SELECT m.id, m.quantita FROM `magazzino` m WHERE m.id_prodotto = {IDPROD}";


$oid = $mysqli->query($products_query);
$products = $oid->fetch_all(MYSQLI_ASSOC);

foreach ($products as $p)
{
    $cart_item = new Template("skins/dtml/dtml_items/cart-item.html");

    $cart_item->setContent("PRODHREF", "single-product_shop.php?code=" . $p['codice']);
    $cart_item->setContent("PRODIMG", $p['img']);
    $cart_item->setContent("PRODNAME", $p['nome']);
    $cart_item->setContent("CODE", $p['codice']);

    $prezzo = "";
    if ($p['percentuale'] !== null)
    {
        $prezzo = format_prices($p['sconto'], 2) .' €';
    }
    else $prezzo = format_prices($p['prezzo']) . ' €';


    $cart_item->setContent("ITEMCOST", $prezzo);
    $cart_item->setContent("QNT", $p['quantita']);


    $prezzo = "";
    if ($p['percentuale'] !== null)
    {
        $prezzo = format_prices($p['sconto'] * $p['quantita'], 2) .' €';
    }
    else $prezzo = format_prices($p['prezzo'] * $p['quantita']) . ' €';
    $cart_item->setContent("TOT", $prezzo);

    $oid2 = $mysqli->query(str_replace("{IDPROD}", $p['id'], $warehouse_query));
    $max_allowed_qnt = $oid2->fetch_all(MYSQLI_ASSOC);
    foreach ($max_allowed_qnt as $qnt)
    {
        $cart_item->setContent("MAXALLOWEDQNT", $qnt['quantita']);
    }


    $cart->setContent("cart_item", $cart_item->get());
}




$query = "SELECT c.id, c.prezzo, c.tipologia, c.azienda FROM corriere c";

$oid = $mysqli->query($query);
$data = $oid->fetch_all(MYSQLI_ASSOC);


foreach ($data as $d)
{
    $ship_detail = new Template("skins/dtml/dtml_items/shipment-details.html");

    //array_push($id_arr, $d['id']);
    $ship_detail->setContent("CODE", $d['id']);
    $ship_detail->setContent("AZIENDASPED", $d['azienda']);
    $ship_detail->setContent("TIPOLOGIASPED", $d['tipologia']);
    $ship_detail->setContent("COSTOSPED", format_prices(strval($d['prezzo'])));

    $cart->setContent("ship-details", $ship_detail->get());

}



$uid = $_SESSION['utente']['id'];
$query_noiva = "SELECT `indirizzo_spedizione`.`id`, 
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

$query_iva = "SELECT `indirizzo_spedizione`.`id`, 
                     `indirizzo_spedizione`.`via`, 
                     `indirizzo_spedizione`.`numero`, 
                     `indirizzo_spedizione`.`citta`, 
                     `indirizzo_spedizione`.`cap`,
                     `indirizzo_spedizione`.`regione`,
                     `indirizzo_spedizione`.`provincia`,
                     `indirizzo_spedizione`.`telefono`,
                     `indirizzo_spedizione`.`partita_iva`
                FROM `possiede` 
                JOIN `indirizzo_spedizione` ON `possiede`.`id_utente` = 22
                AND  `indirizzo_spedizione`.id = `possiede`.`id_indirizzo`
                AND  `indirizzo_spedizione`.partita_iva != ''";

$res = $mysqli->query($query_noiva);
$res2 = $mysqli->query($query_iva);

if (!$res)
{
    $local_addr = new Template("skins/dtml/dtml_items/checkout-addr-opt.html");
    $local_addr->setContent("ADDR", "Inserisci un nuovo indirizzo");
    $local_addr->setContent("ID", "0");
    $cart->setContent("addr", $local_addr->get());
}
else
{
    $addrs = $res->fetch_all(MYSQLI_ASSOC);

    foreach ($addrs as $addr)
    {
        $local_addr = new Template("skins/dtml/dtml_items/checkout-addr-opt.html");
        $local_addr->setContent("ADDR", $addr['via']. ' '. $addr['numero']);
        $local_addr->setContent("ID", $addr['id']);
        $cart->setContent("addr", $local_addr->get());
    }
}

if ($res)
{
    $addrs2 = $res2->fetch_all(MYSQLI_ASSOC);

    foreach ($addrs2 as $addr)
    {
        $local_addr = new Template("skins/dtml/dtml_items/checkout-addr-opt.html");
        $local_addr->setContent("ADDR", $addr['via']. ' '. $addr['numero']);
        $local_addr->setContent("ID", $addr['id']);
        $cart->setContent("addr_iva", $local_addr->get());
    }
}




$main->setContent("dynamic_content", $cart->get());
$main->close();