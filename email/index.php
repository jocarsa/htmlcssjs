<?php
include "inc/motorplantilla.php";

// Create an instance of the TemplateEngine
$engine = new TemplateEngine();

// Render the header part
$json = file_get_contents('darksalmon.json');
$datos = json_decode($json, true);
$templateUrl = 'email.html';
echo $engine->render($templateUrl, $datos);

?>


