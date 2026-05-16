$filePath = 'c:\Users\iperez\source\repos\copi\CB.app\app\Http\Controllers\ItemController.php'
$lines = [System.IO.File]::ReadAllLines($filePath, [System.Text.Encoding]::UTF8)
$count = 0

# Fix 1: destroy() - use todasLasImagenes so ALL images (incl. pending/rejected) are deleted
# Line ~818: foreach ($item->imagenes as $imagen) {
for ($i = 0; $i -lt $lines.Length; $i++) {
    if ($lines[$i] -match 'public function destroy') {
        # look ahead for the imagenes reference
        for ($j = $i; $j -lt [Math]::Min($i + 15, $lines.Length); $j++) {
            if ($lines[$j] -match 'foreach \(\$item->imagenes as \$imagen\)') {
                $lines[$j] = $lines[$j] -replace 'foreach \(\$item->imagenes as \$imagen\)', 'foreach ($item->todasLasImagenes as $imagen)'
                $count++
                break
            }
        }
        break
    }
}

# Fix 2: update() - max orden uses todasLasImagenes()
# Line ~1660: $maxOrden = $item->imagenes()->max(...)
for ($i = 0; $i -lt $lines.Length; $i++) {
    if ($lines[$i] -match '\$maxOrden = \$item->imagenes\(\)->max\(') {
        $lines[$i] = $lines[$i] -replace '\$maxOrden = \$item->imagenes\(\)->max\(', '$maxOrden = $item->todasLasImagenes()->max('
        $count++
    }
}

Write-Host "Replaced $count occurrence(s)"
[System.IO.File]::WriteAllLines($filePath, $lines, [System.Text.Encoding]::UTF8)
Write-Host "Done"
