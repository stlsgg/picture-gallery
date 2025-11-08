<?php
// допустимые форматы, т.е. какие картинки можно загружать на сервер
$allowed_mimes = [
  "image/png",
  "image/jpeg",
  "image/webp"
];

// upload dirs: original and thumbnail paths
$DATA_DIR = "./data/";
$FULL_DIR = $DATA_DIR . "full/";
$THUMB_DIR = $DATA_DIR . "thumb/";
$META = $DATA_DIR . "meta.json";

// подготовка директорий перед работой с файлами
// создаем директории если они отсутствуют
function prepare_directories(): void
{
  global $DATA_DIR, $FULL_DIR, $THUMB_DIR;
  if (!file_exists($DATA_DIR)) {
    mkdir($DATA_DIR, 0755, true);
  }
  if (!file_exists($FULL_DIR)) {
    mkdir($FULL_DIR, 0755, recursive: true);
  }
  if (!file_exists($THUMB_DIR)) {
    mkdir($THUMB_DIR, 0755, recursive: true);
  }
}

// abort function: вывод ошибки в http status code и вывод сообщения об ошибке
function abort(int $status_code, string $message): void
{
  http_response_code($status_code);
  header("Content-Type: text/plain");
  echo "$message\n";
  exit;
}

// mapping на изображение: вызов конкретных функций в зависимости от mimetype
// по сути эта проблема решается через ООП и перегрузку метода, но я пока тут
// пишу в процедурном стиле
$img_gd = [
  "image/png" => [
    "open" => fn($filename) => imagecreatefrompng($filename),
    "save" => fn($filename, $path) => imagepng($filename, $path)
  ],

  "image/jpeg" => [
    "open" => fn($filename) => imagecreatefromjpeg($filename),
    "save" => fn($filename, $path) => imagejpeg($filename, $path)
  ],
  "image/webp" => [
    "open" => fn($filename) => imagecreatefromwebp($filename),
    "save" => fn($filename, $path) => imagewebp($filename, $path)
  ],
];

// проверка существования $_FILES['image']
if (!isset($_FILES["image"]) || empty($_FILES["image"])) {
  abort(400, "file to upload not provided");
}

// сохранение необходимой информации о картинке:
// имя, путь (tmp_name), найти mimetype через класс finfo
// форма имеет поле с картинкой, название поля - image
$image_info = $_FILES["image"];

// проверка на ошибку в $_FILES
$error = $image_info["error"];
if ($error) {
  abort(400, "upload error with status code $error");
}

$file_name = $image_info["name"];
// содержимое (точнее путь до картинки в /tmp директории)
$file_content = $image_info["tmp_name"];

// mimetype по содержимому картинки
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file_content);
finfo_close($finfo);

// проверка что загруженный файл подходит под allowed_mimes
if (!in_array($mime, $allowed_mimes)) {
  abort(406, "file type not allowed");
}

// описание картинки может идти с $_POST
$description = $_POST["desc"] ?? "no description provided";

// на этом этапе я уже могу открыть meta.json и проверить дубликат на сервере
// вопрос в том, по какому критерию я проверяю на дубликат: имя или содержимое?
// для простоты буду проверять по имени и просто выходить, если есть файл на
// сервере
// попытка открыть файл meta по заданному пути
if (!file_exists("./data/meta.json")) {
  // создаем meta.json
  touch("./data/meta.json");
}
$first_key = false;
// если пустое - инициализируем $metadata сами внутри скрипта
if (filesize("./data/meta.json") === 0) {
  $metadata = [];
  $first_key = true;
} else {
  // пробуем прочесть meta
  $metadata = json_decode(file_get_contents("./data/meta.json"), true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    echo "detailed: " . json_last_error_msg() . "\n";
    abort(400, "error while trying to parse meta.json");
  }
}

