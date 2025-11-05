# Sistema de Gestión de Facturas

Este proyecto es una aplicación web desarrollada en Laravel para la gestión y pago de facturas. Permite a los usuarios consultar sus facturas pendientes y realizar pagos a través de la plataforma TuMyPay.

## Requisitos del Sistema

- **PHP**: 8.1 o superior
- **Composer**: Para gestión de dependencias de PHP
- **Node.js**: 16.x o superior (para compilación de assets)
- **NPM**: Para gestión de dependencias de JavaScript
- **Base de datos**: MySQL, PostgreSQL o SQLite
- **Servidor web**: Apache/Nginx con soporte para PHP

## Instalación

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd facturas-app
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Instalar dependencias de JavaScript

```bash
npm install
```

### 4. Configurar el entorno

Copiar el archivo de configuración de ejemplo:

```bash
cp .env.example .env
```

Editar el archivo `.env` con la configuración de tu base de datos y otras variables necesarias:

```env
APP_NAME="Sistema de Facturas"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=facturas_app
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# Configuración de TuMyPay (opcional para desarrollo)
TUMIPAY_API_BASE=https://api.tumipay.co
TUMIPAY_USER=tu_usuario_tumipay
TUMIPAY_PASSWORD=tu_password_tumipay
TUMIPAY_TOKEN_TOP=tu_token_top
```

### 5. Generar clave de aplicación

```bash
php artisan key:generate
```

### 6. Ejecutar migraciones

```bash
php artisan migrate
```

### 7. Ejecutar seeders (opcional)

```bash
php artisan db:seed
```

### 8. Compilar assets

```bash
npm run build
# o para desarrollo
npm run dev
```

### 9. Iniciar el servidor

```bash
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`

## Usuarios del Sistema

### Usuario Administrador
- **Email**: admin@facturas.com
- **Contraseña**: admin123
- **Funciones**:
  - Gestionar facturas (importar desde CSV, eliminar)
  - Ver todas las facturas del sistema

### Usuario Cliente
- No requiere autenticación
- **Funciones**:
  - Consultar facturas por documento
  - Seleccionar facturas para pago
  - Realizar pagos a través de TuMyPay

## Rutas Principales

### Rutas Públicas
- `GET /` - Página principal de consulta de facturas
- `POST /` - Procesar consulta de facturas
- `POST /pagar` - Procesar pago de facturas seleccionadas

### Rutas de Administración
- `GET /admin/login` - Formulario de login de administrador
- `POST /admin/login` - Procesar login de administrador
- `POST /admin/logout` - Cerrar sesión de administrador
- `GET /admin` - Panel de administración (requiere autenticación)
- `POST /admin/import-csv` - Importar facturas desde CSV
- `DELETE /admin/facturas/{id}` - Eliminar factura

## Base de Datos

### Estructura de Tablas

#### Tabla `users`
- `id` (bigint, primary key)
- `name` (string)
- `email` (string, unique)
- `email_verified_at` (timestamp, nullable)
- `password` (string)
- `remember_token` (string, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

#### Tabla `facturas`
- `id` (bigint, primary key)
- `documento` (string) - Documento del cliente
- `nombre_cliente` (string) - Nombre del cliente
- `valor` (decimal, 10,2) - Valor de la factura
- `fecha_vencimiento` (date, nullable) - Fecha de vencimiento
- `pagada` (boolean, default false) - Estado de pago
- `created_at` (timestamp)
- `updated_at` (timestamp)

#### Tabla `cache`
- `key` (string, primary key)
- `value` (text)
- `expiration` (integer)

#### Tabla `jobs`
- `id` (bigint, primary key)
- `queue` (string)
- `payload` (text)
- `attempts` (tinyint)
- `reserved_at` (integer, nullable)
- `available_at` (integer)
- `created_at` (integer)

### Datos de Ejemplo

#### Usuario Administrador
```sql
INSERT INTO users (name, email, password, created_at, updated_at) VALUES
('Admin', 'admin@facturas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());
```

#### Facturas de Ejemplo
```sql
INSERT INTO facturas (documento, nombre_cliente, valor, fecha_vencimiento, pagada, created_at, updated_at) VALUES
('123456789', 'Juan Pérez', 150000.00, '2024-12-31', false, NOW(), NOW()),
('987654321', 'María García', 250000.00, '2024-11-15', false, NOW(), NOW()),
('456789123', 'Carlos Rodríguez', 80000.00, '2024-10-20', true, NOW(), NOW()),
('789123456', 'Ana López', 120000.00, '2024-12-10', false, NOW(), NOW());
```

## Carga de Facturas

### Formato del Archivo CSV

El archivo CSV debe contener las siguientes columnas obligatorias:
- `documento`: Número de documento del cliente
- `nombre_cliente`: Nombre completo del cliente
- `valor`: Valor de la factura (sin puntos ni comas)

**Nota importante**: La columna `fecha_vencimiento` NO debe estar presente en el CSV. El sistema la genera automáticamente con una fecha 30 días posterior a la fecha de carga.

### Ejemplo de Archivo CSV (`sample_facturas.csv`)

```csv
documento,nombre_cliente,valor
123456789,Juan Pérez,150000
987654321,María García,250000
456789123,Carlos Rodríguez,80000
789123456,Ana López,120000
```

### Proceso de Carga

1. Iniciar sesión como administrador en `/admin/login`
2. Ir al panel de administración `/admin`
3. Seleccionar el archivo CSV y hacer clic en "Importar CSV"
4. El sistema validará el formato y creará las facturas con fecha de vencimiento automática

## Configuración de TuMyPay

Para habilitar los pagos reales, configurar las siguientes variables en `.env`:

```env
TUMIPAY_API_BASE=https://api.tumipay.co
TUMIPAY_USER=tu_usuario_proporcionado_por_tumipay
TUMIPAY_PASSWORD=tu_password_proporcionado_por_tumipay
TUMIPAY_TOKEN_TOP=tu_token_top_proporcionado_por_tumipay
```

En desarrollo, si las credenciales no son válidas, el sistema simulará una respuesta exitosa.

## Desarrollo

### Ejecutar en modo desarrollo

```bash
# Servidor de desarrollo
php artisan serve

# Compilación de assets en modo watch
npm run dev

# Ejecutar pruebas
php artisan test
```

### Comandos útiles

```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generar documentación de rutas
php artisan route:list
```

## Licencia

Este proyecto está bajo la Licencia MIT.
