<?php
// inventaire/api/items.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
  require_once __DIR__ . '/../../includes/core/projects.php'; // charge helpers centraux
  $pdo = projectDb('inventaire'); // utilise la config centrale (README) 

  $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

  if ($method === 'GET') {
    // Filtres
    $q     = trim($_GET['q'] ?? '');
    $cat   = trim($_GET['category'] ?? '');
    $sort  = $_GET['sort'] ?? 'name.asc';

    // Pagination facultative, par défaut on ramène large (500) pour être tranquille
    $limit = isset($_GET['limit']) ? max(1, min((int)$_GET['limit'], 1000)) : 500;
    $page  = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;

    $sql = "SELECT id, name, category, product_code, version, notes, acquired_at, location, is_functional, created_at, photo
            FROM items WHERE 1";
    $args = [];

    if ($q !== '') {
      $sql .= " AND (name LIKE :q OR product_code LIKE :q OR version LIKE :q OR notes LIKE :q)";
      $args[':q'] = "%{$q}%";
    }
    if ($cat !== '') {
      $sql .= " AND category = :cat";
      $args[':cat'] = $cat;
    }

    $order = match ($sort) {
      'name.asc'         => 'name ASC',
      'name.desc'        => 'name DESC',
      'created_at.asc'   => 'created_at ASC',
      'created_at.desc'  => 'created_at DESC',
      default            => 'name ASC',
    };
    $sql .= " ORDER BY {$order}";

    // Pas de LIMIT fixe côté SQL, mais on autorise limit/page pour gros volumes
    $sql .= " LIMIT {$limit} OFFSET {$offset}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    $rows = $stmt->fetchAll();

    echo json_encode(['ok'=>true, 'data'=>$rows, 'page'=>$page, 'limit'=>$limit]);
    exit;
  }

  // POST (insert/update) & DELETE en JSON
  $payload = json_decode(file_get_contents('php://input'), true) ?? [];
  if (!is_array($payload)) throw new RuntimeException('JSON invalide');

  if ($method === 'POST') {
    $id = isset($payload['id']) ? (int)$payload['id'] : 0;

    $fields = [
      'name','category','product_code','version','notes','acquired_at',
      'location','is_functional','photo'
    ];
    $data = [];
    foreach ($fields as $f) { $data[$f] = $payload[$f] ?? null; }
    if ($data['is_functional'] !== null) $data['is_functional'] = (int)$data['is_functional'];

    if ($id > 0) {
      // UPDATE
      $sql = "UPDATE items SET
              name=:name, category=:category, product_code=:product_code, version=:version,
              notes=:notes, acquired_at=:acquired_at, location=:location,
              is_functional=:is_functional, photo=:photo
              WHERE id=:id";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([...$data, ':id'=>$id]);
      echo json_encode(['ok'=>true, 'data'=>['id'=>$id]]);
      exit;
    } else {
      // INSERT
      $sql = "INSERT INTO items
              (name, category, product_code, version, notes, acquired_at, location, is_functional, created_at, photo)
              VALUES (:name, :category, :product_code, :version, :notes, :acquired_at, :location, :is_functional, NOW(), :photo)";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($data);
      echo json_encode(['ok'=>true, 'data'=>['id'=>(int)$pdo->lastInsertId()]]);
      exit;
    }
  }

  if ($method === 'DELETE') {
    $id = (int)($payload['id'] ?? 0);
    if ($id <= 0) throw new RuntimeException('id manquant');
    $pdo->prepare("DELETE FROM items WHERE id=?")->execute([$id]);
    echo json_encode(['ok'=>true]);
    exit;
  }

  http_response_code(405);
  echo json_encode(['ok'=>false, 'error'=>'Méthode non autorisée']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
}
