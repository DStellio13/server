<?php
return [
  "db_host" => "127.0.0.1",
  "db_name" => "NOM_BDD_GLOBAL",
  "db_user" => "UTILISATEUR",
  "db_pass" => "MOT_DE_PASSE",
  "options" => [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ],
];
