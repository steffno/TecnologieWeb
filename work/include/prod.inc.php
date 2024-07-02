<?php
require "dbms.inc.php";




    function upload_prod($prodotto){
        // TODO add productio function
        global $mysqli;
        $img = "uploaded_images/".$prodotto['nomefile'];

        $oid = $mysqli->query("SELECT codice FROM prodotto ORDER BY id DESC LIMIT 1");
        $lastcode = $oid->fetch_assoc();
        $code = $lastcode['codice'];
        $temp = str_replace("CD", "", $code);
        $temp++;
        $builder = "CD";
        $zero_nums = strlen($code) - strlen($temp) - 2;
        if(!$zero_nums) $zero_nums+=4;
        for($i=0; $i< $zero_nums; $i++){
            $builder.='0';
        }
        $code = $builder.$temp;

        $json = json_encode($prodotto['json']);
        $query = "INSERT INTO `prodotto`
                        (`nome`, `descrizione`, `specifica`, `prezzo`, `img`, `codice`, `id_categoria`)
                        VALUES (?, ?, ?, ?, ?, ?, ?);";
        $prep = $mysqli->prepare($query);  //prepares the query for action
        $prep->bind_param("sssdssi", $prodotto['nome'], $prodotto['descrizione'], $json, $prodotto['prezzo'],
        $img, $code, $prodotto['idcat']);
        $prep -> execute();
        $prep -> close();


        if ($prodotto['nomedatasheet'] !== null)
        {
            $cartella = 'datasheets/' . $prodotto['nomedatasheet'];
            $pid = "SELECT p.id FROM prodotto p WHERE p.codice = '$code'";
            $pid = $mysqli->query($pid);
            $pid = $pid->fetch_assoc();

            $query = "INSERT INTO `datasheet` (`path`, `id_prodotto`)
                  VALUES (?, ?)";
            $prep = $mysqli->prepare($query);
            $prep->bind_param("si", $cartella, $pid['id']);
            $prep->execute();
            $prep->close();

            //uploado il datasheet
            move_uploaded_file($prodotto['nometempds'], $cartella);
        }



        $cartella = "uploaded_images/".$prodotto['nomefile'];
        //uploado l'immagine
        if(move_uploaded_file($prodotto['nometemp'], $cartella)){
            header("location: product-upload.php?message=20");   
        }else{
            header("location: product-upload.php?message=21"); 
        }
        unset($_SESSION['descrsess']); //distruggo la variabile sessione usata per la descrizione
    }

    function edit_product($prodotto){
        global $mysqli;
        $img = "uploaded_images/".$prodotto['nomefile'];
        $ds = "datasheets/".$prodotto['nomedatasheet'];

        //faccio un controllo, dove se il datacheet non è specificato, allora devo mantenere il vecchio
        if(strcmp($ds, "uploaded_images/") == 0){
            $oid = $mysqli->query("SELECT `path` FROM datasheet WHERE id_prodotto = {$_SESSION['idProdotto']}")->fetch_assoc();
            $ds = $oid['path'];
        }

        //faccio un controllo, dove se l'immagine non è specificata, allora devo mantenere la vecchia
        if(strcmp($img, "uploaded_images/") == 0){
            $oid = $mysqli->query("SELECT img FROM prodotto WHERE id = {$_SESSION['idProdotto']}")->fetch_assoc();;
            $img = $oid['img'];
        }       
        $query = "UPDATE prodotto SET nome=?, descrizione=?, specifica=?,prezzo=?, img=?, id_categoria=? WHERE prodotto.id = ?";
        $prep = $mysqli->prepare($query);
        print_r($prodotto['id']);
        $prep->bind_param("sssdsii", $prodotto['nome'], $prodotto['descrizione'], json_encode($prodotto['json']), $prodotto['prezzo'],
                            $img ,$prodotto['idcat'], $_SESSION[' idProdotto']);
        if($prep->execute()){
            //significa che l'update è andato a buon fine, posso uppare il file
            if(move_uploaded_file($prodotto['nometemp'], $img)){
                header("location: product-edit.php?message=22&key={$_SESSION['idProdotto']}");   
            }else if($prodotto['nometemp'] === ""){
                header("location: product-edit.php?message=22&key={$_SESSION['idProdotto']}");
            }else{
                header("location: product-edit.php?message=21&key={$_SESSION['idProdotto']}");
            }
        }else{
            header("location: product-edit.php?message=21&key={$_SESSION['idProdotto']}");
        }

        $query = "UPDATE datasheet SET `path`= ? WHERE id_prodotto = ?";
        $prep = $mysqli->prepare($query);
        $prep->bind_param("si", $ds, $_SESSION['idProdotto']);
        $prep->execute();
        unset($_SESSION['idProdotto']);
        unset($_SESSION['imgProdotto']);
        unset($_SESSION['datasheetProdotto']);
    }

    function remove_product() {
        global $mysqli;
        $oid = $mysqli->query("DELETE FROM prodotto WHERE prodotto.id = {$_SESSION['idProdotto']}");
        unset($_SESSION['imgProdotto']);
        if(!$oid){
            return false;
        }else{
            return true;
        }
    }
?>

