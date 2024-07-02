<?php

require "dbms.inc.php";
require "php-utils/price-formatter.php";

global $mysqli;

$res = '';

$qry = "SELECT p.img, p.codice, p.nome, p.prezzo, o.percentuale, o.scadenza, po.prezzo as sconto
        FROM prodotto p 
        JOIN prodotto_offerta po ON po.id_prodotto = p.id
        JOIN offerta o ON po.id_offerta = o.id 
        WHERE o.scadenza >= CURRENT_DATE()
        ORDER BY RAND() LIMIT 1";

$oid = $mysqli->query($qry);

if (!$oid->num_rows)
{
    $res = 'none';
}
else
{
    $res = array();
    $data = $oid->fetch_assoc();

    $data['prezzo'] = format_prices($data['prezzo']);
    $data['sconto'] = format_prices($data['sconto']);

    $res = json_encode($data);

}

print_r($res);