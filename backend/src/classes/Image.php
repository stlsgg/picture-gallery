<?php
// класс, хранящий всю информацию о конкретном изображении
class FileUploadException extends Exception {}
class FileNotAllowedException extends Exception {}

class Image
{
  public string $name;
  public string $error;
  public string $tmp;
  public int $size;

  public string $mimetype;

  private const array ALLOWED_MIMES = [
    "image/png",
    "image/jpeg",
    "image/webp"
  ];

  public function __construct()
  {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file = $_FILES['image'];

    // проверка существования $_FILES['image']
    if (!isset($file) || empty($file)) {
      throw new FileNotFoundException(
        "file not found in \$_FILES['image'] field"
      );
    }

    // проверка на ошибку в $_FILES
    $this->error = $file['error'];
    if ($this->error) {
      throw new FileUploadException(
        400,
        "upload error with status code $this->error"
      );
    }

    // проверка допустимости mimetype
    $this->mimetype = finfo_file($finfo, $this->tmp);
    if (!in_array($this->mimetype, self::ALLOWED_MIMES)) {
      throw new FileNotAllowedException(406, "file type not allowed");
    }

    // read info about file
    $this->name = $file['name'];
    $this->tmp = $file['tmp_name'];
    $this->size = $file['size'];
  }
}
