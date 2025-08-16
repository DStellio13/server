<?php
// Exemple versionné — NE PAS METTRE DE VRAIS SECRETS ICI.
return [
  'db' => [
    'dsn'  => 'mysql:host=127.0.0.1;port=3306;dbname=inventaire;charset=utf8mb4',
    'user' => 'root',
    'pass' => 'root', // valeur de démonstration
    'options' => [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
  ],
];