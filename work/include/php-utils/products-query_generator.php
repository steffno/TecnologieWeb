<?php


$__query = "SELECT DISTINCT p.id, p.nome, p.descrizione, p.prezzo, p.codice, p.img, p.id_categoria, o.percentuale, 
                po.prezzo as sconto, AVG(Cast(r.valore as Float)) as valore
                FROM prodotto p 
                LEFT JOIN recensione r
                ON r.id_prodotto = p.id
                LEFT JOIN prodotto_offerta po
                    ON po.id_prodotto = p.id
                LEFT JOIN offerta o
                    ON po.id_offerta = o.id        
                {OTHERJOINS}
                {WHERE}
                GROUP BY p.nome
                {ORDER BY} {ITEM} {SORTTYPE} 
                LIMIT {MAXITEMS} ";

$_query = "";

function productsQueryGenerator($FIND, $CATEGORY, $SORT, $MAXITEMS)
{
    global $_query, $__query;

    $_query = $__query;

    if ($FIND != null and $CATEGORY != null and $SORT != null)
    {
        $_query = sortby($SORT);
        $_query = str_replace("{OTHERJOINS}", "JOIN categoria ON p.id_categoria = (SELECT c.id FROM categoria c WHERE c.nome = \"{$CATEGORY}\")", $_query);
        $_query = where("WHERE p.nome LIKE \"%$FIND%\"");
        $_query = str_replace("{MAXITEMS}", $MAXITEMS, $_query);
    }
    elseif ($FIND != null and $CATEGORY != null)
    {
        $_query = str_replace("{OTHERJOINS}", "JOIN categoria ON p.id_categoria = (SELECT c.id FROM categoria c WHERE c.nome = \"{$CATEGORY}\")", $_query);
        $_query = where("WHERE p.nome LIKE \"%$FIND%\"");
    }
    elseif ($FIND != null and $SORT != null)
    {
        $_query = sortby($SORT);
        $_query = where("WHERE p.nome LIKE \"%$FIND%\"");
    }
    elseif ($CATEGORY != null and $SORT != null)
    {
        $_query = sortby($SORT);
        $_query = str_replace("{OTHERJOINS}", "JOIN categoria ON p.id_categoria = (SELECT c.id FROM categoria c WHERE c.nome = \"{$CATEGORY}\")", $_query);
    }
    elseif ($CATEGORY != null)
    {
        $_query = str_replace("{OTHERJOINS}", "JOIN categoria ON p.id_categoria = (SELECT c.id FROM categoria c WHERE c.nome = \"{$CATEGORY}\")", $_query);
    }
    elseif ($SORT != null)
    {
        $_query = sortby($SORT);
    }
    elseif ($FIND != null)
    {
        $_query = where("WHERE p.nome LIKE \"%$FIND%\"");
    }

    return replacePlaceholders($MAXITEMS);
}

function sortby($SORT)
{
    global $_query;
    $_query = str_replace("{ORDER BY}", "ORDER BY", $_query);

    switch ($SORT)
    {
        case 0:
            $_query = str_replace("{ITEM}", "p.nome", $_query);
            $_query = str_replace("{SORTTYPE}", "ASC", $_query);
            break;
        case 1:
            $_query = str_replace("{ITEM}", "p.prezzo", $_query);
            $_query = str_replace("{SORTTYPE}", "ASC", $_query);
            break;
        case 2:
            $_query = str_replace("{ITEM}", "p.prezzo", $_query);
            $_query = str_replace("{SORTTYPE}", "DESC", $_query);
            break;
        case 3:
            $_query = str_replace("{ITEM}", "valore", $_query);
            $_query = str_replace("{SORTTYPE}", "DESC", $_query);
            break;
    }
    return $_query;
}

function where($where)
{
    global $_query;
    return str_replace("{WHERE}", $where, $_query);
}

function replacePlaceholders($MAXITEMS)
{
    global $_query;
    $_query = str_replace("{ORDER BY}", "", $_query);
    $_query = str_replace("{ITEM}", "", $_query);
    $_query = str_replace("{SORTTYPE}", "", $_query);
    $_query = str_replace("{OTHERJOINS}", "", $_query);
    $_query = str_replace("{WHERE}", "", $_query);
    return str_replace("{MAXITEMS}", $MAXITEMS, $_query);
}