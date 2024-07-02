<?php

    require "include/template2.inc.php";
    require "include/dbms.inc.php";
    require "include/prod.inc.php";
    include "include/auth.inc.php";
    
    
    $main = new Template("NiceAdmin/dtml/frame_private.html");
    $body = new Template("NiceAdmin/dtml/product-upload.html");
    session_start();
    if(isset($_REQUEST['message'])){
        switch ($_REQUEST['message']) {
            case 20:
                $body->setContent("message", "20");
                break;
            case 21:
                $body->setContent("message", "21");
                break;
            case 23:
                $body->setContent("message", "23");
                break;
        }
    }
    global $mysqli;
    //query per prendere le categorie disponibli
    $oid = $mysqli->query("SELECT * FROM categoria");
    //gestione errore query
    if (!$oid) {
        echo "Error {$mysqli->errno}: {$mysqli->error}"; exit;
    } 
    $categorie = $oid->fetch_all(MYSQLI_ASSOC);

    //setto le categorie
    foreach($categorie as $categoria){
        $body->setContent("idcat", $categoria['id']);
        $body->setContent("nomecat", $categoria['nome']);
        
    }
    //ERROR DETECTION 
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $errors = array();
            $prodotto = array();
            //gestione delle specifiche
            if(isset($_POST['nomespec']) && is_array($_POST['nomespec']) && 
                isset($_POST['valorespec']) && is_array($_POST['valorespec'])){

                    $nomespecs = $_POST['nomespec'];
                    $valorespecs = $_POST['valorespec'];
                    $json;
                       /* foreach (array_combine($nomespecs, $valorespecs) as $nomespec => $valorespec){
                            $json->$nomespec = $valorespec;
                        }*/
                    $json = array_combine($nomespecs, $valorespecs);
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
            }else{
                // faccio in modo di ricordare almeno la descrizione se ci sono errori
                $_SESSION['descrsess'] = $_POST['descrizione'];
                $prodotto+=["descrizione" => $_POST['descrizione']];   
            }

            if(!isset($_POST['categoria']) || $_POST['categoria'] == ''){
                $errors += ["categoria" => "Inserire categoria prodotto"];
            }else{$prodotto+=["idcat" => $_POST['categoria']];}

            if(!isset($_POST['quantita']) || $_POST['quantita'] == ''){
                $errors += ["quantita" => "Inserire una quantita per prodotto"];
            }else{$prodotto+=["quantita" => $_POST['quantita']];}

            if(!isset($_FILES['datasheet']) && $_FILES['datasheet']['size'] == 0){
                $prodotto+=["nomedatasheet" => null];
                $prodotto+=["nometempds" => null];
            }else{
                $nomedatasheet = $_FILES['datasheet']['name'];
                $tempnameds = $_FILES["datasheet"]["tmp_name"];
                $prodotto += ["nomedatasheet" => $nomedatasheet];
                $prodotto += ["nometempds" => $tempnameds];
            }

            if(!isset($_FILES['foto']) || $_FILES['foto']['size'] == 0){
                $errors += ["foto" => "Inserire foto prodotto"];
            }else{
                $nomefile=$_FILES['foto']['name'];
                $tempname = $_FILES["foto"]["tmp_name"];
                $prodotto+=["nomefile" => $nomefile];
                $prodotto+=["nometemp" => $tempname];
            }

                if(count($errors) >0) {
                    if (array_key_exists("nome", $errors)) {$body->setContent("errorNome", $errors['nome']);}
                    if (array_key_exists("prezzo", $errors)) {$body->setContent("errorPrezzo", $errors['prezzo']);}
                    if (array_key_exists("descrizione", $errors)) {$body->setContent("errorDescrizione", $errors['descrizione']);}
                    if (array_key_exists("quantita", $errors)) {$body->setContent("errorQuantita", $errors['quantita']);}
                    if (array_key_exists("foto", $errors)) {$body->setContent("errorImg", $errors['foto']);}
                }else{
                    
                    upload_prod($prodotto);
                }
                $body->setContent("descrs", $_SESSION['descrsess']);
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