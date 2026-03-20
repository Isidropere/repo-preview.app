<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=cambialo_rd;charset=utf8mb4", "root", "");
$r = $pdo->query("SELECT id_item, item FROM items WHERE item LIKE '%ñ%' OR item LIKE '%í%' OR item LIKE '%ó%' OR item LIKE '%é%' LIMIT 5");
$rows = $r->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";

// Also check for corrupted data
$r2 = $pdo->query("SELECT id_item, item FROM items WHERE item LIKE '%??%' LIMIT 3");
$bad = $r2->fetchAll(PDO::FETCH_ASSOC);
echo "Corrupted rows: " . count($bad) . "\n";
