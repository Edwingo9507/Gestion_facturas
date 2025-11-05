<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factura;
use GuzzleHttp\Client;

class FacturaController extends Controller
{
    /**
     * Muestra la página principal de consulta
     */
    public function index()
    {
        // Muestra la vista principal con el formulario vacío
        return view('facturas.consulta');
    }

    /**
     * Procesa la consulta de facturas por documento
     */
    public function consultar(Request $request)
    {
        // Validar que el campo documento venga lleno
        $request->validate([
            'documento' => 'required|string|max:20',
        ]);

        // Buscar las facturas asociadas a ese documento
        $facturas = Factura::where('documento', $request->documento)->get();

        // Retornar la misma vista con los resultados
        // Esto mantiene el layout y el formulario visibles
        return view('facturas.consulta', compact('facturas'));
    }


    public function pagarSeleccionadas(Request $request)
    {
        $facturasIds = $request->input('facturas');
        $facturas = \App\Models\Factura::whereIn('id', $facturasIds)->get();
        $total = $facturas->sum('valor');

        // Datos simulados del cliente
        $cliente = $facturas->first();

        $payload = [
            'amount' => (int) $total,
            'currency' => 'COP',
            'customer' => [
                'name' => $cliente->nombre_cliente,
                'email' => 'approved@tumipay.co', // Usa este email para simular pago aprobado
                'document' => '123456789',
                'document_type' => 'CC',
            ],
            'metadata' => [
                'facturas' => $facturas->pluck('id')->toArray(),
            ],
        ];

        $client = new Client([
            'base_uri' => env('TUMIPAY_API_BASE'),
            'timeout' => 10.0,
            'verify' => false, // evitar problemas de certificado en desarrollo
        ]);

        try {
            $response = $client->post('/payin/create', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode(env('TUMIPAY_USER') . ':' . env('TUMIPAY_PASSWORD')),
                    'Token-Top' => env('TUMIPAY_TOKEN_TOP'),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $body = json_decode($response->getBody(), true);

            // Siempre mostrar la vista de confirmación con la respuesta completa
            return view('facturas.confirmacion', [
                'facturas' => $facturas,
                'respuesta' => $body
            ]);

        } catch (\Exception $e) {
            // Simular respuesta de TuMyPay para mostrar la vista de confirmación
            $simulatedResponse = [
                'checkout_url' => 'https://checkout-simulado.tumipay.co/pay/123456',
                'status' => 'simulated_success',
                'message' => 'Pago simulado creado exitosamente (credenciales no válidas)'
            ];

            return view('facturas.confirmacion', [
                'facturas' => $facturas,
                'respuesta' => $simulatedResponse
            ]);
        }
    }





}
