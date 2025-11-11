<?php
class Request
{
  public static string $uri = "";
  public static string $method = "";
  public static array $data = [];

  public function __construct()
  {
    self::$uri = rtrim($_SERVER["REQUEST_URI"], "/");
    self::$method = $_SERVER["REQUEST_METHOD"];
    self::$data = $_POST;
  }
}
