<?php

require "addr-insert.inc.php";
require "dbms.inc.php";

if (isset($_POST['_code']) && isset($_POST['_del']))
{
    remove_addr(intval($_POST['_code']));

    echo 'rimuovo';
}
else if (isset($_POST['_code']) && $_POST['_code'] != '' && $_POST['_code'] != -1)
{
    update_addr([$_POST['province'],
        $_POST['region'],
        $_POST['city'],
        $_POST['address'],
        $_POST['number_address'],
        intval($_POST['postal_code']),
        $_POST['phone_number'],
        $_POST['company'],
        intval($_POST['_code'])]);

    echo 'modifico';
}
else if (isset($_POST['province']) && isset($_POST['city']) && isset($_POST['address']) &&
    isset($_POST['number_address']) && isset($_POST['postal_code']) &&
    isset($_POST['phone_number']) && isset($_POST['region']))
{
        insert_address([$_POST['province'],
            $_POST['region'],
            $_POST['city'],
            $_POST['address'],
            $_POST['number_address'],
            intval($_POST['postal_code']),
            $_POST['phone_number'],
            $_POST['company']]);

        echo 'inserisco';
}
