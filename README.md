# 🎓 Sistema SIS-APAFA V11 - Laravel 11 Multi-Tenant & Alta Disponibilidad (HA)

Sistema integral de gestión para **Asociaciones de Padres de Familia (APAFA)**, modernizado sobre **Laravel 11**, arquitectura **Multi-Tenant** aislada por colegio, microservicio de **Facturación Electrónica SUNAT UBL 2.1**, contenedorización **Docker HA** y suite de pruebas automatizadas.

---

## 📖 Manual de Operación y Ficha Técnica

El sistema cuenta con un **Manual de Usuario y Técnico Interactivo**:
* **Manual Web Dinámico:** [http://localhost:8080/manual](http://localhost:8080/manual) (con filtros interactivos por rol: *Secretario, Tesorero, Vocal, Administrador, Consejo de Vigilancia*).
* **Manual Estático / Offline:** [http://localhost:8080/manual.html](http://localhost:8080/manual.html) (apto para impresión directa en PDF o consulta sin backend).

---

## 🛠️ Tecnologías y Herramientas de Desarrollo

### 1. Backend & Arquitectura
* **Lenguaje:** PHP 8.2 / 8.3 (Dockerizado con PHP-FPM y Alpine Linux).
* **Framework:** Laravel 11.x (Arquitectura limpia, colas asíncronas, políticas de autorización, Eloquent ORM).
* **Multi-Tenancy:** Multi-empresa nativo en un solo esquema relacional aislado mediante el identificador global `empresa_id` con middleware y filtros automáticos.
* **Microservicio Fiscal:** `facturacion-service/` aislado para generación de XML UBL 2.1, firmado digital, cálculo de Hash SHA-256 y Código QR oficial de ticket térmico 80mm.
* **Procesamiento Asíncrono:** Colas de trabajo (`EmitirComprobanteSunatJob`) gestionadas con Redis para garantizar que la ventanilla POS cobre en menos de 2 segundos.

### 2. Base de Datos & Caché
* **Motor Relacional:** MySQL 8.0 (InnoDB, claves foráneas, soporte UTF8mb4, transacciones ACID y bloqueos pesimistas `lockForUpdate` para la caja de ventanilla).
* **Caché en Memoria & Colas:** Redis 7 Alpine para colas de comprobantes SUNAT, caché distribuido y sesiones ultrarrápidas.

### 3. Servidor Web, Proxy Inverso y Resiliencia (HA)
* **Servidor / Balanceador:** Nginx 1.31+ Alpine con upstream balanceado en modo `least_conn`.
* **Rate Limiting Activo:**
  * Login: límite de 10 req/min para prevenir ataques de fuerza bruta.
  * POS Ventanilla: 25 req/s con burst de 30 para operaciones de cobro continuo.
  * Tráfico General: 40 req/s con burst de 50.
* **Buffers y Conexiones:** FastCGI buffers de 64k, keepalive de 32 conexiones persistentes, compresión Gzip en CSS, JS y JSON.
* **Healthcheck Activo:** Endpoint [`/healthz`](http://localhost:8080/healthz) que monitorea en tiempo real la conexión a MySQL, latencia de base de datos y memoria.
* **Watchdog de Autorecuperación:** Script [`scripts/health-watchdog.ps1`](scripts/health-watchdog.ps1) y [`scripts/health-watchdog.sh`](scripts/health-watchdog.sh) que inspecciona los contenedores y los reinicia automáticamente si detecta fallos.
* **Respaldos Automatizados:** Comando `php artisan apafa:backup --keep=14` programado diariamente a las 02:00 am con compresión Gzip y rotación de 14 días.

### 4. Frontend & Experiencia de Usuario
* **Motor de Vistas:** Blade (Laravel nativo, sin dependencias pesadas de Node en producción).
* **Estilos & UI:** Tailwind CSS y Google Fonts (*Plus Jakarta Sans* y *JetBrains Mono*).
* **Visualización de Datos:** Chart.js para dashboards en tiempo real (recaudación mensual, estado de mora, comparativas).
* **Iconografía:** FontAwesome 6 Pro/Free.

---

## 🧩 Extensiones de PHP Requeridas y Compiladas

El contenedor Docker de producción compila y activa **10 extensiones de PHP fundamentales**:

| Extensión | Propósito y Utilidad en SIS-APAFA |
| :--- | :--- |
| **`pdo_mysql`** | Transacciones de alto rendimiento e integridad referencial con MySQL 8.0. |
| **`gd` (Freetype + libjpeg)** | Generación dinámica de carnés de estudiantes con foto, credenciales oficiales y sellos institucionales. |
| **`intl` (ICU)** | Formateo estandarizado de moneda nacional (`S/. PEN`), fechas en español y cotejo Unicode. |
| **`mbstring`** | Procesamiento seguro de caracteres multibyte (tildes, eñes, nombres y apellidos peruanos). |
| **`zip` (libzip)** | Carga e importación masiva de padrones desde archivos Excel/CSV y descarga de respaldos comprimidos. |
| **`bcmath`** | Cálculos matemáticos de precisión arbitraria para tarifas, descuentos, IGV y multas sin pérdida de céntimos. |
| **`exif`** | Lectura de metadatos de cámaras/celulares para corregir automáticamente la orientación de fotos en carnés. |
| **`pcntl`** | Manejo de señales de proceso en Linux para workers de cola asíncronos y graceful shutdowns. |
| **`redis` (PECL C)** | Conector nativo de alto rendimiento en C para comunicación sub-milisegundo con el servidor Redis 7. |
| **`opcache`** | Precompilación del bytecode de PHP en memoria RAM para servir peticiones en menos de 15 milisegundos. |

---

## 🔬 Calidad de Código, Pruebas y CI/CD

El proyecto cuenta con un riguroso ciclo de calidad y tolerancia a fallos:

* **Laravel Pint:** Linter y formateador de código conforme a PSR-12 ([`pint.json`](pint.json)).
  ```powershell
  ./vendor/bin/pint --test
  ```
* **PHPStan / Larastan:** Análisis estático de tipos a Nivel 5 ([`phpstan.neon`](phpstan.neon)).
  ```powershell
  ./vendor/bin/phpstan analyse
  ```
* **PHPUnit 11 & PestPHP:** Batería de **80 pruebas automatizadas con 703 aserciones** que validan:
  * Aislamiento multi-tenant estricto entre colegios.
  * Bloqueos transaccionales y cuadre de caja en POS.
  * Emisión y firmado de comprobantes en el microservicio SUNAT.
  * Padrón electoral y asistencia por código de barras / QR.
  ```powershell
  php artisan test
  ```
* **Git Pre-commit & Pre-push Hooks:** Hooks automáticos configurados en [`scripts/git-hooks/`](scripts/git-hooks/) que impiden commits con errores de estilo o pushes con pruebas rotas.
* **Pipeline GitHub Actions:** Flujo CI en [`.github/workflows/ci.yml`](.github/workflows/ci.yml) con matriz de pruebas sobre PHP 8.2 y PHP 8.3.

---

## 🏛️ Módulos del Sistema

1. **Dashboard de KPIs (`/dashboard`)**:
   * Indicadores clave: Total de asociados, alumnos, recaudación total (S/.) y deuda por cobrar.
   * Gráfico de línea interactivo: Evolución mensual de recaudación.
   * Gráfico de dona (*Doughnut*): Porcentaje de asociados al día vs con deuda.
2. **Padrón de Padres de Familia (`/padres`)**:
   * Registro y control de asociados con DNI, parentesco y cargos en directiva.
   * Búsqueda por DNI o apellidos y paginación rápida.
3. **Padrón de Alumnos Matriculados (`/estudiantes`)**:
   * Control de estudiantes matriculados vinculados a cada padre o tutor.
   * Emisión e impresión de carnés escolares con foto y código QR.
4. **Catálogo de Aportes y Cuotas (`/aportes`)**:
   * Creación de cuotas ordinarias y extraordinarias (ej. Qali Warma, cuota anual, aniversarios).
   * Asignación automática de cuentas por cobrar a todos los padres de la institución.
5. **Asambleas y Asistencia con Lector QR (`/reuniones`)**:
   * Programación de asambleas ordinarias y extraordinarias con fijación de multas.
   * Interfaz de escaneo rápido por código de barras / QR / DNI para registrar la asistencia en segundos.
6. **Punto de Venta (POS) y Comprobantes (`/pagos`)**:
   * Cobro en ventanilla de aportes o multas con validación transaccional.
   * Impresión de ticket térmico estándar de 80mm con Código QR oficial SUNAT (`/pagos/{id}/ticket`).
   * Despacho asíncrono hacia el microservicio fiscal.
7. **Selector Multi-Tenant de Sedes / Instituciones**:
   * Conmutador global en la barra superior para alternar entre colegios instantáneamente.

---

## 🐳 Puesta en Marcha con Docker (Producción / Local)

### 1. Levantar la Infraestructura
```powershell
docker compose up -d --build
```

### 2. Servicios Disponibles
* **Aplicación Web:** [http://localhost:8080](http://localhost:8080)
* **Manual Interactivo:** [http://localhost:8080/manual](http://localhost:8080/manual)
* **Healthcheck:** [http://localhost:8080/healthz](http://localhost:8080/healthz)
* **phpMyAdmin:** [http://localhost:8081](http://localhost:8081)
* **MySQL 8.0:** Puerto `3307` (`root` / `rootpassword`)
* **Redis 7:** Puerto `6380`

### 3. Escalar Contenedores de Aplicación
```powershell
docker compose up -d --scale app=3
```
Nginx distribuirá automáticamente la carga de tráfico entre las 3 instancias de PHP-FPM con `least_conn`.

### 4. Ejecutar las Pruebas en el Contenedor
```powershell
docker compose exec -T app php artisan test
```
Resultados: **80 passed (703 assertions)**.

