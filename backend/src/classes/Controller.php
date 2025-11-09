<?php
// связующее звено между всеми классами
class Controller
{
  private Storage $db;
  private Request $request;
  private const array ROUTES = [
    "POST" => fn() => $this->postImage(),
    "GET" => fn() => $this->getImage(),
  ];

  // бизнес процессы:
  // клиент заполнил форму и отправил POST:
  // проверка, что изображения еще нет в системе (проверка на дубликат)
  // надо обработать картинку:
  // добавить водяной знак на оригинал и сохранить на диск
  // создать превью с текущей датой и временем загрузки
  //
  // клиент (браузер) запросил данные о картинках\картинке по api
  //
  // клиент запросил удалить картинку по api
  //
  // клиент запросил изменить описание картинки по api

  // инициализация:
  // открываю базу данных
  // читаю Request
  public function __construct()
  {
    require_once "./Storage.php";
    try {
      $this->db = new Storage("/src/data/meta.json");
    } catch (Exception $err) {
      Response::error(
        code: 500,
        message: "error occurred while trying to open database: $err: "
          . $err->getMessage()
      );
    }
    try {
      $this->request = new Request();
    } catch (Exception $err) {
      Response::error(
        code: 400,
        message: "error occurred while reading request: $err: "
          . $err->getMessage()
      );
    }
  }

  // upload image to server
  // callback на загрузку фото на сервер
  public function postImage(): void
  {
    require_once "./Image.php";
    // проверка mime загруженного файла идет внутри класса Image
    try {
      $image = new Image();
    } catch (Exception $error) {
      switch ($error) {
        case ($error instanceof FileNotFoundException): {
            Response::error(400, "file not found in \$_FILES['image'] field");
            break;
          }
        case ($error instanceof FileUploadException): {
            Response::error(400, "upload error: $image->error");
            break;
          }
        case ($error instanceof FileUploadException): {
            Response::error(406, "file type not allowed");
            break;
          }
        default:
          Response::error(500, "unexpected error occurred while opening \$_FILES['image']");
          break;
      }
    }
    // проверка на дубликат на сервере
    if (file_exists("/upload/full" . $image->name)) {
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
      "desc" => "description",
      "full" => "full/path",
      "thumb" => "thumb/path"
    ];

    $this->db->create($imageObject);

    // загрузка на сервер через FileManager
    FileManager::saveImage($thumbnail->getImage(), "path", $image->mimetype);
    FileManager::saveImage($imageWorker->getImage(), "path", $image->mimetype);
  }

  // перехватывает запрос и вызывает нужный callback
  public function requestHandler(): void
  {
    // TODO проверка на method
    // POST разрешен только на /api/images
    // GET разрешен на id, поля и /api/images
    // get response by request
    if ($this->request->isCollection()) {
      Response::ok($this->db->readAll());
    }

    if ($this->request->isSingle()) {
      $id = $this->request->getId();
      $obj = $this->db->readById($id);
      if (!$obj) Response::error(404, "object with id $id not found");
      Response::ok($obj);
    }

    if ($this->request->isField()) {
      $id = $this->request->getId();
      $field = $this->request->getField();
      $obj = $this->db->readById($id);
      if (!$obj) Response::error(404, "object with id $id not found");
      if (!array_key_exists($field, $obj)) Response::error(404, "field $field not found");
      Response::ok($obj[$field]);
    }

    // fallback error
    Response::error(503, "unsupported error");
  }

  // callback на получение данных с сервера
  public function getImage(): void {}
}
