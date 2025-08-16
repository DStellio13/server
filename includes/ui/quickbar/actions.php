<?php
// API Quickbar v3.1 — respecte le scope par page
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../core/config.php'; // db()

function respond(bool $ok, array $payload = [], int $http = 200): void {
  http_response_code($http);
  echo json_encode($payload + ['ok'=>$ok], JSON_UNESCAPED_UNICODE);
  exit;
}

$pdo = db();
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw ?: '[]', true) ?: [];

// CSRF (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tok = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
  if (!isset($_SESSION['qb_token']) || !hash_equals($_SESSION['qb_token'], (string)$tok)) {
    respond(false, ['message'=>'Token CSRF invalide','code'=>'CSRF'], 419);
  }
}

// Scope & verrou projet (par page)
$scope  = $_SESSION['qb_scope']  ?? 'full';            // 'full' | 'tasks-only'
$locked = $_SESSION['qb_project'] ?? null;
if ($scope !== 'tasks-only') {                          // EN MODE FULL : JAMAIS DE VERROU
  $locked = null;
}

// Autorisations par scope
$allowedByScope = [
  'full'       => null,  // tout autorisé
  'tasks-only' => ['list_tasks','add_task','update_task','delete_task'],
];
if (isset($allowedByScope[$scope]) && is_array($allowedByScope[$scope])) {
  if (!in_array($action, $allowedByScope[$scope], true)) {
    respond(false, ['message'=>'Action non autorisée dans ce contexte','code'=>'SCOPE'], 403);
  }
}

function table_exists(PDO $pdo, string $table): bool {
  try { $pdo->query("SELECT 1 FROM `$table` LIMIT 1"); return true; }
  catch (Throwable $e) { return false; }
}

function assert_task_in_locked(PDO $pdo, int $id, ?string $locked, string $scope): void {
  if ($scope !== 'tasks-only' || !$locked) return;    // uniquement en scope restreint
  $q = $pdo->prepare("SELECT project_slug FROM tasks WHERE id=?");
  $q->execute([$id]);
  $ps = $q->fetchColumn();
  if ($ps !== $locked) {
    respond(false, ['message'=>'Tâche hors du projet courant','code'=>'LOCK'], 403);
  }
}

