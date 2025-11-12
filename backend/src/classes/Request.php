<?php
class Request
{
  public static function uri() {
    return rtrim($_SERVER["REQUEST_URI"], "/");
  }

  public static function method() {
    return $_SERVER["REQUEST_METHOD"];
  }

  public static function data() {
    return $_POST;
  }
}
