#!/usr/bin/env pwsh
# ─────────────────────────────────────────────────────────────────────────────
# start.ps1 — Script de démarrage du Portail UMMISCO
# Usage : .\start.ps1
# ─────────────────────────────────────────────────────────────────────────────

$ErrorActionPreference = "Stop"
$BASE = $PSScriptRoot

Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║         PORTAIL UMMISCO — Démarrage                  ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# ── Vérifier Docker ──────────────────────────────────────────────────────────
Write-Host "▶ Vérification de Docker..." -ForegroundColor Yellow
try {
    $dockerInfo = docker info --format "{{.ServerVersion}}" 2>&1
    if ($LASTEXITCODE -ne 0) { throw "Docker n'est pas démarré" }
    Write-Host "  ✅ Docker $dockerInfo" -ForegroundColor Green
} catch {
    Write-Host "  ❌ Docker Desktop n'est pas démarré. Lancez Docker Desktop et réessayez." -ForegroundColor Red
    exit 1
}

# ── Initialiser le projet Laravel si absent ──────────────────────────────────
if (-not (Test-Path "$BASE\app\vendor")) {
    Write-Host ""
    Write-Host "▶ Installation Laravel (première fois — ~3 min)..." -ForegroundColor Yellow

    docker run --rm `
        -v "${BASE}/app:/app" `
        -w /app `
        composer:2.7 install --no-interaction --prefer-dist --optimize-autoloader

    if ($LASTEXITCODE -ne 0) {
        Write-Host "  ❌ Echec de composer install" -ForegroundColor Red
        exit 1
    }
    Write-Host "  ✅ Dépendances installées" -ForegroundColor Green
}

# ── Générer APP_KEY si absent ─────────────────────────────────────────────────
$envFile = "$BASE\app\.env"
$envContent = Get-Content $envFile -Raw
if ($envContent -match 'APP_KEY=base64:PLACEHOLDER') {
    Write-Host ""
    Write-Host "▶ Génération de la clé d'application..." -ForegroundColor Yellow
    $key = docker run --rm `
        -v "${BASE}/app:/app" `
        -w /app `
        composer:2.7 php artisan key:generate --show 2>&1

    if ($key -match "base64:") {
        (Get-Content $envFile) -replace 'APP_KEY=.*', "APP_KEY=$key" | Set-Content $envFile
        Write-Host "  ✅ APP_KEY générée" -ForegroundColor Green
    }
}

# ── Démarrer les services Docker ─────────────────────────────────────────────
Write-Host ""
Write-Host "▶ Démarrage des services Docker..." -ForegroundColor Yellow
Set-Location $BASE
docker compose -f docker-compose.dev.yml up -d postgres redis

Write-Host "  ⏳ Attente de PostgreSQL (max 60s)..." -ForegroundColor Yellow
$attempts = 0
do {
    Start-Sleep 3
    $attempts++
    $health = docker inspect --format='{{.State.Health.Status}}' ummisco_postgres 2>&1
} while ($health -ne "healthy" -and $attempts -lt 20)

if ($health -ne "healthy") {
    Write-Host "  ❌ PostgreSQL n'a pas démarré correctement" -ForegroundColor Red
    docker compose -f docker-compose.dev.yml logs postgres | Select-Object -Last 20
    exit 1
}
Write-Host "  ✅ PostgreSQL prêt" -ForegroundColor Green

# ── Démarrer tous les autres services ────────────────────────────────────────
docker compose -f docker-compose.dev.yml up -d
Write-Host "  ✅ Tous les services démarrés" -ForegroundColor Green

# ── Vérifier si la BDD est initialisée ───────────────────────────────────────
Write-Host ""
Write-Host "▶ Vérification de la base de données..." -ForegroundColor Yellow
$tableCount = docker exec ummisco_postgres psql -U ummisco_user -d ummisco_app `
    -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public';" 2>&1

if ($tableCount -match "^\s*0\s*$" -or $tableCount -notmatch "\d+") {
    Write-Host "  ℹ️  Base de données vide — le schéma sera importé via docker-compose init" -ForegroundColor Blue
} else {
    $count = ($tableCount -replace '\s', '')
    Write-Host "  ✅ Base de données initialisée ($count tables)" -ForegroundColor Green
}

# ── Afficher les URLs ─────────────────────────────────────────────────────────
Write-Host ""
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  🌐 Portail public   : http://localhost:8080" -ForegroundColor White
Write-Host "  🗄️  MinIO Console    : http://localhost:9001" -ForegroundColor White
Write-Host "       Login: minio_admin / minio_secret_2024" -ForegroundColor Gray
Write-Host "  🤖 Ollama API       : http://localhost:11434" -ForegroundColor White
Write-Host "  🐘 PostgreSQL       : localhost:5432 / ummisco_app" -ForegroundColor White
Write-Host "  📦 Redis            : localhost:6379" -ForegroundColor White
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "📌 Commandes utiles :" -ForegroundColor Yellow
Write-Host "  docker compose logs -f app          # Logs Laravel" -ForegroundColor Gray
Write-Host "  docker compose exec app php artisan migrate   # Migrations" -ForegroundColor Gray
Write-Host "  docker compose exec app php artisan db:seed   # Seeders" -ForegroundColor Gray
Write-Host "  docker compose down                 # Arrêter tout" -ForegroundColor Gray
Write-Host ""
