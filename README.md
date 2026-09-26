# Sistema de Gestión APAFA (V2) - Laravel 11 & Multi-Tenant

Sistema integral de gestión de **Asociación de Padres de Familia (APAFA)** modernizado sobre **Laravel 11**, arquitectura **Multi-Tenant** y microservicio de **Facturación Electrónica SUNAT**.

---

## 🛠️ Tecnologías y Stack

*   **Backend:** PHP 8.2+ (PHP 8.3 compatible) & Laravel 11.x
*   **Base de Datos:** MySQL 5.7+ / MySQL 8.x
*   **Arquitectura:** Monolito principal con soporte **Multi-tenant** (múltiples colegios/instituciones en una sola base de datos separadas por la columna `empresa_id`).
*   **Frontend:**
    *   Motor de plantillas: Blade (nativo de Laravel)
    *   Estilos: Tailwind CSS (integrado mediante CDN)
    *   Gráficos: Chart.js (Dashboard de KPIs y métricas en tiempo real)
    *   Iconografía: FontAwesome 6
*   **Microservicio de Facturación Electrónica:**
    *   Servicio aislado en `facturacion-service/` encargado de procesar y emitir comprobantes UBL 2.1 (Boletas, Facturas y Notas de Venta) con cálculo de Hash SHA-256 y QR SUNAT.
    *   Emisión asíncrona mediante colas (`EmitirComprobanteSunatJob`) para no bloquear el Punto de Venta (POS) en ventanilla.
*   **Testing y Datos Demo:**
    *   PHPUnit 11 y PestPHP
    *   FakerPHP (`es_PE`) con datos reales para Perú

---

## 🏛️ Estructura y Módulos del Sistema

1. **Dashboard de KPIs (`/dashboard`)**:
   * Indicadores clave: Total de asociados, alumnos, recaudación total (S/.) y deuda por cobrar.
   * Gráfico de línea: Evolución mensual de recaudación.
   * Gráfico de dona (*Doughnut*): Porcentaje de asociados al día vs con deuda.
   * Historial de comprobantes emitidos.
2. **Padrón de Padres de Familia (`/padres`)**:
   * Registro y control de asociados con DNI, parentesco y cargos en directiva.
   * Búsqueda por DNI o apellidos y paginación.
3. **Padrón de Alumnos Matriculados (`/estudiantes`)**:
   * Control de estudiantes asociados a cada padre de familia.
4. **Catálogo de Aportes y Cuotas (`/aportes`)**:
   * Creación de cuotas ordinarias y extraordinarias (ej. Qali Warma, cuota anual).
   * Asignación automática de cuentas por cobrar a todos los padres de la institución.
5. **Asambleas y Asistencia con Lector QR (`/reuniones`)**:
   * Programación de reuniones con fijación de multas por inasistencia.
   * Interfaz de escaneo rápido por código de barras / QR / DNI para registrar la asistencia en segundos.
6. **Punto de Venta (POS) y Comprobantes (`/pagos`)**:
   * Cobro en ventanilla de aportes o multas.
   * Impresión de ticket térmico estándar de 80mm con Código QR oficial SUNAT (`/pagos/{id}/ticket`).
   * Despacho asíncrono hacia el microservicio fiscal.
7. **Selector Multi-Tenant de Sedes / Instituciones**:
   * Menú desplegable en la barra superior que conmuta instantáneamente el contexto institucional (`empresa_id`) y re-filtra automáticamente todos los datos y reportes.

---

## 🚀 Instalación y Puesta en Marcha Local (Laragon / Localhost)

### 1. Requisitos Previos
* PHP 8.2 o superior habilitado en tu servidor web (Laragon / XAMPP / Servidor local).
* MySQL o MariaDB activo en el puerto 3306.
* Composer disponible o el archivo `composer.phar` incluido en la raíz de `c:\SAAS\`.

### 2. Configuración de Base de Datos
En el archivo `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sis_apafa
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Migraciones y Poblado de Datos Demo
Para recrear la base de datos con la nueva estructura multi-tenant y cargar los datos de prueba peruanos:
```powershell
php artisan migrate:fresh --seed
```

### 4. Iniciar el Servidor de Desarrollo
```powershell
php artisan serve
```
El sistema estará accesible inmediatamente en:
👉 **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🧪 Ejecución de Pruebas Automatizadas

El proyecto incluye pruebas de aislamiento multi-tenant y emisión de comprobantes en el microservicio:
```powershell
php artisan test
```

Resultado esperado:
```
PASS  Tests\Unit\ExampleTest
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\MultiTenantAndBillingTest
✓ dashboard loads successfully
✓ multi tenant data isolation
✓ facturacion service emission

Tests: 5 passed (43 assertions)
```

---

## 📁 Ubicación del Código

* **Código fuente del nuevo sistema:** `c:\SAAS\sis_apafa\sis_apafa_v11\`
* **Microservicio aislado de facturación:** `c:\SAAS\sis_apafa\sis_apafa_v11\facturacion-service\`
* **Script SQL Multi-tenant actualizado:** `c:\SAAS\sis_apafa\sis_apafa_v2.sql`
