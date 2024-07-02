<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/php-utils/products-query_generator.php";
require "include/tags/frame-public-utils.php";
require "include/php-utils/price-formatter.php";

const DEFAULT_T = null;
global $mysqli;

/*
 *
 * ##### TEMPLATE GEN SECTION #####
 *
 */
$main = new Template("skins/dtml/frame_public.html");
$shop = new Template("skins/dtml/shop.html");



/*
 *
 * ##### HTML SCRIPTS #####
 *
 */
$no_prod_found = "<style>#opr{color: #0a4968BB;}</style><h3 id =\"opr\">Nessun prodotto trovato</h3>";

$opts = array(
    "<option value=\"0\" id=\"name\" {SEL}>Nome</option>",
    "<option value=\"1\" id=\"pasc\" {SEL}>Prezzo asc</option>",
    "<option value=\"2\" id=\"pdesc\" {SEL}>Prezzo desc</option>",
    "<option value=\"3\" id=\"rec\" {SEL}>Recensioni</option>"
);



/*
 *
 * ##### CATEGORY LIST QUERY #####
 *
 */
$qry3 = "SELECT c.nome FROM categoria c ORDER BY c.nome ASC";



/*
 *
 * GET METHOD VARIABLES
 *
 */
$LIMIT = 9;
$PAGE = 1;
$SORT = DEFAULT_T;
$FIND = DEFAULT_T;
$CAT = DEFAULT_T;
$MAXITEMS = 0;


/*
 *
 * ##### SESSION AREA #####
 *
 */
setup_frame_public($main);



/*
 *
 *
 * ##### GET METHOD AREA FOR SET SUPP VARS #####
 *
 *
 */
if (isset($_GET['limit']))
{
    $LIMIT = intval($_GET['limit']);
    if (!$LIMIT) $LIMIT = 9;
}

if (isset($_GET['cat']))
{
    $CAT = str_replace("_", " ", $_GET['cat']);
}

if (isset($_GET['page']))
{
    $PAGE = intval($_GET['page']);
}

if (isset($_GET['slice']))
{
    $MAXITEMS = $_GET['slice'];
}

if (isset($_GET['find']))
{
    $FIND = str_replace("_", " ", $_GET['find']);
}

if (isset($_GET['sort']))
{
    $SORT = intval($_GET['sort']);
    $opts[$SORT] = str_replace("{SEL}", "selected", $opts[$SORT]);
    foreach ($opts as $curr)
    {
        $shop->setContent("opts", $curr);
    }
}
else
{
    $opts[0] = str_replace("{SEL}", "selected", $opts[0]);
    $opts[1] = str_replace("{SEL}", "", $opts[1]);
    $opts[2] = str_replace("{SEL}", "", $opts[2]);
    $opts[3] = str_replace("{SEL}", "", $opts[3]);
    foreach ($opts as $curr)
    {
        $shop->setContent("opts", $curr);
    }
}


/*
 *
 * ##### MYSQL AREA #####
 *
 */

// query for categories
$cat_oid = $mysqli->query($qry3)->fetch_all(MYSQLI_ASSOC);
foreach ($cat_oid as $c)
{
    $cat = new Template("skins/dtml/dtml_items/shop-category.html");
    $cat->setcontent("CATHREF", "?page=1&cat=" . str_replace(" ", "_", $c['nome']));
    $cat->setcontent("CATNAME", $c['nome']);
    $shop->setContent("categories", $cat->get());
}
// end categories part


$query = $mysqli->query("SELECT count(*) as _count FROM prodotto");
$prod_nums = $query->fetch_assoc()['_count'];


$query = productsQueryGenerator($FIND, $CAT, $SORT, $MAXITEMS . ',' . $LIMIT);
$oid = $mysqli->query($query);
$prodotto = $oid->fetch_all(MYSQLI_ASSOC);

$counter = 0;
if ($FIND != DEFAULT_T || $CAT != DEFAULT_T)
{
    $shop->setContent("item_count", $oid->num_rows);
    $counter = $oid->num_rows;
}
else
{
    $shop->setContent("item_count", $prod_nums);
    $counter = $prod_nums;
}


/*
 *
 *
 * ##### QUERY RESULT MANAGEMENT (FILLING PRODUCT FRAMES ) #####
 *
 *
 */
$it = 0;
$pos = 0;


foreach ($prodotto as $p)
{
    $item = new Template("skins/dtml/shop-product.html");
    $item->setContent("href", "single-product_shop.php?code=" . $p['codice']);
    $item->setContent("img", $p['img']);
    $item->setContent("name", $p['nome']);
    $item->setContent("rat", doubleval($p['valore']));

    $prezzo = "";
    if ($p['percentuale'] !== null)
    {
        $prezzo = '<del>'. format_prices($p['prezzo']) . ' €</del>';
        $prezzo .= '<h3>'. format_prices($p['sconto'], 2) .' €</h3>';
    }
    else $prezzo = format_prices($p['prezzo']) . ' €';

    $item->setContent("price",  $prezzo);
    $shop->setContent("item", $item->get());

    if ($it + 1 === $LIMIT) break; // break if max_item visualization was reached

    $it += 1;
}


