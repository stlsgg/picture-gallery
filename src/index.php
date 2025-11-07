<?php
// допустимые форматы, т.е. какие картинки можно загружать на сервер
$allowed_mimes = [
  "image/png",
  "image/jpeg",
  "image/webp"
];

// alias на plain/text
function set_text()
{
  header("Content-Type: text/plain");
}

// пути, по которому сохраняем и полную картинку, и превьюшку
$upload_dir_original = "/data/full/";
$upload_dir_thumbnail = "/data/thumb/";

// создаем директории если они отсутствуют
if (!file_exists($upload_dir_original)) {
  mkdir($upload_dir_original, 0755, recursive: true);
}
if (!file_exists($upload_dir_thumbnail)) {
  mkdir($upload_dir_thumbnail, 0755, recursive: true);
}

// проверка существования $_FILES['image']
if (!isset($_FILES["image"]) || empty($_FILES["image"])) {
  // error, exit
  http_response_code(400);
  set_text();
  echo "file to upload not provided\n";
  exit;
}

// сохранение необходимой информации о картинке:
// имя, путь (tmp_name), найти mimetype через класс finfo
// форма имеет поле с картинкой, название поля - image
$image_info = $_FILES["image"];

// проверка на ошибку в $_FILES
$error = $image_info["error"];
if ($error) {
  exit;
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
  http_response_code(406);
  set_text();
  echo "file type not allowed\n";
  exit;
}

// описание картинки может идти с $_POST
$description = $_POST["desc"] ?? "no description provided";

// на этом этапе я уже могу открыть meta.json и проверить дубликат на сервере
// вопрос в том, по какому критерию я проверяю на дубликат: имя или содержимое?
// для простоты буду проверять по имени и просто выходить, если есть файл на
// сервере
// попытка открыть файл meta по заданному пути
if (!file_exists("/data/meta.json")) {
  // создаем meta.json
  touch("/data/meta.json");
}
$first_key = false;
// если пустое - инициализируем $metadata сами внутри скрипта
if (filesize("/data/meta.json") === 0) {
  $metadata = [];
  $first_key = true;
} else {
  // пробуем прочесть meta
  $metadata = json_decode(file_get_contents("/data/meta.json"), true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    set_text();
    echo "error while trying to parse meta.json\n";
    echo "detailed: " . json_last_error_msg() . "\n";
  }
}

// O(n) проход по ключам, ищем по каждому id файл
foreach ($metadata as $id => $pic_info) {
  if (strpos($pic_info["full"], $file_name) !== false) {
    http_response_code(400);
    echo "dublicate image founded in storage\n";
    exit; // если нашли дубликат, выходим из скрипта
  }
}

// формирую объект, который потом вставлю в meta.json
// результирующие пути, по которым буду сохранять изображения
$path_to_full = $upload_dir_original . $file_name;
$path_to_thumb = $upload_dir_thumbnail . "thumb__" . $file_name;


// обработка изображения в зависимости от mime
// здесь делаю watermark & thumbnail
// открыть через gd само изображение
switch ($mime) {
  case "image/png":
    $orig = imagecreatefrompng($file_content);
    break;
  case "image/jpeg":
    $orig = imagecreatefromjpeg($file_content);
    break;
  case "image/webp":
    $orig = imagecreatefromwebp($file_content);
    break;
  default:
    exit; // не обрабатывать и не делать ничего если не подходит mime;
    // default не должен отрабатывать, т.к. ранее в коде делается проверка
    // загруженного файла по mime
}
// размеры $orig
$orig_width = imagesx($orig);
$orig_height = imagesy($orig);

// наложить watermark на оригинал
// открыть watermark
$tmp_watermark = imagecreatefrompng("/data/template/watermark.png");
// размеры открытой  watermark
$tmp_w = imagesx($tmp_watermark);
$tmp_h = imagesy($tmp_watermark);

// масштабирую водяной знак до 5% от оригинального изображения
$wm_w = intval($orig_width * 0.05);
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
    imagepng($orig, $path_to_full);
    imagepng($thumbnail, $path_to_thumb);
    break;
  case "image/jpeg":
    imagejpeg($orig, $path_to_full);
    imagejpeg($thumbnail, $path_to_thumb);
    break;
  case "image/webp":
    imagewebp($orig, $path_to_full);
    imagewebp($thumbnail, $path_to_thumb);
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

file_put_contents("/data/meta.json", json_encode($metadata), LOCK_EX);
