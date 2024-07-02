<?php

require "dbms.inc.php";

global $mysqli;

function generateCode(): string
{
    global $mysqli;

    $oid = $mysqli->query("SELECT codice FROM ordine ORDER BY id DESC LIMIT 1");
    $lastcode = $oid->fetch_assoc();
    $code = $lastcode['codice'];
    $temp = str_replace("CDO", "", $code);
    $temp++;
    $builder = "CDO";
    $zero_nums = strlen($code) - strlen($temp) - 3;
    if ($zero_nums === 0) $zero_nums += 8;
    for ($i = 0; $i < $zero_nums; $i++) {
        if (!$zero_nums) $zero_nums += 4;
        for ($i = 0; $i < $zero_nums; $i++) {
            $builder .= '0';
        }
    }
    return $builder . $temp;
}

function getLastId()
{
    global $mysqli;

    $oid = $mysqli->query("SELECT id FROM ordine ORDER BY id DESC LIMIT 1");
    $lastcode = $oid->fetch_assoc();
    return $lastcode['id'];
}


session_start();

$result = '';

$userid = $_SESSION['utente']['id'];

$products_query = "SELECT p.id, p.img, p.nome, p.codice, p.prezzo, pc.quantita FROM prodotto_carrello pc 
                    JOIN prodotto p ON p.id = pc.id_prodotto
                    WHERE pc.id_utente = $userid";

if (isset($_POST['_tot']) && isset($_POST['_code']) && isset($_POST['_normAddr'])) {

    $tot = $_POST['_tot'];
    $code = $_POST['_code'];
    $normAddr = $_POST['_normAddr'];

    $query = "INSERT INTO `ordine` (`id`, `stato`, `totale`, `codice`, `data`, `id_spedizione`, `id_utente`, `id_corriere`)
              VALUES (NULL, 'in preparazione', ?, ?, ?, ?, ?, ?) ";

    $query2 = "INSERT INTO `prodotto_ordine` (`id`, `img`, `nome`, `codice`, `prezzo`, `id_ordine`, `quantita`)
               VALUES (NULL, ?, ?, ?, ?, ?, ?) ";

    $query5 = "UPDATE `magazzino` SET `quantita` = (SELECT m.quantita FROM `magazzino` m WHERE m.id_prodotto = ?) - ?
		       WHERE `magazzino`.`id_prodotto` = ?";

    $oid = $mysqli->query($products_query);
    $products = $oid->fetch_all(MYSQLI_ASSOC);

    $array = array();
    $i = 0;
    foreach ($products as $p) {
        $qnt = $p['quantita'];
        $pid = $p['id'];

        $oid6 = $mysqli->query("SELECT m.quantita FROM magazzino m WHERE m.id_prodotto = $pid");
        $res = $oid6->fetch_assoc();
        if ($res['quantita'] < $qnt) {
            $array[$p['nome']] = $res['quantita'];
        }
    }

    if (count($array) > 0) {
        $result = json_encode($array);
    } else {

        $currentData = date("Y-m-d");
        $newCode = generateCode();

        $stmt = $mysqli->prepare($query);

        $stmt->bind_param("dssiii", $tot, $newCode, $currentData, $normAddr, $userid, $code);
        $stmt->execute();

        $stmt->close();

        $lid = getLastId();

        foreach ($products as $p) {

            $local_query2 = $query2;

            $stmt2 = $mysqli->prepare($local_query2);
            $stmt2->bind_param("sssdii", $p['img'], $p['nome'], $p['codice'],
                $p['prezzo'], $lid, $p['quantita']);

            $stmt2->execute();
            $stmt2->close();

            $local_query5 = $query5;

            $stmt5 = $mysqli->prepare($local_query5);
            $stmt5->bind_param("iii", $p['id'], $p['quantita'], $p['id']);
            $stmt5->execute();
            $stmt5->close();

        }

        $query3 = "DELETE FROM `prodotto_carrello` WHERE `prodotto_carrello`.`id_utente` = $userid";
        $mysqli->query($query3);

    }

}

print_r($result);