// O(n) проход по ключам, ищем по каждому id файл
foreach ($metadata as $id => $pic_info) {
  if (strpos($pic_info["full"], $file_name) !== false) {
    // если нашли дубликат, выходим из скрипта
    abort(400, "dublicate image founded in storage");
  }
}

// формирую объект, который потом вставлю в meta.json
// результирующие пути, по которым буду сохранять изображения
$path_to_full = $upload_dir_original . $file_name;
$path_to_thumb = $upload_dir_thumbnail . "thumb__" . $file_name;


// обработка изображения в зависимости от mime
// здесь делаю watermark & thumbnail
// открыть через gd само изображение
$orig = $img_gd['open'][$mime]($file_content);

// check orig existing
if (!$orig) {
  abort(500, "failed to open image in gd");
}
// размеры $orig
$orig_width = imagesx($orig);
$orig_height = imagesy($orig);

// наложить watermark на оригинал
// открыть watermark
if (!file_exists("./data/template/watermark.png")) {
  imagedestroy($orig);
  abort(500, "watermark not found");
}
$tmp_watermark = $img_gd['open']["image/png"]("./data/template/watermark.png");

if (!$tmp_watermark) {
  imagedestroy($orig);
  abort(500, "failed to open watermark in gd");
}
// размеры открытой  watermark
$tmp_w = imagesx($tmp_watermark);
$tmp_h = imagesy($tmp_watermark);

// масштабирую водяной знак до 10% от оригинального изображения
$wm_w = intval($orig_width * 0.1);
$scale = $wm_w / $tmp_w;
$wm_h = intval($tmp_h * $scale);

$watermark = imagecreatetruecolor($wm_w, $wm_h);

// прозрачность
imagesavealpha($watermark, true);
imagecopyresampled($watermark, $tmp_watermark, 0, 0, 0, 0, $wm_w, $wm_h, $tmp_w, $tmp_h);

// позиция watermark на оригинальном image
$pos_x = $orig_width - $wm_w - 15;
$pos_y = $orig_height - $wm_h - 15;

// копирую watermark на orig
imagecopy($orig, $watermark, $pos_x, $pos_y, 0, 0, $wm_w, $wm_h);
imagesavealpha($orig, true);

// создать копию изображения (thumb)
$thumbnail = imagecreatetruecolor(300, 300); // у меня на сайте всегда
// 300 на 300 пикселей, я тут босс
// пропорции
$side = min($orig_height, $orig_width); // нахожу наименьшую сторону изобр.
$x = intval(($orig_width - $side) / 2);
$y = intval(($orig_height - $side) / 2);

imagecopyresampled($thumbnail, $orig, 0, 0, $x, $y, 300, 300, $side, $side);

// сохранить thumb & full по путям

switch ($mime) {
  case "image/png":
    if (!imagepng($orig, $path_to_full) || !imagepng($thumbnail, $path_to_thumb)) {
      abort(500, "failed to save processed image");
    };
    break;
  case "image/jpeg":
    if (!imagejpeg($orig, $path_to_full) || !imagejpeg($thumbnail, $path_to_thumb)) {
      abort(500, "failed to save processed image");
    };
    break;
  case "image/webp":
    if (!imagewebp($orig, $path_to_full) || !imagewebp($thumbnail, $path_to_thumb)) {
      abort(500, "failed to save processed image");
    };
    break;
  default:
    exit; // не обрабатывать и не делать ничего если не подходит mime;
    // default не должен отрабатывать, т.к. ранее в коде делается проверка
    // загруженного файла по mime
}

// пост-очистка
imagedestroy($orig);
imagedestroy($tmp_watermark);
imagedestroy($watermark);
imagedestroy($thumbnail);

// сохраняю мету о изображении
if ($first_key) {
  $key = 1;
} else {
  $key = max(array_keys($metadata)) + 1;
}

$metadata[$key] = [
  "desc" => $description,
  "full" => $path_to_full,
  "thumb" => $path_to_thumb
];

file_put_contents("./data/meta.json", json_encode($metadata), LOCK_EX);
