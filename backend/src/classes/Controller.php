<?php
// связующее звено между всеми классами
class Controller
{
  private Storage $db;

  public function __construct()
  {
    require_once __DIR__ . "/Storage.php";
    require_once __DIR__ . "/Response.php";
    require_once __DIR__ . "/Request.php";

    try {
      $this->db = new Storage("/var/www/backend/src/data/meta.json");
    } catch (Exception $err) {
      Response::error(
        code: 500,
        message: "error occurred while trying to open database: $err: "
          . $err->getMessage()
      );
    }
  }

  // upload image to server
  // callback на загрузку фото на сервер
  public function postImage(): void
  {
    require_once __DIR__ . "/Image.php";
    require_once __DIR__ . "/ImageHandler.php";
    require_once __DIR__ . "/FileManager.php";

    // проверка mime загруженного файла идет внутри класса Image
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
    // присвоение имени по содержимому изображения
    $image->name = FileManager::hash($image->tmp);
    $imageName = "$image->name.$image->fext";
    $rootPath = "/var/www/backend/public";
    $fullPath = "/upload/full/$imageName";
    $thumbPath = "/upload/thumbnails/thumb__$imageName";

    // проверка на дубликат на сервере
    if (file_exists($fullPath)) {
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
      "desc" => Request::$data['desc'] ?? "no description",
      "full" => $fullPath,
      "thumb" => $thumbPath
    ];

    $this->db->create($imageObject);

    // загрузка на сервер через FileManager
    FileManager::saveImage($thumbnail->getImage(), $rootPath . $thumbPath, $image->mimetype);
    FileManager::saveImage($imageWorker->getImage(), $rootPath . $fullPath, $image->mimetype);
  }
}
