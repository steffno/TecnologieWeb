<?php

require "dbms.inc.php";

global $mysqli;
$status = '';

session_start();

if (isset($_POST['_count']) && isset($_POST['_prod_code'])) {
    $count = $_POST['_count'];
    $prod_code = $_POST['_prod_code'];

    if (!isset($_SESSION['auth'])) {
        $status = 'notlogged';
    } else {
        $user_id = $_SESSION['utente']['id'];

        $oid2 = $mysqli->query("SELECT p.id FROM prodotto p WHERE p.codice ='$prod_code'");
        $prod_id = $oid2->fetch_all(MYSQLI_ASSOC);

        foreach ($prod_id as $pid) {
            $productid = $pid['id'];
            $oid = $mysqli->query("SELECT pc.id, pc.quantita FROM prodotto_carrello pc WHERE pc.id_utente = $user_id AND pc.id_prodotto = '$productid'");

            if ($oid->num_rows) {
                $exist = $oid->fetch_all(MYSQLI_ASSOC);
                foreach ($exist as $e) {
                    $qnt = $count + $e['quantita'];
                    if ($qnt == 0) {
                        // elimina un prodotto
                        $stmt = $mysqli->prepare("DELETE FROM prodotto_carrello WHERE `prodotto_carrello`.`id` = ?");
                        $stmt->bind_param("i", $e['id']);
                        $stmt->execute();

                        $status = 'rimosso';
                    } else {
                        // modifica un prodotto
                        $stmt = $mysqli->prepare("UPDATE prodotto_carrello pc SET pc.quantita = ? WHERE pc.id = ?");
                        $stmt->bind_param("ii", $qnt, $e['id']);
                        $stmt->execute();

                        $status = 'modificato';
                    }
                }

            } else {
                // aggiunge un nuovo prodotto
                $stmt = $mysqli->prepare(" INSERT INTO `prodotto_carrello` (`id`, `id_prodotto`, `id_utente`, `quantita`) 
                                        VALUES (NULL, ?, ?, ?)");
                $stmt->bind_param("iii", $productid, $user_id, $count);
                $stmt->execute();

                $status = 'aggiunto';
            }
        }


    }

}

if (isset($_POST['_delete']) && isset($_POST['_code'])) {  // tastino rosso elimina prodotto
    $_prod_code = $_POST['_code'];
    $user_id = $_SESSION['utente']['id'];

    $oid2 = $mysqli->query("SELECT p.id FROM prodotto p WHERE p.codice ='$_prod_code'");
    $prod_id = $oid2->fetch_assoc();

    $productid = $prod_id['id'];

    $oid3 = $mysqli->query("SELECT pc.id FROM prodotto_carrello pc 
                                  WHERE pc.id_utente = $user_id AND pc.id_prodotto = '$productid'");

    $pcid = $oid3->fetch_assoc();

    $stmt = $mysqli->prepare("DELETE FROM prodotto_carrello WHERE `prodotto_carrello`.`id` = ?");
    $stmt->bind_param("i", $pcid['id']);
    $stmt->execute();

    $status = 'rimosso';


}

echo $status;

?>
