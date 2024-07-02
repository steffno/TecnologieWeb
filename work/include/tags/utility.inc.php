<?php
    class utility extends taglibrary {

        function dummy() {}

        function notify($name, $data, $pars) {

            switch($data) {

                case "00":
                    $msg = "La transazione è andata a buon fine";
                    $class = "alert alert-success alert-dismissible fade show";
                    break;
                case "10":
                    $msg = "Attenzione: Si è verificato un errore";
                    $class = "alert alert-danger alert-dismissible fade show";
                    break;
                case "20":
                    $msg = "Prodotto aggiunto correttamente";
                    $class = "alert alert-success alert-dismissible fade show";
                    break;
                case "21":
                    $msg = "Qualcosa è andato storto";
                    $class = "alert alert-danger alert-dismissible fade show";
                    break;
                case "22":
                    $msg = "Prodotto modificato correttamente";
                    $class = "alert alert-success alert-dismissible fade show";
                    break;
                case "23":
                    $msg = "Prodotto eliminato correttamente";
                    $class = "alert alert-warning alert-dismissible fade show";
                    break;
                case "30":
                    $msg = "Offerta eliminata correttamente";
                    $class = "alert alert-success alert-dismissible fade show";
                    break;
                case "31":
                    $msg = "Qualcosa è andato storto durante la rimozione dell'offerta";
                    $class = "alert alert-success alert-dismissible fade show";
                    break;
                default:
                    $msg = "";
                    $class = "hidden_notification";
                    break;

            }


            $result ="<div class=\"{$class}\">{$msg}.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";

            return $result;

        }

        function show($name, $data, $pars): String {
            global $mysqli;
            
            $main = new Template("skins/dtml/slider.html");

            /*
            *
            * MOST SELLED
            *
            */
            $query = $mysqli->query("SELECT prodotto_ordine.img, prodotto_ordine.nome,
                                            prodotto_ordine.codice,
                                            SUM(prodotto_ordine.quantita)
                                            FROM prodotto_ordine
                                         	GROUP BY prodotto_ordine.nome
                                            ORDER BY SUM(prodotto_ordine.quantita) DESC LIMIT 5");

            do {
                $data = $query->fetch_assoc();
                if ($data) {
                    $main->setContent("IMGPROD", $data['img']);
                    $main->setContent("NOMEPROD", $data['nome']);
                    $main->setContent("CODE", $data['codice']);
                }

            } while ($data);
            
            return $main->get();
        }

        static function cryptify($data): string
        {
            return md5(md5(md5(md5(md5($data)))));
        }


        function sales($name, $data, $pars) {
            global $mysqli;
            $oid = $mysqli->query("SELECT COUNT (id) FROM ordine WHERE data = CURDATE()");
        }

        function report($name, $data, $pars){
            global $mysqli;
            $report = new Template("NiceAdmin/dtml/{$name}.html");
                
            $oid = $mysqli->query("SELECT {$pars['fields']} FROM {$pars['table']} {$pars['others']}");
        
            if(!$oid){
                header("location: error.html");
                echo "Error {$mysqli->errno}: {$mysqli->error}"; exit;
            }

            do {
                $data = $oid->fetch_assoc();
                if ($data) {
                    foreach($data as $key => $value) {
                        $report->setContent($key, $value);
                    }
                }

            } while ($data);
            return $report->get();
        }
    }

?>

