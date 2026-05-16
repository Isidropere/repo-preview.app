$path = 'c:\Users\iperez\source\repos\copi\CB.app\app\Http\Controllers\ItemController.php'
$content = [System.IO.File]::ReadAllText($path)
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText($path, $content, $utf8NoBom)
Write-Host "Fixed"
