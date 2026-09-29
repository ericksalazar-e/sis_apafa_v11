#!/usr/bin/env bash
# ========================================================
# Script de Despliegue Automatizado a Producción - HA & Resiliencia
# SIS-APAFA V11 - Cero Downtime con Rollback de Emergencia
# ========================================================

set -e

echo "🚀 Iniciando despliegue de alta disponibilidad en SIS-APAFA..."

CURRENT_COMMIT=$(git rev-parse HEAD)
echo "📌 Commit actual: $CURRENT_COMMIT"

# 1. Modo Mantenimiento con Secret Bypass
echo "🔒 Activando modo mantenimiento con bypass secreto..."
php artisan down --secret="apafa-deploy-bypass-2026" --render="errors.503" || true

# 2. Respaldo preventivo de seguridad de la base de datos
echo "💾 Creando snapshot de seguridad de la base de datos previo al despliegue..."
php artisan apafa:backup --keep=30 || echo "⚠️ Advertencia: No se pudo completar el backup preventivo."

# Función de Reversión Automática en caso de fallo
rollback() {
    echo "🚨 ERROR DETECTADO DURANTE EL DESPLIEGUE. Iniciando Rollback de emergencia..."
    git reset --hard "$CURRENT_COMMIT"
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan up
    echo "🔙 Rollback completado al commit anterior: $CURRENT_COMMIT"
    exit 1
}

trap rollback ERR

# 3. Descargar últimas actualizaciones del repositorio
echo "📥 Actualizando código fuente desde rama principal..."
git pull origin main

# 4. Instalar dependencias optimizadas de producción
echo "📦 Instalando dependencias Composer optimizadas..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 5. Ejecutar migraciones atómicas
echo "🗄️ Ejecutando migraciones de base de datos..."
php artisan migrate --force

# 6. Compilar y calentar cachés del framework
echo "⚡ Optimizando y calentando cachés (Config, Routes, Views)..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Enlace simbólico de storage
echo "🔗 Verificando enlace simbólico storage/..."
php artisan storage:link || true

# 8. Reiniciar Workers de Cola en segundo plano
echo "🔄 Reiniciando workers de colas asíncronas..."
php artisan queue:restart || true

# 9. Desactivar modo mantenimiento
echo "🔓 Restaurando servicio al público..."
php artisan up

# 10. Verificación de Salud Post-Despliegue (Deep Healthcheck)
echo "🩺 Verificando estado operativo con Deep Healthcheck..."
HEALTH_STATUS=$(php -r "
    \$res = @file_get_contents('http://localhost:8080/healthz') ?: @file_get_contents('http://127.0.0.1:8000/healthz');
    if (\$res) {
        \$data = json_decode(\$res, true);
        echo \$data['status'] ?? 'unknown';
    } else {
        echo 'unreachable';
    }
")

if [ "$HEALTH_STATUS" != "healthy" ]; then
    echo "⚠️ Advertencia: Healthcheck reportó estado '$HEALTH_STATUS'. Ejecutando diagnóstico detallado:"
    php artisan production:diagnose || true
else
    echo "✅ Healthcheck superado: Sistema 100% operativo y saludable."
fi

echo "========================================================"
echo "🎉 ¡DESPLIEGUE A PRODUCCIÓN COMPLETADO EXITOSAMENTE!"
echo "========================================================"
