<?php
// класс для выдачи ответов по api каналу
class Response
{
  // success response
  public static function ok(array $data)
  {
    header("Content-Type: application/json");
    echo json_encode(["status" => "ok", "data" => $data]);
    exit;
  }

  // error respose
  public static function error(int $code, string $message)
  {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "description" => $message]);
    exit;
  }
}
