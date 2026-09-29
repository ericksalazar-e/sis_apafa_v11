# Instalar Git Hooks en PowerShell
$ErrorActionPreference = "Stop"

$gitHooksDir = Join-Path (Get-Location) ".git\hooks"
$customHooksDir = Join-Path (Get-Location) "scripts\git-hooks"

if (-not (Test-Path $gitHooksDir)) {
    Write-Error "No se encontró el directorio .git/hooks. Asegúrate de estar en la raíz de un repositorio Git."
}

Copy-Item "$customHooksDir\pre-commit" "$gitHooksDir\pre-commit" -Force
Copy-Item "$customHooksDir\pre-push" "$gitHooksDir\pre-push" -Force

Write-Host "✅ Hooks de Git instalados exitosamente en .git/hooks/" -ForegroundColor Green
Write-Host "   - pre-commit: Linting y comprobación de sintaxis con Laravel Pint" -ForegroundColor Cyan
Write-Host "   - pre-push:   Batería de pruebas automatizadas" -ForegroundColor Cyan
