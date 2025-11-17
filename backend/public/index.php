<?php
// constants
$rootPath = "/var/www/backend";
$public = $_SERVER["DOCUMENT_ROOT"];
$dbPath = "$rootPath/src/data/meta.json";
$fullPath = "/upload/full";
$thumbPath = "/upload/thumbnails";
$classDir = "$rootPath/src/classes";

// подключение классов
require_once "$classDir/Router.php";
require_once "$classDir/Response.php";
require_once "$classDir/Request.php";
require_once "$classDir/Storage.php";
require_once "$classDir/Image.php";
require_once "$classDir/ImageHandler.php";
require_once "$classDir/FileManager.php";

$router = new Router();
$db = new Storage($dbPath);

$router->on("get", "/check", function () {
  Response::ok(200, "check success");
});

$router->on("get", "/images/{id:int}", function ($id) use ($db) {
  if ($db->readById($id)) {
    Response::ok(200, $db->readById($id));
  }
  Response::error(404, "not found");
});

$router->on("post", "/images", function () use ($db, $rootPath, $fullPath, $thumbPath) {
  $uploadFullPath = "$rootPath/public$fullPath";
  $uploadThumbPath = "$rootPath/public$thumbPath";

  // create dirs if not existing
  if (!file_exists($uploadFullPath)) {
    FileManager::mkdir($uploadFullPath);
  }
  if (!file_exists($uploadThumbPath)) {
    FileManager::mkdir($uploadThumbPath);
  }

  try {
    $image = new Image();
  } catch (Exception $error) {
    match ($error) {
      'FileNotFoundException' => Response::error(400, "file not found in \$_FILES['image'] field"),
      'FileUploadException' => Response::error(400, "upload error: $image->error"),
      'FileUploadException' => Response::error(406, "file type not allowed"),
      default => Response::error(500, "unexpected error occurred while opening \$_FILES['image']")
    };
  }

  $image->name = FileManager::hash($image->tmp) . ".$image->fext";
  // проверка на дубликат на сервере
  if (file_exists("$uploadFullPath/$image->name")) {
    Response::error(409, "file already exists");
  }

  // обработка изображения:
  $imageWorker = new ImageHandler($image->tmp, $image->mimetype);
  //  1. thumbnail с подписью в виде даты
  $thumbnail = $imageWorker->createThumbnail();
  //  2. watermark на оригинал
  $imageWorker->putWatermark();

  // добавление информации в meta.json (добавление объекта)
  $imageObject = [
    "desc" => Request::data()['desc'] ?? "no description",
    /* "desc" => $_POST["desc"] ?? "no description", */ //
    "full" => "$fullPath/$image->name",
    "thumb" => "$thumbPath/thumb__$image->name"
  ];

  $db->create($imageObject);

  // загрузка на сервер через FileManager
  FileManager::saveImage($thumbnail->getImage(), "$uploadThumbPath/thumb__$image->name", $image->mimetype);
  FileManager::saveImage($imageWorker->getImage(), "$uploadFullPath/$image->name", $image->mimetype);
  Response::ok(201, "created");
});

$router->on("get", "/images", function () use ($db) {
  Response::ok(200, $db->readAll());
});

$router->listen();
