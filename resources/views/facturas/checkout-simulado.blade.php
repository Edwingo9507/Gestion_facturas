@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Simulación de Checkout - TuMyPay</h3>
                </div>
                <div class="card-body">
                    <h4>Detalles del Pago</h4>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID Factura</th>
                                <th>Referencia</th>
                                <th>Valor</th>
                                <th>Fecha Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($facturas as $factura)
                            <tr>
                                <td>{{ $factura->id }}</td>
                                <td>{{ $factura->referencia }}</td>
                                <td>${{ number_format($factura->valor, 0, ',', '.') }}</td>
                                <td>{{ $factura->fecha_vencimiento ? $factura->fecha_vencimiento->format('d/m/Y') : 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <h5>Total a Pagar: ${{ number_format($total, 0, ',', '.') }}</h5>

                    <p>Esta es una simulación del proceso de pago. Selecciona una acción:</p>

                    <form action="{{ route('checkout.process', $transaction_id) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success">Aprobar Pago</button>
                    </form>

                    <form action="{{ route('checkout.process', $transaction_id) }}" method="POST" class="d-inline ml-2">
                        @csrf
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-danger">Rechazar Pago</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
