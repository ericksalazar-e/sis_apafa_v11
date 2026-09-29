# ========================================================
# Self-Healing Watchdog - SIS-APAFA V11
# Monitoreo continuo de salud y autorecuperación
# ========================================================

param(
    [string]$Url = "http://localhost:8080/healthz",
    [int]$IntervalSeconds = 30,
    [int]$MaxConsecutiveFailures = 3
)

$consecutiveFailures = 0

Write-Host "🛡️ [Watchdog] Iniciando centinela de autorecuperación SIS-APAFA..." -ForegroundColor Cyan
Write-Host "   -> Monitoreando: $Url cada $IntervalSeconds segundos" -ForegroundColor Cyan

while ($true) {
    try {
        $response = Invoke-RestMethod -Uri $Url -Method Get -TimeoutSec 10 -ErrorAction Stop
        
        if ($response.status -eq "healthy") {
            $dbLatency = $response.checks.database.latency_ms
            Write-Host "[$(Get-Date -Format 'HH:mm:ss')] ✅ Sistema SALUDABLE (DB Latencia: ${dbLatency}ms)" -ForegroundColor Green
            $consecutiveFailures = 0
        } else {
            $consecutiveFailures++
            Write-Host "[$(Get-Date -Format 'HH:mm:ss')] ⚠️ Estado degradado detectado ($consecutiveFailures/$MaxConsecutiveFailures)" -ForegroundColor Yellow
        }
    }
    catch {
        $consecutiveFailures++
        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] ❌ Falló la consulta de salud ($consecutiveFailures/$MaxConsecutiveFailures): $($_.Exception.Message)" -ForegroundColor Red
    }

    if ($consecutiveFailures -ge $MaxConsecutiveFailures) {
        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] 🚨 UMBRAL DE FALLOS ALCANZADO. Iniciando Auto-Recuperación (Self-Healing)..." -ForegroundColor Magenta
        
        Write-Host "🔄 Reiniciando servicios en segundo plano..." -ForegroundColor Yellow
        docker compose restart app web
        
        Write-Host "⏳ Esperando 15 segundos para estabilización..." -ForegroundColor Yellow
        Start-Sleep -Seconds 15
        
        $consecutiveFailures = 0
        Write-Host "🩺 Reanudando monitoreo tras autorecuperación..." -ForegroundColor Cyan
    }

    Start-Sleep -Seconds $IntervalSeconds
}
