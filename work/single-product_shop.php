<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/tags/frame-public-utils.php";
require "include/php-utils/price-formatter.php";

const DEFAULT_T = null;

/*
 *
 * ##### QUERIES #####
 *
 */
$product_qry = "SELECT p.id, p.nome, p.descrizione, p.specifica, p.prezzo, p.codice, p.img, p.id_categoria, o.percentuale,
                po.prezzo as sconto, AVG(Cast(r.valore as Float)) as valore
                FROM prodotto p 
                LEFT JOIN recensione r
                    ON r.id_prodotto = p.id
                LEFT JOIN prodotto_offerta po 
                    ON po.id_prodotto = p.id
                LEFT JOIN offerta o 
                    ON po.id_offerta = o.id
                WHERE p.codice = '{CODE}'";

$review_prod = 'SELECT r.id, r.testo, r.valore, r.id_prodotto, u.nome, u.cognome 
                FROM `recensione` r 
                JOIN utente u 
                ON r.id_utente = u.id 
                WHERE r.id_prodotto = {ID} 
                ORDER BY r.valore DESC';

$product_cat = "SELECT c.nome FROM categoria c WHERE c.id = {CAT}";


$warehouse_prod = "SELECT m.id, m.quantita FROM `magazzino` m WHERE m.id_prodotto = {IDPROD}";


/*
 *
 * #### HTML INJECTIONS
 *
 */
$product_img = '<div data-thumb="{IMG}">
                    <img src="{IMG}" />
                </div>';

$product_spec = '<tr>
                  <td>
                    <h5>{KEY}</h5>
                  </td>
                  <td>
                    <h5>{VAL}</h5>
                  </td>
                </tr>';


function reviewStyle($rev, $count)
{
    $_rev = $rev;
    switch ($count) {
        case 1:
            $_rev->setContent("STYLE1", "");
            $_rev->setContent("STYLE2", 'style="color: #777777"');
            $_rev->setContent("STYLE3", 'style="color: #777777"');
            $_rev->setContent("STYLE4", 'style="color: #777777"');
            $_rev->setContent("STYLE5", 'style="color: #777777"');
            break;
        case 2:
            $_rev->setContent("STYLE1", "");
            $_rev->setContent("STYLE2", "");
            $_rev->setContent("STYLE3", 'style="color: #777777"');
            $_rev->setContent("STYLE4", 'style="color: #777777"');
            $_rev->setContent("STYLE5", 'style="color: #777777"');
            break;
        case 3:
            $_rev->setContent("STYLE1", "");
            $_rev->setContent("STYLE2", "");
            $_rev->setContent("STYLE3", "");
            $_rev->setContent("STYLE4", 'style="color: #777777"');
            $_rev->setContent("STYLE5", 'style="color: #777777"');
            break;
        case 4:
            $_rev->setContent("STYLE1", "");
            $_rev->setContent("STYLE2", "");
            $_rev->setContent("STYLE3", "");
            $_rev->setContent("STYLE4", "");
            $_rev->setContent("STYLE5", 'style="color: #777777"');
            break;
        case 5:
            $_rev->setContent("STYLE1", "");
            $_rev->setContent("STYLE2", "");
            $_rev->setContent("STYLE3", "");
            $_rev->setContent("STYLE4", "");
            $_rev->setContent("STYLE5", "");
            break;
    }
    return $_rev;
}


$main = new Template("skins/dtml/frame_public.html");
$single_product = new Template("skins/dtml/single-product.html");


setup_frame_public($main);


$CODE = DEFAULT_T;

if (isset($_GET['code'])) {
    $CODE = $_GET['code'];
}

