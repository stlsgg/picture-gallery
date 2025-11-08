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
if ($req->isCollection()) {
  Response::ok($db->readAll());
}

if ($req->isSingle()) {
  $id = $req->getId();
  $obj = $db->readById($id);
  if (!$obj) Response::error(404, "object with id $id not found");
  Response::ok($obj);
}

if ($req->isField()) {
  $id = $req->getId();
  $field = $req->getField();
  $obj = $db->readById($id);
  if (!$obj) Response::error(404, "object with id $id not found");
  if (!array_key_exists($field, $obj)) Response::error(404, "field $field not found");
  Response::ok($obj[$field]);
}

// fallback error
Response::error(503, "unsupported error");

