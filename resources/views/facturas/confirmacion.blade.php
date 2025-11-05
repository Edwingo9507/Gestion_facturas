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
    <!-- <h3>Respuesta de TuMyPay:</h3> -->
    <!-- <pre>{{ json_encode($respuesta, JSON_PRETTY_PRINT) }}</pre> -->

    @if(isset($respuesta['checkout_url']))
        <!-- <p>URL de Checkout: <a href="{{ $respuesta['checkout_url'] }}" target="_blank">{{ $respuesta['checkout_url'] }}</a></p> -->
        <button onclick="window.open('{{ $respuesta['checkout_url'] }}', '_blank');">
            Proceder al pago con TuMyPay
        </button>
    @else
        <p>No se recibió una URL de checkout. Verifica la respuesta arriba.</p>
    @endif
@else
    <p>No se recibió respuesta de TuMyPay.</p>
@endif
@endsection
