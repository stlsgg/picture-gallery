<?php
// класс по парсингу и валидации URI по отношению к API
class InvalidURIException extends Exception {};

class Request
{
  private string $uri = "";
  private string $method = "";

  public function __construct(string $uri)
  {
    $uri = rtrim($uri, "/");
    $this->validate($uri);
    $this->uri = $uri;
    $this->method = $_SERVER['REQUEST_METHOD'];
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

  // проверка на наличие id в запросе
  public function hasId(): bool
  {
    $m = [];
    $pattern = "#^/api/images/(?P<id>\d+)(/|$)#";
    return preg_match($pattern, $this->uri, $m) ? true : false;
  }

  // проверка на наличие field в запросе
  public function hasField(): bool
  {
    $m = [];
    $pattern = "#^/api/images/\d+/(?P<field>\w+)(/|$)#";
    return preg_match($pattern, $this->uri, $m) ? true : false;
  }

  public function isCollection(): bool
  {
    return !$this->hasId();
  }

  public function isSingle(): bool
  {
    return $this->hasId() && !$this->hasField();
  }

  public function isField(): bool
  {
    return $this->hasId() && $this->hasField();
  }

  // получить метод запроса
  public function getMethod(): string {
    return $this->method;
  }
}
