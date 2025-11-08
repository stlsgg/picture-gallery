<?php
require_once "./Storage.php";
require_once "./Request.php";
require_once "./Response.php";

$uri = $_SERVER["REQUEST_URI"];


// open meta.json
try {
  $db = new Storage("../data/meta.json");
} catch (Exception $err) {
  Response::error(
    code: 500,
    message: "error occurred while trying to open database: $err: "
      . $err->getMessage()
  );
}

// open request
try {
$req = new Request($uri);
} catch (Exception $err) {
  Response::error(
    code: 400,
    message: "error occurred while reading request: $err: "
      . $err->getMessage()
  );
}

// get response by request
$id = $req->getId();
$field = $req->getField();
Response::ok(["id" => $id, "field" => $field]);
