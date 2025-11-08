<?php
// класс по работе с фотографией - наложение watermark, создание thumbnail
// только возвращает GdImage, сохранением занимается FileManager
class UnsupportedMIMEException extends Exception {}

class ImageHandler
{
  private GdImage $image;
  private array $sizes = [
    "width" => 0,
    "height" => 0,
  ];
  private const HANDLERS = [
    "image/png" => fn($filename) => imagecreatefrompng($filename),
    "image/jpeg" => fn($filename) => imagecreatefromjpeg($filename),
    "image/webp" => fn($filename) => imagecreatefromwebp($filename),
  ];

  public function __construct(string $filename, string $mime)
  {
    if (!isset(self::HANDLERS[$mime])) {
      throw new UnsupportedMIMEException("unsupported mimetype: $mime");
    }
    $this->image = self::HANDLERS[$mime]($filename);
    $this->sizes["width"] = imagesx($this->image);
    $this->sizes["height"] = imagesy($this->image);
  }

  // создаем на основе original изображения thumbnail GdImage
  public function createThumbnail(): GdImage
  {
    // NOTE 300px hard coded значение - пока так задумано под сайт
    return $this->resizeImage(
      saveProportions: false,
      newWidth: 300,
      newHeight: 300
    );
  }

  // создаем watermark на исходном изображении
  // потому что иначе пришлось бы делать копию изображения, дорого
  public function putWatermark(): bool
  {
    $watermark = new ImageHandler(
      "./data/template/watermark.png",
      "image/png"
    );
    // масштабирую водяной знак до 10% от оригинального изображения
    $wm_w = intval($this->sizes["width"] * 0.1);
    $scale = $wm_w / $watermark->sizes["width"];
    $wm_h = intval($watermark->sizes["height"] * $scale);

    $watermark->image = $watermark->resizeImage(
      saveProportions: true,
      newWidth: $wm_w,
      newHeight: $wm_h
    );
    // обновляю информацию о размерах
    $watermark->sizes["width"] = imagesx($watermark->image);
    $watermark->sizes["height"] = imagesy($watermark->image);

    // позиция watermark на оригинальном image
    // справа снизу
    $pos_x = $this->sizes["width"] - $wm_w - 15;
    $pos_y = $this->sizes["height"] - $wm_h - 15;

    // прозрачность включаем
    imagesavealpha($this->image, true);
    // копирую watermark на orig
    $res = imagecopy(
      $this->image,
      $watermark->image,
      $pos_x,
      $pos_y,
      0,
      0,
      $wm_w,
      $wm_h
    );

    return $res;
  }

  // выполнить ресайз изображения, либо центрируем либо просто ресайз с
  // сохранением пропорций
  public function resizeImage(
    bool $saveProportions,
    int $newWidth,
    int $newHeight
  ): GdImage {
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    // сохраняю прозрачность
    imagesavealpha($newImage, true);

    if (!$saveProportions) {
      $side = min($this->sizes["width"], $this->sizes["height"]); // нахожу наименьшую сторону изобр.
      $x = intval(($this->sizes["width"] - $side) / 2);
      $y = intval(($this->sizes["height"] - $side) / 2);
    } else {
      $x = 0;
      $y = 0;
    }

    imagecopyresampled(
      $newImage,
      $this->image,
      0,
      0,
      $x,
      $y,
      $newWidth,
      $newHeight,
      $saveProportions ? $this->sizes["width"] : $side,
      $saveProportions ? $this->sizes["height"] : $side,
    );

    return $newImage;
  }

  // получить оригинальную картинку
  public function getImage(): GdImage
  {
    return $this->image;
  }

  public function __destruct()
  {
    if (is_resource($this->image)) {
      imagedestroy($this->image);
    }
  }
}
