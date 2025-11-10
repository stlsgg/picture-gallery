<?php
class Request
{
  public static string $uri = "";
  public static string $method = "";
  public static array $data = [];

  public function __construct()
  {
    $this->uri = rtrim($_SERVER["REQUEST_URI"], "/");
    $this->method = $_SERVER['REQUEST_METHOD'];
    $this->data = $_POST;
  }
}
