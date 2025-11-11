<?php
// constants
$rootPath = "/var/www/backend";
$public = $_SERVER["DOCUMENT_ROOT"];
$dbPath = "$rootPath/src/data/meta.json";
$fullPath = "/upload/full/";
$thumbPath = "/upload/thumbnails/";
$classDir = "$rootPath/src/classes";

// подключение классов
require_once "$classDir/Router.php";
require_once "$classDir/Response.php";
require_once "$classDir/Storage.php";

$router = new Router();
$db = new Storage($dbPath);

$router->on("get", "/check", function () {
  Response::ok(200, "check success");
});

$router->on("get", "/images/{id:int}", function ($id) use ($db) {
  if  ($db->readById($id)) {
    Response::ok(200, $db->readById($id));
  }
  Response::error(404, "not found");
});

/* $router->on("delete", "/images/{id:int}", function ($id) use ($db) { */
/*   $db->delete($id); */
/*   Response::ok(204); */
/* }); */

/* $router->on("post", "/images", function () { */
/*   Response::ok(201, "created"); */
/* }); */

$router->on("get", "/images", function () use ($db) {
  Response::ok(200, $db->readAll());
});

$router->listen();
