# TODO: Corregir el flujo de pago con TumiPay

## Pasos a completar:

- [x] Cambiar el redirect_url en FacturaController.php para redirigir directamente a /pago/ok/{reference} en lugar de a /?reference={reference}.
- [x] Agregar instrucciones para usar ngrok en desarrollo para exponer el webhook localmente, ya que TumiPay no puede acceder al servidor local.
- [x] Verificar que el webhook actualice correctamente el estado de las facturas en la base de datos.
- [x] Probar el flujo completo: selección de facturas, redirección a pasarela, recepción de webhook, actualización de DB y vista de confirmación.
- [x] Actualizar README.md con documentación completa del proyecto, funcionamiento y pruebas

## Instrucciones para ngrok en desarrollo:

Para que el webhook sea accesible desde TumiPay durante el desarrollo, usa ngrok para exponer el servidor local:

1. Instala ngrok si no lo tienes: `npm install -g ngrok` o descarga desde https://ngrok.com/.
2. Ejecuta el servidor Laravel: `php artisan serve` (por defecto en http://localhost:8000).
3. En otra terminal, ejecuta: `ngrok http 8000`.
4. Copia la URL HTTPS generada por ngrok (ej: https://abc123.ngrok.io).
5. Actualiza el .env con la URL de ngrok para TUMIPAY_API_BASE si es necesario, y configura el ipn_url en el payload del pago con la URL de ngrok + /webhook/tumipay.
6. Asegúrate de que el webhook esté configurado correctamente en TumiPay para apuntar a https://tu-ngrok-url.ngrok.io/webhook/tumipay.

Nota: Recuerda actualizar la URL cada vez que reinicies ngrok, ya que cambia.
