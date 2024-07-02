<?php

    require "include/template2.inc.php";
    require "include/dbms.inc.php";
    require "include/tags/frame-public-utils.php";
    require "include/php-utils/price-formatter.php";
    require "include/php-utils/products-query_generator.php";

    $main = new Template("skins/dtml/frame_public.html");
    $home = new Template("skins/dtml/index.html");

    setup_frame_public($main);

    global $mysqli;



    /*
     *
     * RAND ITEMS VISUALIZATION
     *
     *
     *
     */
    $qry1 = "SELECT DISTINCT categoria.nome as cat, prodotto.nome, prodotto.img FROM categoria
            LEFT JOIN prodotto ON prodotto.id_categoria = categoria.id
            LEFT JOIN recensione ON recensione.id_prodotto = prodotto.id
            GROUP BY categoria.nome
            ORDER BY RAND()
            LIMIT 4";

    $prod_oid = $mysqli->query($qry1);
    $data_cat = $prod_oid->fetch_all(MYSQLI_ASSOC);


    $item = new Template("skins/dtml/dtml_items/home-rand-item.html");

    $j = 0;
    foreach ($data_cat as $data)
    {
        $item->setContent("cat"  . ($j+1), $data['cat']);
        $item->setContent("href" . ($j+1), str_replace(" ", "_", $data['cat']));
        $item->setContent("img"  . ($j+1), $data['img']);
        $item->setContent("name" . ($j+1), $data['nome']);
        ++$j;
    }

    $home->setContent("ITEMS", $item->get());






    /*
    *
    * ##### FOOTER CAROUSEL AREA
    *
    */

    $query = productsQueryGenerator(null, null, 3, 10);
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
            $home->setContent("better_rated", $item->get());
        }
        else break;
    }

    if (!$isRated)
    {
        $item = new Template("skins/dtml/dtml_items/no-reviewed-prod.html");
        $item->setContent("img", 'skins/img/sadsmiledark.png');
        $item->setContent("name", 'In questa categoria non ci sono prodotti recensiti!');
        $home->setContent("better_rated", $item->get());
    }

    $main->setContent("dynamic_content", $home->get());

    $main->close();
?>

