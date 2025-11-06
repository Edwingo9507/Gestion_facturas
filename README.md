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

### Configuración para Desarrollo con Webhooks

Para probar los webhooks en desarrollo local, es necesario exponer el servidor local a internet usando ngrok:

#### 1. Instalar ngrok

```bash
# macOS con Homebrew
brew install ngrok/ngrok/ngrok

# O descargar desde https://ngrok.com/download
```

#### 2. Configurar ngrok con authtoken

```bash
ngrok config add-authtoken TU_AUTHTOKEN_AQUI
```

#### 3. Iniciar ngrok apuntando al puerto 8000

```bash
ngrok http 8000
```

Esto generará una URL como `https://abcd1234.ngrok-free.app` que apunta a tu servidor local.

#### 4. Actualizar la URL del webhook en el código

En `app/Http/Controllers/FacturaController.php`, línea donde se define `ipn_url`, reemplazar con la URL de ngrok:

```php
'ipn_url' => 'https://abcd1234.ngrok-free.app/webhook/tumipay',
```

#### 5. Configurar TuMyPay para usar la URL de ngrok

Proporcionar la URL de ngrok a TuMyPay como URL de webhook para que puedan enviar las notificaciones de pago.

## Funcionamiento del Sistema de Pagos

### Flujo de Pago Completo

1. **Consulta de Facturas**: El usuario ingresa su documento y consulta sus facturas pendientes
2. **Selección de Facturas**: El usuario selecciona las facturas que desea pagar
3. **Creación de Transacción**: El sistema crea una transacción en TuMyPay con:
   - Referencia única generada
   - Monto total de las facturas seleccionadas
   - Datos del cliente
   - URL de redirección directa a `/pago/ok/{reference}`
   - URL del webhook (ngrok en desarrollo)
   - Metadata con IDs de las facturas
4. **Redirección a TuMyPay**: El usuario es redirigido a la pasarela de pagos de TuMyPay
5. **Procesamiento del Pago**: TuMyPay procesa el pago
6. **Redirección de Retorno**: TuMyPay redirige al usuario a `/pago/ok/{reference}`
7. **Actualización en Tiempo Real**: La página muestra inicialmente "Pago Pendiente" y se actualiza automáticamente cada 3 segundos
8. **Webhook de Notificación**: TuMyPay envía un webhook POST a `/webhook/tumipay` con el resultado del pago
9. **Actualización de Base de Datos**: El webhook actualiza el estado de las facturas en la base de datos
10. **Actualización Visual**: La página automáticamente cambia a "Pago Aprobado" sin recargar

### Persistencia de Transacciones

Para asegurar que el webhook pueda acceder a las facturas asociadas a una transacción, el sistema guarda cada transacción en un archivo JSON en `storage/app/transactions/{reference}.json` que contiene:

```json
{
  "reference": "REF690D2CB014C46",
  "facturas": [1, 2, 3],
  "amount": 1500.00,
  "status": "pending",
  "created_at": "2025-11-06T18:18:08.000000Z",
  "tumipay_response": {...}
}
```

### Manejo del Webhook

El webhook `/webhook/tumipay` maneja diferentes formatos de payload:

- **Payload con metadata**: `{"top_status": "APPROVED", "top_metadata": {"facturas": [1,2]}}`
- **Payload sin metadata**: `{"top_status": "APPROVED", "top_reference": "REF..."}`

En ambos casos, el webhook:
1. Extrae el status del pago (`top_status`)
2. Obtiene los IDs de las facturas (de metadata o del archivo de transacción)
3. Actualiza el estado `pagada = true` en la base de datos
4. Actualiza el archivo de transacción con el nuevo status
5. Responde con `{"status": "ok"}`

### Verificación de Estado

El endpoint `/pago/status/{reference}` permite verificar el estado de un pago consultando la base de datos:

```json
{
  "status": "approved" // o "pending"
}
```

## Pruebas de Pago

### Pruebas con TuMyPay Sandbox

TuMyPay proporciona un entorno de pruebas (sandbox) donde puedes simular pagos sin usar dinero real.

#### Configuración para Pruebas

1. **Credenciales de Prueba**: Solicitar credenciales de sandbox a TuMyPay
2. **Email de Prueba**: Usar `approved@tumipay.co` para simular pagos aprobados
3. **Webhook de Prueba**: Configurar ngrok como se explicó anteriormente

#### Simulación de Pagos

1. **Pago Aprobado**:
   - Usar email: `approved@tumipay.co`
   - El pago se aprobará automáticamente

2. **Pago Rechazado**:
   - Usar email: `declined@tumipay.co`
   - El pago será rechazado

3. **Pago Pendiente**:
   - Usar email: `pending@tumipay.co`
   - El pago quedará en estado pendiente

### Pruebas Manuales del Webhook

Puedes probar el webhook manualmente usando curl:

#### Webhook con Metadata (Desarrollo)
```bash
curl -X POST https://tu-ngrok-url.ngrok-free.app/webhook/tumipay \
  -H "Content-Type: application/json" \
  -d '{
    "top_status": "APPROVED",
    "top_reference": "REF690D2CB014C46",
    "top_metadata": {"facturas": [1, 2]}
  }'
```

#### Webhook sin Metadata (Producción)
```bash
curl -X POST https://tu-ngrok-url.ngrok-free.app/webhook/tumipay \
  -H "Content-Type: application/json" \
  -d '{
    "top_status": "APPROVED",
    "top_reference": "REF690D2CB014C46"
  }'
```

#### Verificar Estado de Facturas
```bash
php artisan tinker --execute="echo App\Models\Factura::whereIn('id', [1,2])->get(['id', 'pagada'])->toJson()"
```

### Pruebas del Flujo Completo

1. **Iniciar servidor**: `php artisan serve --host=0.0.0.0 --port=8000`
2. **Iniciar ngrok**: `ngrok http 8000`
3. **Actualizar webhook URL** en el código con la URL de ngrok
4. **Crear transacción**: Ir a la aplicación y crear un pago
5. **Simular webhook**: Usar curl para enviar el webhook
6. **Verificar actualización**: La página debería cambiar automáticamente de "Pendiente" a "Aprobado"

### Debugging

#### Logs del Webhook
```bash
tail -f storage/logs/laravel.log
```

Los logs mostrarán:
- Recepción del webhook con el payload completo
- Actualización exitosa de facturas
- Errores si ocurren

#### Verificar Transacciones Guardadas
```bash
ls -la storage/app/transactions/
cat storage/app/transactions/REF690D2CB014C46.json
```

#### Verificar Estado de Base de Datos
```bash
php artisan tinker --execute="App\Models\Factura::all()->toJson()"
```

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
