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
  public string $fext;

  public string $mimetype;

  private const array ALLOWED_MIMES = [
    "image/png",
    "image/jpeg",
    "image/webp"
  ];

  public function __construct()
  {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fext = finfo_open(FILEINFO_EXTENSION);
    $file = $_FILES['image'];
    $this->tmp = $file['tmp_name'];

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
    $this->size = $file['size'];
    $this->fext = finfo_file($fext, $this->tmp);

    // free memory
    finfo_close($fext);
    finfo_close($finfo);
  }
}
