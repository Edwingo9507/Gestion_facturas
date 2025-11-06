<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factura;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class FacturaController extends Controller
{
    /**
     * Muestra la página principal de consulta
     */
    public function index(Request $request)
    {
        // Check if there's a reference in the query (redirect from TuMyPay)
        $reference = $request->query('reference');
        if ($reference) {
            return redirect()->route('pagar.facturas', ['reference' => $reference]);
        }

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
        if (!$facturasIds) {
            return redirect()->route('consulta');
        }
        $facturas = \App\Models\Factura::whereIn('id', $facturasIds)->get();
        $total = $facturas->sum('valor');

        // Check if there's a reference in the request (redirect from TuMyPay)
        $reference = $request->query('reference');
        if ($reference) {
            // This is a redirect from TuMyPay, redirect to /pagar with the reference
            return redirect()->route('pagar.facturas', ['reference' => $reference]);
        }

        // If this is a GET request without reference, redirect to consulta
        if ($request->isMethod('get') && !$reference) {
            return redirect()->route('consulta');
        }

        // If there's a reference, load the transaction data and show the confirmation page
        if ($reference) {
            $transactionData = session('transaction_' . $reference);
            if ($transactionData) {
                $facturasIds = $transactionData['metadata']['facturas'] ?? [];
                $facturas = Factura::whereIn('id', $facturasIds)->get();

                // Determine the status based on paid invoices
                $pagadas = $facturas->where('pagada', true)->count();
                $totalFacturas = $facturas->count();
                $status = ($pagadas === $totalFacturas) ? 'approved' : 'pending';

                return view('facturas.confirmacion', [
                    'facturas' => $facturas,
                    'respuesta' => $transactionData,
                    'status' => $status
                ]);
            } else {
                return redirect()->route('consulta')->with('error', 'Transacción no encontrada.');
            }
        }

        // Datos simulados del cliente
        $cliente = $facturas->first();

        // Generar referencia única para el pago
        $uniqueReference = 'REF' . strtoupper(uniqid());

        $payload = [
            'reference' => $uniqueReference,
            'amount' => (int) $total, // Amount in COP
            'currency' => 'COP',
            'country' => 'CO',
            'payment_method' => 'ALL_METHODS',
            'description' => 'Pago de facturas seleccionadas',
            'customer_data' => [
                'full_name' => $cliente->nombre_cliente,
                'email' => 'approved@tumipay.co', // Usa este email para simular pago aprobado
                'legal_doc' => $cliente->documento,
                'legal_doc_type' => 'CC',
                'phone_code' => '57',
                'phone_number' => '3005604063', // Default phone for simulation
            ],
            'expiration_time' => 720,
            'redirect_url' => route('pago.ok', ['reference' => $uniqueReference]), // Redirect directly to payment confirmation page
            'ipn_url' => 'https://490b8ebf9f2f.ngrok-free.app/webhook/tumipay',
            'metadata' => [
                'facturas' => $facturas->pluck('id')->toArray(),
            ],
        ];

        $client = new Client([
            'base_uri' => env('TUMIPAY_API_BASE'),
            'timeout' => 10.0,
            'verify' => false, // evitar problemas de certificado en desarrollo
        ]);

        $response = $client->post('/production/api/v1/payin', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(env('TUMIPAY_USER') . ':' . env('TUMIPAY_PASSWORD')),
                'Token-Top' => env('TUMIPAY_TOKEN_TOP'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $body = json_decode($response->getBody(), true);

        // Almacenar la transacción en archivo usando la referencia generada
        $transactionData = [
            'reference' => $uniqueReference,
            'facturas' => $facturas->pluck('id')->toArray(),
            'amount' => $total,
            'status' => 'pending',
            'created_at' => now(),
            'tumipay_response' => $body
        ];
        Storage::put('transactions/' . $uniqueReference . '.json', json_encode($transactionData));

        // También mantener en sesión para compatibilidad
        session(['transaction_' . $uniqueReference => $body]);

        // Siempre mostrar la vista de confirmación con la respuesta completa
        return view('facturas.confirmacion', [
            'facturas' => $facturas,
            'respuesta' => $body
        ]);
    }





    /**
     * Maneja el webhook de TuMyPay para actualizar el estado del pago
     */
    public function webhook(Request $request)
    {
        // Log the incoming webhook
        \Log::info('Webhook TuMyPay recibido', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // El payload real de TuMyPay tiene 'top_status' en lugar de 'status'
        $status = $request->input('top_status') ?: $request->input('status');

        // Intentar obtener facturasIds de diferentes ubicaciones posibles
        $facturasIds = $request->input('metadata.facturas') ?: $request->input('top_metadata.facturas');

        // Si no hay metadata.facturas, intentar extraer de top_metadata si existe
        if (!$facturasIds && $request->has('top_metadata')) {
            $topMetadata = $request->input('top_metadata');
            if (is_array($topMetadata) && isset($topMetadata['facturas'])) {
                $facturasIds = $topMetadata['facturas'];
            }
        }

        // Si aún no hay facturasIds, intentar obtenerlos del archivo usando la referencia
        if (!$facturasIds && $request->has('top_reference')) {
            $reference = $request->input('top_reference');
            $transactionFile = Storage::get('transactions/' . $reference . '.json');
            if ($transactionFile) {
                $transactionData = json_decode($transactionFile, true);
                if ($transactionData && isset($transactionData['facturas'])) {
                    $facturasIds = $transactionData['facturas'];
                }
            }
        }

        // Validar que el payload tenga los campos necesarios
        if (!$status || !$facturasIds) {
            \Log::error('Webhook TuMyPay inválido', [
                'status' => $status,
                'facturasIds' => $facturasIds,
                'payload' => $request->all(),
            ]);
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Determinar si el pago fue exitoso (solo 'APPROVED' se considera exitoso)
        $pagoExitoso = strtoupper($status) === 'APPROVED';

        // Actualizar el estado de las facturas
        Factura::whereIn('id', $facturasIds)->update([
            'pagada' => $pagoExitoso,
        ]);

        // Actualizar el archivo de transacción con el nuevo status
        if ($request->has('top_reference')) {
            $reference = $request->input('top_reference');
            $transactionFile = Storage::get('transactions/' . $reference . '.json');
            if ($transactionFile) {
                $transactionData = json_decode($transactionFile, true);
                $transactionData['status'] = $pagoExitoso ? 'approved' : 'failed';
                $transactionData['updated_at'] = now();
                Storage::put('transactions/' . $reference . '.json', json_encode($transactionData));
            }
        }

        // Log del resultado
        \Log::info('Estado de facturas actualizado', [
            'facturas_ids' => $facturasIds,
            'pago_exitoso' => $pagoExitoso,
            'status_tumipay' => $status,
        ]);

        // Responder a TuMyPay con un código de éxito
        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Verifica el estado del pago para polling
     */
    public function checkStatus(Request $request, $reference)
    {
        // Primero intentar obtener del archivo de transacción
        $transactionFile = Storage::get('transactions/' . $reference . '.json');
        if ($transactionFile) {
            $transactionData = json_decode($transactionFile, true);
            $facturasIds = $transactionData['facturas'] ?? [];
        } else {
            // Fallback a sesión para compatibilidad
            $transactionData = session('transaction_' . $reference);
            $facturasIds = $transactionData['metadata']['facturas'] ?? [];
        }

        if (!$facturasIds) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $facturas = Factura::whereIn('id', $facturasIds)->get();

        // Determinar el estado basado en las facturas pagadas
        $pagadas = $facturas->where('pagada', true)->count();
        $totalFacturas = $facturas->count();
        $status = ($pagadas === $totalFacturas) ? 'approved' : 'pending';

        return response()->json(['status' => $status]);
    }



    /**
     * Muestra la página de confirmación de pago exitoso
     */
    public function pagoOk(Request $request, $reference)
    {
        // Primero intentar obtener del archivo de transacción
        $transactionFile = Storage::get('transactions/' . $reference . '.json');
        if ($transactionFile) {
            $transactionData = json_decode($transactionFile, true);
            $facturasIds = $transactionData['facturas'] ?? [];
            $tumipayResponse = $transactionData['tumipay_response'] ?? [];
        } else {
            // Fallback a sesión para compatibilidad
            $transactionData = session('transaction_' . $reference);
            $facturasIds = $transactionData['metadata']['facturas'] ?? [];
            $tumipayResponse = $transactionData;
        }

        if (!$facturasIds) {
            return redirect()->route('consulta')->with('error', 'Transacción no encontrada.');
        }

        $facturas = Factura::whereIn('id', $facturasIds)->get();
        $total = $facturas->sum('valor');

        // Determinar el estado basado en las facturas pagadas
        $pagadas = $facturas->where('pagada', true)->count();
        $totalFacturas = $facturas->count();
        $status = ($pagadas === $totalFacturas) ? 'approved' : 'pending';

        return view('facturas.pago-ok', [
            'facturas' => $facturas,
            'total' => $total,
            'reference' => $reference,
            'status' => $status,
            'transactionData' => $tumipayResponse,
        ]);
    }

}
