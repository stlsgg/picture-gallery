<?php
header("Content-Type: text/plain");
$uri = $_SERVER["REQUEST_URI"];
$uri = rtrim($uri, " /");
echo "$uri\n\n";

$pattern = "/\/api\/(?'scope'images)?\/?(?'id'\d+)?\/?(?'field'\w+)?/";
$matches = [];
preg_match($pattern, $uri, $matches);

// пробую meta на вкус
$meta = json_decode(file_get_contents('../data/meta.json'), true);
$id = $matches['id'] ?? null;

if ($id) {
  var_dump($meta[$id]);
} else if (!$id) {
  var_dump($meta);
}
