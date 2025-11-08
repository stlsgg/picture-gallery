<?php
// класс по парсингу и валидации URI по отношению к API
class InvalidURIException extends Exception {};

class Request
{
  private string $uri = "";

  public function __construct(string $uri)
  {
    $uri = rtrim($uri, "/");
    $this->validate($uri);
    $this->uri = $uri;
  }

  // проверка валидности переданного uri
  private function validate(string $uri)
  {
    if (!preg_match("#^/api/images(/(\d+(/\w+)?)?)?$#", $uri)) {
      throw new InvalidURIException("invalid uri given: $uri");
    }
  }

  // получить id
  public function getId(): ?string
  {
    $m = [];
    $pattern = "#^/api/images/(?P<id>\d+)(/|$)#";
    if (preg_match($pattern, $this->uri, $m)) {
      return $m['id'] ?? null;
    }
    return null;
  }

  // получить field
  public function getField(): ?string
  {
    $m = [];
    $pattern = "#^/api/images/\d+/(?P<field>\w+)(/|$)#";
    if (preg_match($pattern, $this->uri, $m)) {
      return $m['field'] ?? null;
    }
    return null;
  }
}
