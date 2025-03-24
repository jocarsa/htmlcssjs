<?php
    include "motorplantilla.php";
    $json = file_get_contents('contenido.json');
    $datos = json_decode($json, true);
    echo renderTemplate('034-animacion css.html', $datos);
?>