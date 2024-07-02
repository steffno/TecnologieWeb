<?php
    require "dbms.inc.php";
    

    global $mysqli;
    $nuovostato = '';
    session_start();

    if(isset($_POST['id']))
    {
            switch($_POST['stato']){
                case "in preparazione":
                    $nuovostato = "preparato";
                    break;
                case "preparato":
                    $nuovostato = "in transito";
                    break;
                case "in transito":
                    $nuovostato = "in consegna";
                    break;
                case "in consegna":
                    $nuovostato = "consegnato";
                    break;
            }   

            $query = $mysqli->prepare("UPDATE ordine SET stato = ? WHERE ordine.id = ?");
            $query->bind_param("si", $nuovostato, $_POST['id']);
            $query->execute();
    
    }


    print_r($nuovostato);
?>