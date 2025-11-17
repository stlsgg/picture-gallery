<?php
// класс для выдачи ответов по api каналу
class Response
{
  /**
   * Send success response to the client
   *
   * Sets the HTTP status code and, if data is provided,
   * returns it as JSON.
   *
   * @param int $code http status code
   * @param array|string|null $data optional data to sent in the response body
   * @return void closes connection between server and client
   */
  public static function ok(int $code, array|string|null $data = null)
  {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode(["status" => "ok", "data" => $data]);
    exit;
  }

  /**
   * Send error response to the client
   * @param int $code http error status code
   * @param string $message error description
   * @return void closes connection between server and client
   */
  public static function error(int $code, string $message)
  {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "description" => $message]);
    exit;
  }
}
