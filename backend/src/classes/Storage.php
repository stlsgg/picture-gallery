<?php
// exceptions
class FileReadException extends Exception {};
class FileNotFoundException extends Exception {};
class InvalidJSONException extends Exception {};

class Storage
{
  private array $db = [];
  private string $dbPath = "";

  // load db
  public function __construct(string $dbPath)
  {
    if (!file_exists($dbPath)) {
      throw new FileNotFoundException("db file not found: $dbPath");
    }

    $content = file_get_contents($dbPath);
    if (!$content) {
      throw new FileReadException("cannot read the database: $dbPath");
    }
    $data = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new InvalidJSONException("invalid JSON in $dbPath");
    }

    $this->db = $data ?? [];
    $this->dbPath = $dbPath;
  }

  // create new object and paste to the end of db
  public function create(array $object): bool
  {
    $this->validate($object);

    $key = (empty($this->db)) ? 1 : array_key_last($this->db) + 1;
    $this->db[$key] = [
      "desc" => $object['desc'],
      "full" => $object['full'],
      "thumb" => $object['thumb']
    ];

    return true;
  }

  // read object by given id
  public function readById(int|string $id): ?array
  {
    return $this->db[$id] ?? null;
  }

  // read objects in db
  public function readAll(): array
  {
    return $this->db;
  }

  // update object by given id and data
  public function update(int $id, array $object): bool
  {
    $this->validate($object);
    if (!isset($this->db[$id])) {
      return false;
    }
    $this->db[$id] = array_merge($this->db[$id], $object);
    return true;
  }

  // delete object
  public function delete(int|string $id): bool
  {
    if (!isset($this->db[$id])) {
      return false;
    }

    // TODO оценить сложность O алгоритма пересборки ключей
    unset($this->db[$id]);
    $this->db = array_values($this->db);
    $this->db = array_combine(range(1, count($this->db)), $this->db) ?: [];
    return true;
  }

  // save to disk
  public function save(): bool
  {
    $json = json_encode($this->db);
    if (!$json) {
      return false;
    }
    $result = file_put_contents($this->dbPath, $json, LOCK_EX);
    return $result !== false;
  }

  // validate object
  private function validate(array &$object): void
  {
    foreach (['desc', 'full', 'thumb'] as $field) {
      if (!array_key_exists($field, $object)) {
        throw new InvalidArgumentException("missing required field: $field");
      }
    }
  }
}
