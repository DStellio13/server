<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/core/projects.php';

try {
  $pdo = projectDb('inventaire');
  $count = (int)$pdo->query('SELECT COUNT(*) FROM items')->fetchColumn();
  echo json_encode(['ok'=>true, 'rows'=>$count]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
}