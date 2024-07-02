<?php

    
    require "include/template2.inc.php";
    require "include/dbms.inc.php";
    require "include/prod.inc.php";
    include "include/auth.inc.php";

    $main = new Template("NiceAdmin/dtml/frame_private.html");
    $body = new Template("NiceAdmin/dtml/product-edit.html");
    session_start();
    if(isset($_REQUEST['message'])){
        switch ($_REQUEST['message']) {
            case 22:
                $body->setContent("message", "22");
                break;
            case 21:
                $body->setContent("message", "21");
                break;
        }
    }
    //ERROR DETECTION 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $errors = array();
        $prodotto = array();

        if(isset($_POST['elimina']) && $_POST['elimina'] == "elimina"){
            $result = remove_product();
            print_r($result);
            exit();
        }

         //gestione delle specifiche
         if(isset($_POST['nomespec']) && is_array($_POST['nomespec']) &&
         isset($_POST['valorespec']) && is_array($_POST['valorespec'])){
             $nomespecs = $_POST['nomespec'];
             $valorespecs = $_POST['valorespec'];
             $json = array_combine($nomespecs, $valorespecs);
         //    echo $json;
         $prodotto +=["json" => $json];
     
     }
        // un controllo per ogni campo
        if(!isset($_POST['nomeprod']) || $_POST['nomeprod'] == ''){
            $errors += ["nome" => "Inserire nome prodotto"];
        }else{$prodotto+=["nome" => $_POST['nomeprod']];}

        if(!isset($_POST['prezzo']) || $_POST['prezzo'] == ''){
            $errors += ["prezzo" => "Inserire prezzo"];
        }else{$prodotto+=["prezzo" => $_POST['prezzo']];}

        if(!isset($_POST['descrizione']) || $_POST['descrizione'] == ''){
            $errors += ["descrizione" => "Inserire descrizione prodotto"];
        }else{$prodotto+=["descrizione" => $_POST['descrizione']];   }

        if(!isset($_POST['categoria']) || $_POST['categoria'] == ''){
            $errors += ["categoria" => "Inserire categoria prodotto"];
        }else{$prodotto+=["idcat" => $_POST['categoria']];}

        if(!isset($_POST['quantita']) || $_POST['quantita'] == ''){
            $errors += ["quantitia" => "Inserire una quantita per prodotto"];
        }else{$prodotto+=["quantita" => $_POST['quantita']];}


        if(!isset($_FILES['datasheet']) && $_FILES['datasheet']['size'] == 0){
            $prodotto+=["nomedatasheet" =>   $_SESSION['datasheetProdotto']];
        }else{
            $nomedatasheet = $_FILES['datasheet']['name'];
            $tempnameds = $_FILES["datasheet"]["tmp_name"];
            $prodotto += ["nomedatasheet" => $nomedatasheet];
            $prodotto += ["nometempds" => $tempnameds];
        }

        if(isset($_FILES['foto'])){
            $nomefile=$_FILES['foto']['name'];
            $tempname = $_FILES["foto"]["tmp_name"];
            $prodotto+=["nomefile" => $nomefile];
            $prodotto+=["nometemp" => $tempname];
        }else{
            $prodotto +=["nomefile" => $_SESSION['imgProdotto']];
        }


            
            if(count($errors) >0) {
                if (array_key_exists("nome", $errors)) {$body->setContent("errorNome", $errors['nome']);}
                if (array_key_exists("prezzo", $errors)) {$body->setContent("errorPrezzo", $errors['prezzo']);}
                if (array_key_exists("descrizione", $errors)) {$body->setContent("errorDescrizione", $errors['descrizione']);}
                if (array_key_exists("quantita", $errors)) {$body->setContent("errorQuantita", $errors['quantita']);}
            }else{
                edit_product($prodotto);
            }
    }  


// html inj
$specfield = ' <div class="row" id="dynamic_row">
                    <div class="row" id="dynamic_row">
                    <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="nomespec" name="nomespec[]" placeholder="Nome Specifica" value="{NOME}">
                        <label for="nomespec">Nome della specifica</label>
                    </div>
                    </div>
                    <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="valorespec" name="valorespec[]" placeholder="Valore specifica" value="{VALORE}">
                        <label for="valorespec">Valore della specifica</label>
                    </div>
                    </div>
                    <div class="col-md-2"><a href="javascript:void(0);" class="remove_input_button" id="remove_input_button"
                    title="Add field">
                        <!-- <img src="add-icon.png"/> --> <h2><i class="bi bi-dash-square"></i></h2>
                    </a></div><div><br></div></div>
                    </div>';
   

    $oid = $mysqli->query("SELECT prodotto.id, prodotto.nome, descrizione, prezzo, specifica, img, codice, categoria.nome as nomeCat, categoria.id as idCat, datasheet.path FROM prodotto 
                            LEFT JOIN categoria on categoria.id = prodotto.id_categoria 
                            LEFT JOIN datasheet on datasheet.id_prodotto = prodotto.id
                            WHERE prodotto.id = {$_REQUEST['key']}");

    //gestione errore query
    if (!$oid ) {
        echo "Error {$mysqli->errno}: {$mysqli->error}"; exit;
        
    } 
    $prod = $oid->fetch_array(MYSQLI_ASSOC);
    if(empty($prod)){
        header("location: NiceAdmin/error.html");
        exit;
    }
    $body->setContent("nome", $prod['nome']);
    $body->setContent("prezzo", $prod['prezzo']);
    $body->setContent("descrizione", $prod['descrizione']);
    $body->setContent("imgPath", $prod['img']);

        $spec = json_decode($prod['specifica']);
        foreach($spec as $nome => $valore){
            $local_specfield = $specfield;
            $local_specfield = str_replace("{NOME}", $nome, $local_specfield);
            $local_specfield = str_replace("{VALORE}", $valore, $local_specfield);
            $body->setContent("spec", $local_specfield);
        }
    

    //salvo =in sessione
    $_SESSION['idProdotto'] = $prod['id'];
    $_SESSION['imgProdotto'] = $prod['img'];
    $_SESSION['datasheetProdotto'] = $prod['path'];
    //popolo le categorie e do un selected se i valori sono uguali

    $oid = $mysqli->query("SELECT * FROM categoria");
    //gestione errore query
    if (!$oid) {
        echo "Error {$mysqli->errno}: {$mysqli->error}"; exit;
    } 
    $categorie = $oid->fetch_all(MYSQLI_ASSOC);

    //setto le categorie
    foreach($categorie as $categoria){
        
        if($prod['idCat'] == $categoria['id']){
            $body->setContent("selected",  "selected");
        }else{$body->setContent("selected", "");} //se i valori son diversi, testo vuoto
        $body->setContent("idcat", $categoria['id']);
        $body->setContent("nomecat", $categoria['nome']);
    }
    if (isOwner("dashboard.php"))
    {
        $main->setContent("body", $body->get());
        if( !isset($_SESSION['utente']['username']))
            $main->setContent("username", "errore");
        else
            $main->setContent("username", $_SESSION['utente']['username']);

       
    }
   
    $main->close();

?>