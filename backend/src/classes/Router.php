<?php
require_once __DIR__ . "/Response.php";

/**
 * Class Router представляет создание, обработку клиентских запросов по
 * переданному URL.
 *
 * @author Gregory Guzey
 * @version 1.0.0
 */
class Router
{
  private array $routes = [];

  /**
   * Привязка функции к определенному URL, используя маску.
   *
   * @param string $method - тип запроса к ресурсу, например get
   * @param string $mask - маска запроса, по которой Роутер проверяет совпадения.
   *   Маска может содержать следующие паттерны: {<fieldName>[:тип параметра]}
   *   Пример: /images/{id:int}, /site/{name:str}, /site/{domain}. Допустимые
   *   типы параметров: str, int. По умолчанию, если не указан тип параметра,
   *   поле считается типом str.. Допустимые типы параметров: str, int. По
   *   умолчанию, если не указан тип параметра, поле считается типом str.
   * @param callable $cb - коллбэк функция, которая вызывается в случае совпадения
   * маски с клиентским запросом
   *
   * @return void
   *
   * @example
   * $router->on("get", "/users/{id}/{field}", function($id, $field) => {
   *    echo "user id: $id\nuser field: $field";
   * });
   */
  public function on(string $method, string $mask, callable $cb): void
  {
    $pattern = preg_replace_callback(
      "#\{(\w+)(?::(\w+))?\}#",
      function ($m) {
        $name = $m[1];
        $type = $m[2] ?? 'str';
        return match ($type) {
          'int' => "(?P<$name>\d+)",
          'str' => "(?P<$name>\w+)",
          default => "(?P<$name>[^/]+)",
        };
      },
      $mask
    );
    $pattern = "#^$pattern$#";
    $this->routes[] = compact('method', 'pattern', 'cb');
  }

  /**
   * Инициализация прослушивания.
   *
   * Функция парсит URL запроса к серверу от клиента, ищет совпадение в списке
   * существующих масок и вызывает callback в случае совпадения. В противном
   * случае listen() вернет метод класса Response с статусом 404 и описанием
   * ошибки "not found", что означает отсутсвие api handler за запрошенному
   * ресурсу.
   *
   * @return void
   */
  public function listen(): void
  {
    $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $method = strtolower($_SERVER["REQUEST_METHOD"]);

    foreach ($this->routes as $route) {
      if ($method !== $route['method']) continue;
      if (preg_match($route['pattern'], $url, $matches)) {
        $args = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        echo $route['cb'](...$args);
        return;
      }
    }

    Response::error(404, "not found");
  }
}
