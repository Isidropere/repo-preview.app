<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=cambialo_rd", "root", "");

// Check DB charset
$r = $pdo->query("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = 'cambialo_rd'");
echo "DB: " . json_encode($r->fetch(PDO::FETCH_ASSOC)) . "\n";

// Check items table charset
$r = $pdo->query("SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA='cambialo_rd' AND TABLE_NAME='items'");
echo "items table: " . json_encode($r->fetch(PDO::FETCH_ASSOC)) . "\n";

// Check a sample value with accents
$r = $pdo->query("SELECT id_item, item FROM items WHERE item LIKE '%?%' LIMIT 3");
$rows = $r->fetchAll(PDO::FETCH_ASSOC);
echo "Rows with '?': " . json_encode($rows, JSON_UNESCAPED_UNICODE) . "\n";

// Check connection charset
$r = $pdo->query("SHOW VARIABLES LIKE 'character_set%'");
echo "Connection vars:\n";
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo "  " . $row['Variable_name'] . " = " . $row['Value'] . "\n";
}

// Try reading with latin1 to see if data is actually latin1
$pdo2 = new PDO("mysql:host=127.0.0.1;dbname=cambialo_rd;charset=latin1", "root", "");
$r2 = $pdo2->query("SELECT id_item, item FROM items WHERE item LIKE '%í%' OR item LIKE '%ó%' OR item LIKE '%ñ%' LIMIT 3");
$rows2 = $r2->fetchAll(PDO::FETCH_ASSOC);
echo "With latin1 connection: " . json_encode($rows2, JSON_UNESCAPED_UNICODE) . "\n";
