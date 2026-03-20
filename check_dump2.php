<?php
$file = 'C:\\Users\\iperez\\Desktop\\appDoc\\cambialo_rd (5).sql';
$content = file_get_contents($file);

// Search for "alba" near the corrupted word
if (preg_match_all('/alba.{0,30}/u', $content, $matches)) {
    foreach ($matches[0] as $m) {
        echo "Found: " . $m . " | hex: " . bin2hex($m) . "\n";
    }
}

// Also search for common Spanish words with accents
foreach (['jardín', 'jardÃ­n', 'jard??n', 'albañil', 'albaÃ±il', 'alba??il'] as $word) {
    $pos = strpos($content, $word);
    echo "$word: " . ($pos !== false ? "FOUND at $pos" : "not found") . "\n";
}
