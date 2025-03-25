<?php
    include "motorplantilla.php";
    $json = file_get_contents('indianred.json');
    $datos = json_decode($json, true);
    echo renderTemplate('landing.html', $datos);
?>