if ($CODE != DEFAULT_T) {
    global $mysqli;
    $oid = $mysqli->query(str_replace("{CODE}", $CODE, $product_qry));

    $prodotto = $oid->fetch_assoc();

    if ($prodotto['id'] == '') {
        header("location: error.php?error=404");
        exit();
    }

    $ds = $mysqli->query("SELECT d.path FROM datasheet d WHERE d.id_prodotto = {$prodotto['id']}");
    if ($ds->num_rows !== 0) {
        $datas = $ds->fetch_assoc();
        $single_product->setContent("datasheet", '<a id="datasheet-a" href="' . $datas['path'] . '" download>Ottieni il datasheet</a>');
    } else $single_product->setContent("datasheet", '<a id="datasheet-a" href="" download>Ottieni il datasheet</a>');


    $review_area = new Template("skins/dtml/dtml_items/single-prod-review-area.html");
    $review_area->setContent("IDPROD", $prodotto['id']);
    $review_area->setContent("LOCATION", urlencode($_SERVER['REQUEST_URI']));

    $oid = $mysqli->query(str_replace("{ID}", $prodotto['id'], $review_prod));
    $review = $oid->fetch_all(MYSQLI_ASSOC);
    $single_product->setContent("rev_c", $oid->num_rows);
    $single_product->setContent("codice", $CODE);

    $_5 = 0;
    $_4 = 0;
    $_3 = 0;
    $_2 = 0;
    $_1 = 0;
    foreach ($review as $r) {
        $local_rev = new Template("skins/dtml/dtml_items/single-prod-review.html");

        $local_rev->setContent("IMG", "");
        $local_rev->setContent("TESTO", $r['testo']);
        $local_rev->setContent("USER", $r['nome'] . " " . $r['cognome']);
        $local_rev->setContent("VAL", $r['valore']);

        if ($r['valore'] == 1) {
            $_1++;
            $local_rev = reviewStyle($local_rev, 1);
        } else if ($r['valore'] == 2) {
            $_2++;
            $local_rev = reviewStyle($local_rev, 2);
        } else if ($r['valore'] == 3) {
            $_3++;
            $local_rev = reviewStyle($local_rev, 3);
        } else if ($r['valore'] == 4) {
            $_4++;
            $local_rev = reviewStyle($local_rev, 4);
        } else if ($r['valore'] == 5) {
            $_5++;
            $local_rev = reviewStyle($local_rev, 5);
        }

        $single_product->setContent("user_rev", $local_rev->get());
    }
    $single_product->setContent("5count", $_5);
    $single_product->setContent("4count", $_4);
    $single_product->setContent("3count", $_3);
    $single_product->setContent("2count", $_2);
    $single_product->setContent("1count", $_1);


    $oid2 = $mysqli->query(str_replace("{IDPROD}", $prodotto['id'], $warehouse_prod));
    $warehouse_res = $oid2->fetch_all(MYSQLI_ASSOC);

    foreach ($warehouse_res as $wr) {
        $single_product->setContent("disponibilta", $wr['quantita']);

        if ($wr['quantita'] == 0) {
            $single_product->setContent("disp_text", "Esaurito");
        } else if ($wr['quantita'] < 10) {
            $single_product->setContent("disp_text", "In esaurimento");
        } else {
            $single_product->setContent("disp_text", "Disponibile");
        }
    }


    $spec = json_decode($prodotto['specifica']);
    foreach ($spec as $key => $value) {
        $local_spec = $product_spec;
        $local_spec = str_replace("{KEY}", $key, $local_spec);
        $local_spec = str_replace("{VAL}", $value, $local_spec);
        $single_product->setContent("specs", $local_spec);
    }

    $single_product->setContent("imgs", str_replace("{IMG}", $prodotto['img'], $product_img));
    $single_product->setContent("nome", $prodotto['nome']);

    $prezzo = "";
    if ($prodotto['percentuale'] !== null) {
        $prezzo = '<del>' . format_prices($prodotto['prezzo']) . ' €</del>';
        $prezzo .= '<h3>' . format_prices($prodotto['sconto'], 2) . ' €</h3>';
    } else $prezzo = format_prices($prodotto['prezzo']) . ' €';

    $single_product->setContent("prezzo", $prezzo);

    $oid = $mysqli->query(str_replace("{CAT}", $prodotto['id_categoria'], $product_cat));
    $categoria = $oid->fetch_all(MYSQLI_ASSOC);
    foreach ($categoria as $c) {
        $single_product->setContent("catref", "shop.php?page=0&cat=" . str_replace(" ", "_", $c['nome']));
        $single_product->setContent("cat", $c['nome']);
    }

    $single_product->setContent("rat", $prodotto['valore'] === null ? "Non recensito" : $prodotto['valore']);
    $single_product->setContent("info", $prodotto['descrizione']);
    

}

if (isset($_SESSION['auth']) && $_SESSION['auth']) {
    $single_product->setContent("can_review", $review_area->get());
} else {
    $redirector = "login.php?location=" . urlencode($_SERVER['REQUEST_URI']);
    $single_product->setContent("can_review", "<a href=\"$redirector\">Effettua il login per recensire</a>");
}


$main->setContent("dynamic_content", $single_product->get());
$main->close();
