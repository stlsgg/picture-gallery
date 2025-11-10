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

  /**
   * Generate string with len 10 safe for file naming
   * @param string $filename full path to file
   * @return string|bool $generatedString generated string or false on failure
   */
  public static function hash(string $filename): string|bool
  {
    $hash = hash_init("sha256");
    $handle = fopen($filename, "rb");
    if ($handle === false) {
      return false;
    }
    $chunkSize = 8192; // 8 KB
    while (!feof($handle)) {
      $chunk = fread($handle, $chunkSize);
      hash_update($hash, $chunk);
    }
    fclose($handle);
    return substr(hash_final($hash), 0, 10);
  }
}
