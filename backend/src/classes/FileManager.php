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
    return mkdir($path, 0644, recursive: true);
  }

  // сохранение картинки по заданному пути
  public static function saveImage(
    GdImage $image,
    string $path,
    string $mime
  ): bool {
    $save = self::HANDLERS[$mime];
    if (!file_exists($path)) {
      FileManager::mkdir(dirname($path));
    }
    $save($image, $path);
    return true;
  }

  // создание файла
  // обертка над touch
  public static function touch(string $filename): bool
  {
    return touch($filename);
  }

  /**
   * Generate string with len 10 safe for file naming
   * @param string $filename full path to file
   * @return string|bool $generatedString generated string or false on failure
   */
  public static function hash(string $filename): string|bool
  {
    if (!is_readable($filename)) return false;

    $full = hash_file("sha256", $filename);
    if ($full === false) return false;
    return substr($full, 0, 10);
  }
}
