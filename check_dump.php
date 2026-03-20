<?php
$file = 'C:\\Users\\iperez\\Desktop\\appDoc\\cambialo_rd (5).sql';
$handle = fopen($file, 'r');
$lines = 0;
$charsetLines = [];
while (($line = fgets($handle)) !== false && $lines < 50) {
    $lines++;
    echo $line;
    if (stripos($line, 'charset') !== false || stripos($line, 'character') !== false || stripos($line, 'SET NAMES') !== false || stripos($line, 'collat') !== false) {
        $charsetLines[] = trim($line);
    }
}
fclose($handle);
echo "\n\n--- CHARSET LINES FOUND ---\n";
foreach ($charsetLines as $l) echo $l . "\n";
