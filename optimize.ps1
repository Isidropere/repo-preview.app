# Laravel Optimization Script for Windows
Write-Host "🚀 Iniciando optimización de la aplicación..." -ForegroundColor Cyan

# 1. Limpiar y cachear configuración, rutas y vistas
Write-Host "📦 Cacheando configuración, rutas y vistas..."
php artisan optimize

# 2. Cachear iconos (si se usa blade-icons)
if (Test-Path "vendor/blade-ui-kit/blade-icons") {
    Write-Host "🎨 Cacheando iconos..."
    php artisan icons:cache
}

# 3. Compilar assets para producción
if (Test-Path "package.json") {
    Write-Host "🛠️ Compilando assets (Vite)..."
    npm run build
}

# 4. Limpiar logs antiguos (opcional)
# Remove-Item storage/logs/*.log

Write-Host "✅ ¡Aplicación optimizada!" -ForegroundColor Green
Write-Host "Recuerda que si realizas cambios en .env o rutas, debes volver a correr este script." -ForegroundColor Yellow
