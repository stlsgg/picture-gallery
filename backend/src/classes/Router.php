<?php
require_once __DIR__ . "/Response.php";

class Router
{
  private array $routes = [];

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
