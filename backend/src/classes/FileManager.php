<?php

/**
 * Класс по управлению физическими данными сервера.
 *
 * Создание директорий, физического файла базы данных, а также вспомогательные
 * функции по работе с файловой системой.
 */
class FileManager
{
  private const HANDLERS = [
    "image/png" => "imagepng",
    "image/jpeg" => "imagejpeg",
    "image/webp" => "imagewebp",
  ];

  /**
   * Рекурсивное создание директории.
   *
   * Обертка над обычной функцией mkdir.
   *
   * @param string $path путь (относительный или абсолютный) до директории.
   * @return bool $result результат выполнения команды mkdir, true при успехе,
   * иначе false.
   */
  public static function mkdir(string $path): bool
  {
    return mkdir($path, 0644, recursive: true);
  }

  /**
   * Сохранение картинки по заданному пути.
   *
   * @param GdImage $image изображение класса GdImage. Если директории не
   * существует, метод создаст необходимые директории для сохранения файла.
   * @param string $path путь, по которому будет сохранена картинка.
   * @param string $mime mimetype сохраняемого файла. Параметр необходим для
   * выбора функции из библиотеки gd, в зависимости от mime.
   * @return bool $result результат выполнения команды - true при успехе, иначе
   * false.
   */
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

  /**
   * Создание файла по заданному пути.
  /**
   * Создание пустого файла по переданному пути.
   *
   * Обертка над обычной функцией touch.
   *
   * @param string $filename путь (относительный или абсолютный) файла.
   * @return ?bool $result результат выполнения команды copy, true при успехе;
   * В случае ошибки выбрасывается ошибка, сообщающая о провале операции.
   */
  public static function touch(string $filename): ?bool
  {
    return touch($filename);
  }

  /**
   * Генерация строки длиной 10 символов.
   *
   * Генерируется строка, содержащие символы латинского алфавита и цифры от 0 до
   * 9, на основе содержимого передаваемого файла.
   *
   * @param string $filename путь до файла, по которому генерируется строка.
   * @return string|bool $generatedString сгенерированная строка при успехе
   * операции, иначе false.
   */
  public static function hash(string $filename): string|bool
  {
    if (!is_readable($filename)) return false;

    $full = hash_file("sha256", $filename);
    if ($full === false) return false;
    return substr($full, 0, 10);
  }
}
