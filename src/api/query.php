<?php
// TODO ответ всегда в json
header("Content-Type: application/json");
/* header("Content-Type: text/plain"); */
$uri = $_SERVER["REQUEST_URI"];
$uri = rtrim($uri, " /");

// проверка uri
// 1. что запрос начинается с /api/images
if (!preg_match("/^\/api\/images(\/|$)/", $uri)) {
  echo json_encode(["status" => "error", "description" => "отсутствие /api/images в запросе"]);
  exit;
}
// 2. извлечение возможных полей через regex
$pattern = "/\/api\/images\/?(?'id'\d+)?\/?(?'field'\w+)?/";
$matches = [];
preg_match($pattern, $uri, $matches);
$id = $matches['id'] ?? null;
$field = ($id) ? $matches['field'] ?? null : null;

// TODO проверка uri на валидность
// всегда начинается с /api/images
// может иметь id поле
// может иметь field поле, но только если есть id
// id указывается в рамках существующего пула в meta
// field должен быть валиден (что есть в meta по конкр. id)

// пробую meta на вкус
// TODO проверка существования meta.json
if (!file_exists("../data/meta.json")) {
  // TODO проверка на наличие хотя бы одной картинки в meta
  echo json_encode(["status" => "ok", "data" => []]);
  exit;
} else {
  // TODO проверка api uri запроса: есть ли такой id, есть ли такой field
  $meta = json_decode(file_get_contents('../data/meta.json'), true);
  $id_in_meta = $meta[$id] ?? null;
  if ($id && !$id_in_meta) {
    echo json_encode(["status" => "error", "description" => "id not found"]);
    exit;
  } else if ($id && $id_in_meta) {
    $field_in_meta = $meta[$id][$field] ?? null;
    if ($field && !$field_in_meta) {
      echo json_encode(["status" => "error", "description" => "field not found by given id"]);
      exit;
    } else if (!$field) {
      echo json_encode(["status" => "ok", "data" => $meta[$id]]);
      exit;
    } else if ($field && $field_in_meta) {
      echo json_encode(["status" => "ok", "data" => $meta[$id][$field]]);
      exit;
    }
  } else if (!$id) {
    echo json_encode(["status" => "ok", "data" => $meta]);
    exit;
  }
}
