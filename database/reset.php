<?php

declare(strict_types= 1);
echo "ｺﾝﾆﾁﾜ!\n";

$root = dirname(__DIR__);
$dbPath = $root . '/storage/database.sqlite';
$schemaPath = __DIR__.'/schema.sql';
$seedPath = __DIR__.'/seed.sql';

$pdo = new PDO('sqlite:'.$dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

echo "DB: $dbPath\n";

$pdo->exec("drop table if exists todos;");
echo "Dropped table\n";

$schemaSql = file_get_contents($schemaPath);
if($schemaSql === false) {
    throw new RuntimeException("Failed to read $schemaPath");
}
$pdo ->exec($schemaSql);
echo "Applied schema.sql\n";