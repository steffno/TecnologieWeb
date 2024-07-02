<?php

    $status = '';
    session_start();

    if(isset($_POST['evt']))
    {
        if ($_POST['evt'] === 'logout')
        {
            session_destroy();
            $status = '200';
        }
    }


    echo $status;
?>