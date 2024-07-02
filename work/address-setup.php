<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/tags/frame-public-utils.php";

$main = new Template("skins/dtml/frame_public.html");
$addr = new Template("skins/dtml/insert_address.html");

setup_frame_public($main);

global $mysqli;

if (isset($_GET['code']))
{
    $code = $_GET['code'];
    $qry  = "SELECT isp.via, isp.numero, isp.citta, isp.cap, isp.regione, isp.provincia, isp.telefono, isp.partita_iva 
             FROM indirizzo_spedizione isp 
             WHERE isp.id = $code";
    $oid = $mysqli->query($qry);

    $data = $oid->fetch_assoc();

    $reg = $data['regione'];
    $pro = $data['provincia'];
    $cit = $data['citta'];
    $via = $data['via'];
    $num = $data['numero'];
    $cap = $data['cap'];
    $tel = $data['telefono'];
    $iva = $data['partita_iva'];

    $addr->setContent("reg", "<option value=$reg>$reg</option>");
    $addr->setContent("pro", "<option value=$pro>$pro</option>");
    $addr->setContent("cit", "<option value=$cit>$cit</option>");
    $addr->setContent("via", $via);
    $addr->setContent("num", $num);
    $addr->setContent("cap", $cap);
    $addr->setContent("tel", $tel);
    $addr->setContent("iva", $iva);
}



$main->setContent("dynamic_content", $addr->get());

$main->close();


?>