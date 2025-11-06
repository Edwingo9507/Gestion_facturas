@extends('layouts.app')

@section('title', 'Confirmar Pago')

@section('content')
<h2> Confirmar Pago</h2>

<p>Vas a proceder al pago de las siguientes facturas:</p>

<table>
    <thead>
        <tr>
            <th>Nro. Factura</th>
            <th>Cliente</th>
            <th>Monto</th>
        </tr>
    </thead>
    <tbody>
        @foreach($facturas as $factura)
            <tr>
                <td>{{ $factura->id }}</td>
                <td>{{ $factura->nombre_cliente }}</td>
                <td>$ {{ number_format($factura->valor, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p>Total a pagar: <strong>$ {{ number_format($facturas->sum('valor'), 0, ',', '.') }}</strong></p>

@if(isset($respuesta))
    @php
        $paymentUrl = $respuesta['data']['payment_url'] ?? null;
        $currentStatus = $status ?? 'initial';
    @endphp

    @if($paymentUrl && $currentStatus === 'initial')
        <div id="pending-message" style="background-color: yellow; padding: 10px; display: none; margin-bottom: 10px;">
            Pago pendiente, procesando...
        </div>
        <div id="approved-message" style="background-color: green; color: white; padding: 10px; display: none; margin-bottom: 10px;">
            ¡Pago Aprobado! Tu pago ha sido procesado exitosamente.
        </div>
        <button type="button" id="pago-button" onclick="procesarPago()" class="btn btn-success">Proceder con el Pago</button>
    @elseif($currentStatus === 'pending')
        <div id="pending-message" style="background-color: yellow; padding: 10px; display: block; margin-bottom: 10px;">
            Pago pendiente, procesando...
        </div>
        <div id="approved-message" style="background-color: green; color: white; padding: 10px; display: none; margin-bottom: 10px;">
            ¡Pago Aprobado! Tu pago ha sido procesado exitosamente.
        </div>
        <button type="button" id="pago-button" onclick="procesarPago()" class="btn btn-success">Proceder con el Pago</button>
    @elseif($currentStatus === 'approved')
        <div id="pending-message" style="background-color: yellow; padding: 10px; display: none; margin-bottom: 10px;">
            Pago pendiente, procesando...
        </div>
        <div id="approved-message" style="background-color: green; color: white; padding: 10px; display: block; margin-bottom: 10px;">
            ¡Pago Aprobado! Tu pago ha sido procesado exitosamente.
        </div>
    @else
        <p>No se recibió una URL de pago. Verifica la configuración.</p>
    @endif

    <script>
        function procesarPago() {
            document.getElementById('pending-message').style.display = 'block';
            document.getElementById('pago-button').disabled = true;
            window.open('{{ $paymentUrl }}', '_blank');

            var reference = '{{ $respuesta['data']['transaction']['reference'] }}';
            var previousStatus = null;
            var checkCount = 0;
            var maxChecks = 120; // Stop after 2 minutes (120 * 2 seconds)

            // Get initial status
            fetch('/pago/status/' + reference)
                .then(response => response.json())
                .then(data => {
                    previousStatus = data.status;
                    console.log('Initial status:', data.status);
                })
                .catch(error => console.error('Error getting initial status:', error));

            var interval = setInterval(function() {
                checkCount++;
                fetch('/pago/status/' + reference)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Checked status:', data.status, 'Previous:', previousStatus);
                        if (data.status === 'approved' && previousStatus !== 'approved') {
                            clearInterval(interval);
                            console.log('Updating page to approved');
                            document.getElementById('pending-message').style.display = 'none';
                            document.getElementById('approved-message').style.display = 'block';
                            document.getElementById('pago-button').style.display = 'none';
                        } else if (checkCount >= maxChecks) {
                            clearInterval(interval);
                            console.log('Stopped polling after max checks');
                            document.getElementById('pending-message').innerHTML = 'Pago pendiente, verifique el estado manualmente.';
                        }
                        previousStatus = data.status;
                    })
                    .catch(error => console.error('Error checking status:', error));
            }, 2000); // Check every 2 seconds
        }


    </script>

    <h3>Detalles de la Transacción:</h3>
    <ul>
        <li><strong>Ticket:</strong> {{ $respuesta['data']['ticket'] ?? 'N/A' }}</li>
        <li><strong>Fecha:</strong> {{ $respuesta['data']['date'] ?? 'N/A' }}</li>
        <li><strong>Referencia:</strong> {{ $respuesta['data']['transaction']['reference'] ?? 'N/A' }}</li>
        <li><strong>Monto:</strong> $ {{ number_format(($respuesta['data']['transaction']['amount'] ?? 0), 0, ',', '.') }} {{ $respuesta['data']['transaction']['currency'] ?? '' }}</li>
        <li><strong>Método de Pago:</strong> {{ $respuesta['data']['transaction']['payment_method'] ?? 'N/A' }}</li>
        <li><strong>Descripción:</strong> {{ $respuesta['data']['transaction']['description'] ?? 'N/A' }}</li>
    </ul>
@else
    <p>No se recibió respuesta de TuMyPay.</p>
@endif
@endsection