/*
 *
 * ##### PAGE SHIFTER AREA #####
 *
 */

if ($counter)
{
    $can_incr = 0; // dummy var to skip first 'it' add

    $shiftings = null;

    $shiftings = intdiv($counter, $LIMIT);
    if (($counter % $LIMIT) != 0) $shiftings += 1;

    do{
        if ($can_incr) { $it += $LIMIT; }

        $page_tag = new Template("skins/dtml/dtml_items/shop-page-no.html");

        $page_tag->setContent("page", "?page=" . ($pos+1));

        $page_tag->setContent("limit", "&limit=" . $LIMIT);

        if ($FIND !== DEFAULT_T) { $page_tag->setContent("find", "&find=" . $FIND); }
        else { $page_tag->setContent("find", $FIND); }

        if ($SORT !== DEFAULT_T) { $page_tag->setContent("sort", "&sort=" . $SORT); }
        else { $page_tag->setContent("sort", ""); }

        $lcat = join('_', explode(' ', $CAT));
        if ($CAT !== DEFAULT_T) { $page_tag->setContent("cat", "&cat=" . $lcat); }
        else { $page_tag->setContent("cat", ""); }

        $page_tag->setContent("slice", "&slice=" . ($LIMIT * $pos));

        $page_tag->setContent("NUM", $pos+1);

        $shop->setContent("page_shifter", $page_tag->get());

        $can_incr = 1;
        $shiftings -= 1;
        $pos += 1;

    } while($shiftings);

}
else { $shop->setContent("item", $no_prod_found); }


/*
 *
 * ##### FOOTER CAROUSEL AREA
 *
 */
$query = productsQueryGenerator(DEFAULT_T, $CAT, 3, 10);
$oid = $mysqli->query($query);
$prodotti = $oid->fetch_all(MYSQLI_ASSOC);

$isRated = false;

foreach ($prodotti as $p)
{
    if ($p['valore'] > 0.0)
    {
        $isRated = true;

        $item = new Template("skins/dtml/better_rated-product.html");
        $item->setContent("href", "single-product_shop.php?code=" . $p['codice']);
        $item->setContent("img", $p['img']);
        $item->setContent("name", $p['nome']);
        $item->setContent("rat", doubleval($p['valore']));

        $prezzo = "";
        if ($p['percentuale'] !== null)
        {
            $prezzo = '<del>'. format_prices($p['prezzo']) . ' €</del>';
            $prezzo .= '<h3>'. format_prices($p['sconto'], 2) .' €</h3>';
        }
        else $prezzo = format_prices($p['prezzo']) . ' €';

        $item->setContent("price", $prezzo);
        $shop->setContent("better_rated", $item->get());
    }
    else break;
}

if (!$isRated)
{
    $item = new Template("skins/dtml/dtml_items/no-reviewed-prod.html");
    $item->setContent("img", 'skins/img/sadsmiledark.png');
    $item->setContent("name", 'In questa categoria non ci sono prodotti recensiti!');
    $shop->setContent("better_rated", $item->get());
}



/*
 *
 * MOST SELLED
 *
 */
$query = $mysqli->query("SELECT po.img, po.nome, po.codice, po.prezzo, o.percentuale, SUM(po.quantita), 
                                AVG(Cast(r.valore as Float)) as valore, pof.prezzo as sconto
                                FROM prodotto_ordine po
                                JOIN prodotto p 
                                    ON po.codice = p.codice
                                LEFT JOIN recensione r
                                    ON r.id_prodotto = p.id
                                LEFT JOIN prodotto_offerta pof
                                    ON pof.id_prodotto = p.id
                                LEFT JOIN offerta o
                                    ON pof.id_offerta = o.id
                                GROUP BY po.nome
                                ORDER BY SUM(po.quantita) DESC");
do {
    $data = $query->fetch_assoc();
    if ($data) {
        $item = new Template("skins/dtml/better_rated-product.html");
        $item->setContent("href", "single-product_shop.php?code=" . $data['codice']);
        $item->setContent("img", $data['img']);
        $item->setContent("name", $data['nome']);
        $item->setContent("rat", doubleval($data['valore']));

        $prezzo = "";
        if ($data['percentuale'] !== null)
        {
            $prezzo = '<del>'. format_prices($data['prezzo']) . ' €</del>';
            $prezzo .= '<h3>'. format_prices($data['sconto'], 2) .' €</h3>';
        }
        else $prezzo = format_prices($data['prezzo']) . ' €';

        $item->setContent("price", $prezzo);
        $shop->setContent("mostly_sold", $item->get());
    }

} while ($data);



$main->setContent("dynamic_content", $shop->get());
$main->close();
?>

