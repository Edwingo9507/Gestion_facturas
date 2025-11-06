@extends('layouts.app')

@section('title', 'Estado del Pago')

@section('content')
<h2>Estado del Pago</h2>

<div id="payment-status">
@if($status === 'approved')
    <div style="color: green;">
        <h3>¡Pago Aprobado!</h3>
        <p>Tu pago ha sido procesado exitosamente.</p>
    </div>
@elseif($status === 'pending')
    <div style="color: orange;">
        <h3>Pago Pendiente</h3>
        <p>Tu pago está siendo procesado. Recibirás una confirmación pronto.</p>
    </div>
@else
    <div style="color: red;">
        <h3>Pago Rechazado</h3>
        <p>Tu pago no pudo ser procesado. Por favor, intenta nuevamente.</p>
    </div>
@endif
</div>

<p><strong>Referencia de Transacción:</strong> {{ $reference }}</p>

<h3>Detalles del Pago:</h3>
<table>
    <thead>
        <tr>
            <th>Nro. Factura</th>
            <th>Cliente</th>
            <th>Monto</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($facturas as $factura)
            <tr>
                <td>{{ $factura->id }}</td>
                <td>{{ $factura->nombre_cliente }}</td>
                <td>$ {{ number_format($factura->valor, 0, ',', '.') }}</td>
                <td id="factura-status-{{ $factura->id }}">{{ $factura->pagada ? 'Pagada' : 'Pendiente' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p><strong>Total Pagado:</strong> $ {{ number_format($total, 0, ',', '.') }}</p>

@if(isset($transactionData))
    <h3>Detalles de la Transacción:</h3>
    <ul>
        <li><strong>Ticket:</strong> {{ $transactionData['data']['ticket'] ?? 'N/A' }}</li>
        <li><strong>Fecha:</strong> {{ $transactionData['data']['date'] ?? 'N/A' }}</li>
        <li><strong>Referencia:</strong> {{ $transactionData['data']['transaction']['reference'] ?? 'N/A' }}</li>
        <li><strong>Monto:</strong> $ {{ number_format(($transactionData['data']['transaction']['amount'] ?? 0), 0, ',', '.') }} {{ $transactionData['data']['transaction']['currency'] ?? '' }}</li>
        <li><strong>Método de Pago:</strong> {{ $transactionData['data']['transaction']['payment_method'] ?? 'N/A' }}</li>
        <li><strong>Descripción:</strong> {{ $transactionData['data']['transaction']['description'] ?? 'N/A' }}</li>
    </ul>
@endif

<a href="{{ route('consulta') }}" class="btn btn-primary">Volver a Consultar Facturas</a>

<script>
    // Función para actualizar el estado del pago
    function updatePaymentStatus() {
        fetch('/pago/status/{{ $reference }}')
            .then(response => response.json())
            .then(data => {
                const statusDiv = document.getElementById('payment-status');
                if (data.status === 'approved') {
                    statusDiv.innerHTML = `
                        <div style="color: green;">
                            <h3>¡Pago Aprobado!</h3>
                            <p>Tu pago ha sido procesado exitosamente.</p>
                        </div>
                    `;
                    // Actualizar estados de facturas individuales
                    @foreach($facturas as $factura)
                        document.getElementById('factura-status-{{ $factura->id }}').textContent = 'Pagada';
                    @endforeach
                } else if (data.status === 'pending') {
                    statusDiv.innerHTML = `
                        <div style="color: orange;">
                            <h3>Pago Pendiente</h3>
                            <p>Tu pago está siendo procesado. Recibirás una confirmación pronto.</p>
                        </div>
                    `;
                }
            })
            .catch(error => console.error('Error al actualizar estado:', error));
    }

    // Actualizar cada 3 segundos si el estado es pendiente
    @if($status === 'pending')
        setInterval(updatePaymentStatus, 3000);
    @endif
</script>
@endsection
