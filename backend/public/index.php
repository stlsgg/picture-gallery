<?php
require_once "../src/classes/Controller.php";

$uri = $_SERVER["REQUEST_URI"];

$app = new Controller();
$app->requestHandler();
