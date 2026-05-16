$filePath = 'c:\Users\iperez\source\repos\copi\CB.app\app\Http\Controllers\ItemController.php'
$content = [System.IO.File]::ReadAllText($filePath, [System.Text.Encoding]::UTF8)

# Replace all $item->imagenes in update() and talentoupdate() areas
$content = $content -replace 'foreach \(\$item->imagenes as \$imgVieja\)', 'foreach ($item->todasLasImagenes as $imgVieja)'
$content = $content -replace '\$imagenesActuales = \$item->imagenes\(\)->where', '$imagenesActuales = $item->todasLasImagenes()->where'

[System.IO.File]::WriteAllText($filePath, $content, [System.Text.Encoding]::UTF8)
Write-Host "Done"
