<?php

$logPath = "C:\\Users\\iperez\\.gemini\\antigravity\\brain\\e3e02076-ef5f-419c-9801-32e40eee62e3\\.system_generated\\logs\\transcript.jsonl";

if (file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -15);
    foreach ($lastLines as $line) {
        $data = json_decode($line, true);
        if (isset($data['type'])) {
            echo "Type: {$data['type']} | Status: " . ($data['status'] ?? 'N/A') . "\n";
            if (isset($data['content'])) {
                echo "Content snippet: " . substr(strip_tags($data['content']), 0, 150) . "...\n";
            }
            echo "--------------------------------------------------\n";
        }
    }
} else {
    echo "Log file not found.\n";
}
