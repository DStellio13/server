<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/core/config.php';phprequire_once('includes/config.php');if (!isset($_GET['id'])) {    die("ID manquant.");}$id = (int) $_GET['id'];$stmt = $pdo->prepare("DELETE FROM candidatures WHERE id = ?");$stmt->execute([$id]);header("Location: index.php");exit;