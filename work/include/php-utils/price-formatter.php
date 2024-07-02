<?php

/**
 * @param $price
 * @return string
 */
function format_prices($price): string
{
    if (count(explode(".", $price)) === 1) $price .= '.00';
    else if (strlen(explode(".", $price)[1]) === 1) $price .= "0";
    return str_replace(".", ",", $price);
}
