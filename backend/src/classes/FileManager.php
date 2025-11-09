<?php
// класс по управлению физическими данными:
// создание, удаление файлов и директорий
// перенос данных из одного места в другое (для tmp картинки самое оно)
class FileManager
{
  private const HANDLERS = [
    "image/png" => "imagepng",
    "image/jpeg" => "imagejpeg",
    "image/webp" => "imagewebp",
  ];

  // создание директории
  // обертка над обычным mkdir
  public static function mkdir(string $path): bool
  {
    return mkdir($path, 0755, recursive: true);
  }

  // сохранение картинки по заданному пути
  public static function saveImage(
    GdImage $image,
    string $path,
    string $mime
  ): bool {
    $save = self::HANDLERS[$mime];
    $save($image, $path);
    return true;
  }

  // создание файла
  // обертка над touch
  public static function touch(string $filename): bool
  {
    return touch($filename);
  }
}
