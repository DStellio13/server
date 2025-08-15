<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/config.php'; // doit exposer function db(): PDO
$pdo = db();

function out($ok, $data = [], $code = 200) {
  http_response_code($ok ? $code : ($code >= 400 ? $code : 400));
  echo json_encode(['ok'=>$ok,'data'=>$data], JSON_UNESCAPED_UNICODE);
  exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, ['error'=>'POST only'], 405);

$in = json_decode(file_get_contents('php://input'), true) ?? [];
$act = $in['action'] ?? '';

try {
  switch ($act) {
    case 'add_project': {
      $name = trim((string)($in['name'] ?? ''));
      $slug = strtolower(trim((string)($in['slug'] ?? '')));
      if ($name === '') out(false, ['error'=>'name requis']);
      if ($slug === '') $slug = preg_replace('~[^a-z0-9_-]+~','-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name));

      $stmt = $pdo->prepare("
        INSERT INTO projects (slug, name, emoji, description, icon_path, entry_path, order_index, is_active, is_initialized)
        VALUES (?, ?, '', '', NULL, NULL, 0, 1, 0)
        ON DUPLICATE KEY UPDATE name=VALUES(name), is_active=1
      ");
      $stmt->execute([$slug, $name]);
      out(true, ['slug'=>$slug, 'name'=>$name]);
    }

    case 'add_task': {
      $title = trim((string)($in['title'] ?? ''));
      $project = trim((string)($in['project_slug'] ?? ''));
      if ($title === '' || $project === '') out(false, ['error'=>'title & project_slug requis']);
      $status = in_array(($in['status'] ?? 'todo'), ['todo','in_progress','done','blocked'], true) ? $in['status'] : 'todo';
      $priority = (int)($in['priority'] ?? 3);

      $stmt = $pdo->prepare("INSERT INTO tasks (project_slug, title, status, priority, order_index, is_archived) VALUES (?, ?, ?, ?, 0, 0)");
      $stmt->execute([$project, $title, $status, $priority]);
      out(true, ['id'=>(int)$pdo->lastInsertId(), 'project_slug'=>$project, 'status'=>$status, 'priority'=>$priority]);
    }

    case 'add_item': {
      $taskId = (int)($in['task_id'] ?? 0);
      $title  = trim((string)($in['title'] ?? ''));
      if ($taskId <= 0 || $title === '') out(false, ['error'=>'task_id & title requis']);
      // nécessite table task_items si tu veux des sous‑tâches
      $pdo->prepare("CREATE TABLE IF NOT EXISTS task_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        is_done TINYINT(1) NOT NULL DEFAULT 0,
        order_index INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (task_id),
        CONSTRAINT fk_task_items_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
      ) ENGINE=InnoDB")->execute();

      $stmt = $pdo->prepare("INSERT INTO task_items (task_id, title, is_done, order_index) VALUES (?, ?, 0, 0)");
      $stmt->execute([$taskId, $title]);
      out(true, ['id'=>(int)$pdo->lastInsertId(), 'task_id'=>$taskId]);
    }

    default: out(false, ['error'=>'action inconnue']);
  }
} catch (Throwable $e) {
  out(false, ['error'=>$e->getMessage()], 500);
}
