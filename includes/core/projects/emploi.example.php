<?php
return [
  "db_host" => "127.0.0.1",
  "db_name" => "emploi",
  "db_user" => "root",
  "db_pass" => "changeme",
  "options" => [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ],
];
