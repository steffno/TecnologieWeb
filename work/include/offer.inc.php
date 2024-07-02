<?php
    require "dbms.inc.php";
    $ids = array();
    $i=0;
    

if(isset($_POST['percentuale']) &&  isset($_POST['inputDate'])){

    //creo l'offerta
        $oid = $mysqli->prepare("INSERT INTO offerta (id, percentuale, scadenza) VALUES (NULL, ?, ?)");
        $oid->bind_param("is", $_POST['percentuale'],  $_POST['inputDate']);
        $oid->execute();
        $oid->close();
        $id_offerta = $mysqli->insert_id;
        $promozione = $mysqli->query("SELECT * FROM offerta WHERE id = {$id_offerta}")->fetch_array();
    // offerta creata
        // adesso associo l'offerta a ogni prodotto
        if (isset($_POST['checkbox'])){
            foreach($_POST['checkbox'] as $val){
                $ids[] = (int) $val;

                $oid = $mysqli->query("SELECT p.prezzo FROM prodotto p WHERE p.id = $val");
                if ($oid->num_rows != 1) { print_r('error'); exit(); }

                $prezzo = $oid->fetch_assoc();
                $prezzo = number_format($prezzo['prezzo'] - (($prezzo['prezzo'] / 100) * $_POST['percentuale']), 2);

                $oid = $mysqli->prepare("INSERT INTO prodotto_offerta(id, prezzo, id_prodotto, id_offerta) 
                                                VALUES(NULL, ?, ?, ?)");
                $oid->bind_param("dii", $prezzo, $val, $id_offerta);
                    if(!$oid->execute()){
                        $i++;
                    }
                $oid->close(); 
            }
        }

        $returnArr = array("result"=> $i, "promozione" =>$promozione);
        $data= json_encode($returnArr);
        print_r($data);
        exit();
    }
        
if(isset($_POST['id'])){
        $oid = $mysqli->query("SELECT prodotto.id, prodotto.nome, prodotto.prezzo, categoria.nome as nomeC
                                FROM prodotto 
                                LEFT JOIN categoria ON categoria.id = prodotto.id_categoria 
                                RIGHT JOIN prodotto_offerta ON prodotto_offerta.id_prodotto = prodotto.id 
                                WHERE prodotto_offerta.id_offerta = {$_POST['id']} ") ->fetch_all(MYSQLI_ASSOC);
        print_r(json_encode($oid));
        exit();
}

if (isset($_POST['checkboxr'])){
    $i=0;
   
            foreach($_POST['checkboxr'] as $val){  
                 $ids[] = (int) $val; 
                 $oid = $mysqli->query("DELETE FROM prodotto_offerta WHERE prodotto_offerta.id_prodotto = {$val}");
                $i++;
            }
                
            print_r($i);
            exit();
        }

if(isset($_REQUEST['rimuovi'])){
    

    $oid= $mysqli->query("DELETE FROM offerta WHERE id = {$_REQUEST['rimuovi']}");
    if($oid){
        header("location: ../sales.php?message=30");
        exit();
    }
}

if(isset($_REQUEST['rimuoviProdotto'])){
    

    $oid= $mysqli->query("DELETE FROM prodotto_offerta WHERE id_prodotto = {$_REQUEST['rimuoviProdotto']}");
    if($oid){
        header("location: ../sales.php?message=30");
        exit();
    }
}

if(isset($_POST['percentualeModifica']) &&  isset($_POST['inputDateModifica']) && isset($_POST['idPromo'])){
    $oid = $mysqli->prepare("UPDATE offerta SET percentuale = ?, scadenza = ? WHERE id = ? ");
        $oid->bind_param("isi", $_POST['percentualeModifica'],  $_POST['inputDateModifica'], $_POST['idPromo']);
        if($oid->execute()){
            $oid->close();
        }

        $idprom = $_POST['idPromo'];
        $oid = $mysqli->query("SELECT po.id, po.id_prodotto FROM prodotto_offerta po where po.id_offerta = $idprom");
        $pids = $oid->fetch_all(MYSQLI_ASSOC);

        foreach ($pids as $pid)
        {
            $real_pid = $pid['id_prodotto'];
            $poid = $pid['id'];
            $oid = $mysqli->query("SELECT p.prezzo FROM prodotto p WHERE p.id = $real_pid");
            $prezzo = $oid->fetch_assoc();

            $prezzo = number_format($prezzo['prezzo'] - (($prezzo['prezzo'] / 100) * $_POST['percentualeModifica']), 2);

            $stmt = $mysqli->prepare("UPDATE prodotto_offerta po SET po.prezzo = ? WHERE po.id = ?");
            $stmt->bind_param("di", $prezzo, $poid);
            $stmt->execute();
        }

        foreach($_POST['checkboxm'] as $val){
            $ids[] = (int) $val;

            $oid = $mysqli->query("SELECT p.prezzo FROM prodotto p WHERE p.id = $val");
            if ($oid->num_rows != 1) { print_r('error'); exit(); }

            $prezzo = $oid->fetch_assoc();
            $prezzo = number_format($prezzo['prezzo'] - (($prezzo['prezzo'] / 100) * $_POST['percentualeModifica']), 2);


            $oid = $mysqli->prepare("INSERT INTO prodotto_offerta(id, prezzo, id_prodotto, id_offerta) 
                                            VALUES(NULL, ?, ?, ?)");
            $oid->bind_param("iii", $prezzo, $val, $_POST['idPromo']);
                if(!$oid->execute()){
                    $i++;
                }
            $oid->close(); 
           
        }
}
            
?>