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
   * @return ?bool $result результат выполнения команды mkdir, true при успехе;
   * В случае ошибки выбрасывается ошибка, сообщающая о провале операции.
   */
  public static function mkdir(string $path): ?bool
  {
    if (!mkdir($path, 0644, recursive: true)) {
      throw new Exception("Failed to create a directory by given path: $path");
    }
    return true;
  }

  /**
   * Удаление файла по переданному пути.
   *
   * Обертка над обычной функцией unlink.
   *
   * @param string $path путь (относительный или абсолютный) до файла.
   * @return ?bool $result результат выполнения команды unlink, true при успехе;
   * В случае ошибки выбрасывается ошибка, сообщающая о провале операции.
   */
  public static function delete(string $path): ?bool
  {
    if (!unlink($path)) {
      throw new Exception("Failed delete file: $path");
    }
    return true;
  }

  /**
   * Копирование файла по переданному пути.
   *
   * Обертка над обычной функцией copy.
   *
   * @param string $srcPath путь (относительный или абсолютный) исходного файла.
   * @param string $dstPath путь (относительный или абсолютный) до файла
   * назначения.
   * @return ?bool $result результат выполнения команды copy, true при успехе;
   * В случае ошибки выбрасывается ошибка, сообщающая о провале операции.
   */
  public static function copy(string $srcPath, string $dstPath): ?bool
  {
    if (!copy($srcPath, $dstPath)) {
      throw new Exception("Failed copy file: $srcPath to location $dstPath");
    }
    return true;
  }

  /**
   * Перемещение файла по переданному пути.
   *
   * Метод состоит из методов copy & delete класса FileManager.
   *
   * @param string $srcPath путь (относительный или абсолютный) исходного файла.
   * @param string $dstPath путь (относительный или абсолютный) до файла
   * назначения.
   * @return ?bool $result результат выполнения, true при успехе;
   * В случае ошибки выбрасывается ошибка, сообщающая о провале операции на
   * определенном этапе.
   */
  public static function move(string $srcPath, string $dstPath): ?bool
  {
    FileManager::copy($srcPath, $dstPath);
    FileManager::delete($srcPath);
    return true;
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
   * Сохранение переданных данных по заданному пути.
   *
   * LOCK_EX в теории должен защищать от гонок записи/чтения.
   *
   * @param string $path - путь до файла, куда будет сохранятся база данных.
   * В случае отсутствия директории, директория будет создана.
   * @param array $data - массив записываемых данных.
   * @return ?bool $result результат выполнения: true при успехе, иначе выброс
   * ошибки.
   */
  public static function saveDataBase(
    string $path,
    array $data
  ): ?bool {
    $directory = dirname($path);
    if (!file_exists($directory)) {
      FileManager::mkdir($directory);
    }
    $jsonData = json_encode($data);
    if(json_last_error_msg() !== JSON_ERROR_NONE) {
      throw new Exception("Failed to encode data to JSON format. Data: $data");
    }
    if (!file_put_contents($path, $jsonData, LOCK_EX)) {
      throw new Exception("Failed to write data to DataBase. Path: $path\nData: $data");
    };
    return true;
  }

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
    if (!touch($filename)) {
      throw new Exception("Failed to create file at specified location: $filename");
    }
    return true;
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
