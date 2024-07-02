<?php

require "dbms.inc.php";

global $mysqli;

$res = array();


if (isset($_POST['regione']))
{
    $qry = "SELECT DISTINCT si.regione FROM `stato_italia` si ORDER BY si.regione ASC";
    $oid = $mysqli->query($qry);
    $data = $oid->fetch_all(MYSQLI_ASSOC);

    $i = 0;
    foreach ($data as $r)
    {
        $res[$i++] = $r['regione'];
    }

    $res = json_encode($res);

    unset($_POST['regione']);
}
else if (isset($_POST['reg_prov_assoc']))
{
    $reg = addslashes($_POST['reg_prov_assoc']);
    $qry = "SELECT DISTINCT si.provincia FROM stato_italia si WHERE si.regione = '$reg' ORDER BY si.provincia ASC";
    $oid = $mysqli->query($qry);
    $data = $oid->fetch_all(MYSQLI_ASSOC);

    $i = 0;
    foreach ($data as $r)
    {
        $res[$i++] = $r['provincia'];
    }

    $res = json_encode($res);

    unset($_POST['reg_prov_assoc']);
}
else if (isset($_POST['citta']))
{
    $pro = addslashes($_POST['citta']);
    $qry = "SELECT DISTINCT si.citta FROM stato_italia si WHERE si.provincia = '$pro' ORDER BY si.citta ASC";
    $oid = $mysqli->query($qry);
    $data = $oid->fetch_all(MYSQLI_ASSOC);

    $i = 0;
    foreach ($data as $r)
    {
        $res[$i++] = $r['citta'];
    }

    $res = json_encode($res);

    unset($_POST['citta']);
}


print_r($res);






