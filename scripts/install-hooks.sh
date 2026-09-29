#!/usr/bin/env bash
set -e

HOOKS_DIR=".git/hooks"
CUSTOM_DIR="scripts/git-hooks"

if [ ! -d "$HOOKS_DIR" ]; then
    echo "❌ No se encontró el directorio .git/hooks."
    exit 1
fi

cp "$CUSTOM_DIR/pre-commit" "$HOOKS_DIR/pre-commit"
cp "$CUSTOM_DIR/pre-push" "$HOOKS_DIR/pre-push"

chmod +x "$HOOKS_DIR/pre-commit" "$HOOKS_DIR/pre-push"

echo "✅ Hooks de Git instalados exitosamente en .git/hooks/"
echo "   - pre-commit: Linting y comprobación de sintaxis con Laravel Pint"
echo "   - pre-push:   Batería de pruebas automatizadas"