switch ($action) {
  // -------- Projets (scope: full) ----------
  case 'list_projects':
    $rows = $pdo->query("SELECT slug,name,emoji,order_index,is_active,icon_path,description,entry_path FROM projects ORDER BY order_index, name")->fetchAll(PDO::FETCH_ASSOC);
    respond(true, ['rows'=>$rows]);

  case 'get_project':
    $slug = (string)($_GET['slug'] ?? '');
    $stmt = $pdo->prepare("SELECT slug,name,emoji,order_index,is_active,icon_path,description,entry_path FROM projects WHERE slug = ?");
    $stmt->execute([$slug]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$p) respond(false, ['message'=>'Projet introuvable'], 404);
    respond(true, ['project'=>$p]);

  case 'save_project': {
    $slug = trim((string)($input['slug'] ?? ''));
    $name = trim((string)($input['name'] ?? ''));
    if ($slug==='' || $name==='') respond(false, ['message'=>'Slug et Nom requis'], 400);
    $emoji = (string)($input['emoji'] ?? '');
    $order = (int)($input['order_index'] ?? 0);
    $active= (int)($input['is_active'] ?? 1);
    $icon  = (string)($input['icon_path'] ?? '');
    $desc  = (string)($input['description'] ?? '');
    $entry = (string)($input['entry_path'] ?? '');

    $pdo->beginTransaction();
    try {
      $exists = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE slug=?");
      $exists->execute([$slug]);
      if ((int)$exists->fetchColumn() > 0) {
        $q = $pdo->prepare("UPDATE projects SET name=?, emoji=?, order_index=?, is_active=?, icon_path=?, description=?, entry_path=? WHERE slug=?");
        $q->execute([$name,$emoji,$order,$active,$icon,$desc,$entry,$slug]);
        $pdo->commit();
        respond(true, ['message'=>'Projet mis à jour']);
      } else {
        $q = $pdo->prepare("INSERT INTO projects (slug,name,emoji,order_index,is_active,icon_path,description,entry_path) VALUES (?,?,?,?,?,?,?,?)");
        $q->execute([$slug,$name,$emoji,$order,$active,$icon,$desc,$entry]);
        $pdo->commit();
        respond(true, ['message'=>'Projet créé']);
      }
    } catch (Throwable $e) {
      $pdo->rollBack();
      respond(false, ['message'=>$e->getMessage()]);
    }
  }

  case 'archive_project': {
    $slug = trim((string)($input['slug'] ?? ''));
    if ($slug==='') respond(false, ['message'=>'Slug requis'], 400);
    $q = $pdo->prepare("UPDATE projects SET is_active=0 WHERE slug=?");
    $q->execute([$slug]);
    respond(true, ['message'=>'Projet archivé']);
  }

  // -------- Tâches ----------
  case 'list_tasks': {
    $slug = (string)($_GET['project'] ?? '');
    if ($scope === 'tasks-only' && $locked) $slug = $locked; // force uniquement en scope restreint
    if ($slug==='') respond(true, ['rows'=>[]]);
    $q = $pdo->prepare("SELECT id,title,status,priority,DATE_FORMAT(due_at, '%Y-%m-%d') as due_at FROM tasks WHERE project_slug=? AND is_archived=0 ORDER BY order_index ASC, priority ASC, id DESC LIMIT 200");
    $q->execute([$slug]);
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    respond(true, ['rows'=>$rows]);
  }

case 'add_task': {
  $slug = trim((string)($input['project_slug'] ?? ''));
  if ($scope === 'tasks-only' && $locked) $slug = $locked;

  $title= trim((string)($input['title'] ?? ''));
  if ($slug==='' || $title==='') respond(false, ['message'=>'Projet et titre requis'], 400);

  // Récupère l'id du projet
  $p = $pdo->prepare("SELECT id FROM projects WHERE slug=? LIMIT 1");
  $p->execute([$slug]);
  $projectId = $p->fetchColumn();
  if (!$projectId) respond(false, ['message'=>'Projet introuvable'], 404);

  $status   = (string)($input['status'] ?? 'todo');
  $priority = (int)($input['priority'] ?? 0);
  $due_at   = $input['due_at'] ?? null;

  $q = $pdo->prepare("
    INSERT INTO tasks (project_id, project_slug, title, status, priority, due_at, is_archived)
    VALUES (?,?,?,?,?,?,0)
  ");
  $q->execute([$projectId, $slug, $title, $status, $priority, $due_at]);

  respond(true, ['message'=>'Tâche ajoutée']);
}

  case 'update_task': {
    $id = (int)($input['id'] ?? 0);
    if ($id<=0) respond(false, ['message'=>'ID requis'], 400);
    assert_task_in_locked($pdo, $id, $locked, $scope);

    $title = (string)($input['title'] ?? null);
    $status= (string)($input['status'] ?? null);
    $priority = isset($input['priority']) ? (int)$input['priority'] : null;
    $due_at = array_key_exists('due_at',$input) ? ($input['due_at'] ?: null) : null;

    $fields=[]; $vals=[];
    if ($title!==null){ $fields[]='title=?'; $vals[]=$title; }
    if ($status!==null){ $fields[]='status=?'; $vals[]=$status; }
    if ($priority!==null){ $fields[]='priority=?'; $vals[]=$priority; }
    if (array_key_exists('due_at',$input)){ $fields[]='due_at=?'; $vals[]=$due_at; }
    if (!$fields) respond(false, ['message'=>'Aucune modification'], 400);
    $vals[]=$id;
    $q = $pdo->prepare("UPDATE tasks SET ".implode(',',$fields)." WHERE id=?");
    $q->execute($vals);
    respond(true, ['message'=>'Tâche mise à jour']);
  }

  case 'delete_task': {
    $id = (int)($input['id'] ?? 0);
    if ($id<=0) respond(false, ['message'=>'ID requis'], 400);
    assert_task_in_locked($pdo, $id, $locked, $scope);
    $q = $pdo->prepare("UPDATE tasks SET is_archived=1 WHERE id=?");
    $q->execute([$id]);
    respond(true, ['message'=>'Tâche supprimée']);
  }

  // -------- Items (scope: full) ----------
  case 'list_items': {
    $slug = (string)($_GET['project'] ?? '');
    if (!table_exists($pdo,'items')) respond(false, ['message'=>'Table items absente','code'=>'NO_TABLE']);
    $q = $pdo->prepare("SELECT id,name,qty,price,notes FROM items WHERE project_slug=? AND is_archived=0 ORDER BY id DESC LIMIT 200");
    $q->execute([$slug]);
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    respond(true, ['rows'=>$rows]);
  }

  case 'add_item': {
    if (!table_exists($pdo,'items')) respond(false, ['message'=>'Table items absente','code'=>'NO_TABLE']);
    $slug = trim((string)($input['project_slug'] ?? ''));
    $name = trim((string)($input['name'] ?? ''));
    if ($slug==='' || $name==='') respond(false, ['message'=>'Projet et nom requis'], 400);
    $qty  = (int)($input['qty'] ?? 1);
    $price= (float)($input['price'] ?? 0);
    $notes= (string)($input['notes'] ?? '');
    $q = $pdo->prepare("INSERT INTO items (project_slug,name,qty,price,notes,is_archived) VALUES (?,?,?,?,?,0)");
    $q->execute([$slug,$name,$qty,$price,$notes]);
    respond(true, ['message'=>'Item ajouté']);
  }

  case 'update_item': {
    if (!table_exists($pdo,'items')) respond(false, ['message'=>'Table items absente','code'=>'NO_TABLE']);
    $id = (int)($input['id'] ?? 0);
    if ($id<=0) respond(false, ['message'=>'ID requis'], 400);
    $name = (string)($input['name'] ?? null);
    $qty  = isset($input['qty']) ? (int)$input['qty'] : null;
    $price= isset($input['price']) ? (float)$input['price'] : null;
    $notes= (string)($input['notes'] ?? null);

    $fields=[]; $vals=[];
    if ($name!==null){ $fields[]='name=?'; $vals[]=$name; }
    if ($qty!==null){ $fields[]='qty=?'; $vals[]=$qty; }
    if ($price!==null){ $fields[]='price=?'; $vals[]=$price; }
    if ($notes!==null){ $fields[]='notes=?'; $vals[]=$notes; }
    if (!$fields) respond(false, ['message'=>'Aucune modification'], 400);

    $vals[]=$id;
    $q = $pdo->prepare("UPDATE items SET ".implode(',',$fields)." WHERE id=?");
    $q->execute($vals);
    respond(true, ['message'=>'Item mis à jour']);
  }

  case 'delete_item': {
    if (!table_exists($pdo,'items')) respond(false, ['message'=>'Table items absente','code'=>'NO_TABLE']);
    $id = (int)($input['id'] ?? 0);
    if ($id<=0) respond(false, ['message'=>'ID requis'], 400);
    $q = $pdo->prepare("UPDATE items SET is_archived=1 WHERE id=?");
    $q->execute([$id]);
    respond(true, ['message'=>'Item supprimé']);
  }

  default:
    respond(false, ['message'=>'Action inconnue'], 400);
}
