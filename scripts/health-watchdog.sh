#!/usr/bin/env bash
# ========================================================
# Self-Healing Watchdog (Bash / Linux) - SIS-APAFA V11
# ========================================================

URL="${1:-http://localhost:8080/healthz}"
INTERVAL="${2:-30}"
MAX_FAILURES="${3:-3}"
FAILURES=0

echo "🛡️ [Watchdog] Iniciando centinela de salud SIS-APAFA en: $URL (Intervalo: ${INTERVAL}s)"

while true; do
    HTTP_CODE=$(curl -s -o /tmp/healthz_response.json -w "%{http_code}" --max-time 10 "$URL" || echo "000")

    if [ "$HTTP_CODE" -eq 200 ] && grep -q '"status":"healthy"' /tmp/healthz_response.json 2>/dev/null; then
        echo "[$(date +'%T')] ✅ Sistema SALUDABLE (HTTP 200)"
        FAILURES=0
    else
        FAILURES=$((FAILURES + 1))
        echo "[$(date +'%T')] ⚠️ Fallo detectado (HTTP $HTTP_CODE) - Intento $FAILURES/$MAX_FAILURES"
    fi

    if [ "$FAILURES" -ge "$MAX_FAILURES" ]; then
        echo "[$(date +'%T')] 🚨 UMBRAL DE FALLOS ALCANZADO. Ejecutando Auto-Recuperación..."
        docker compose restart app web
        sleep 15
        FAILURES=0
    fi

    sleep "$INTERVAL"
done
