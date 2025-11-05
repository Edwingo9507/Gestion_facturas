@extends('layouts.app')

@section('title', 'Panel de Administración')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Panel de Administración</h2>
        <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-danger">Cerrar Sesión</button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Importar CSV -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Importar Facturas desde CSV</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.import.csv') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="csv_file" class="form-label">Seleccionar archivo CSV</label>
                    <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                    <div class="form-text">
                        El archivo debe contener las columnas: documento, nombre_cliente, valor, fecha_vencimiento
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Importar Facturas</button>
            </form>
        </div>
    </div>

    <!-- Lista de Facturas -->
    <div class="card">
        <div class="card-header">
            <h5>Facturas Registradas</h5>
        </div>
        <div class="card-body">
            @if($facturas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Documento</th>
                                <th>Cliente</th>
                                <th>Valor</th>
                                <th>Fecha Vencimiento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($facturas as $factura)
                                <tr>
                                    <td>{{ $factura->id }}</td>
                                    <td>{{ $factura->documento }}</td>
                                    <td>{{ $factura->nombre_cliente }}</td>
                                    <td>$ {{ number_format($factura->valor, 0, ',', '.') }}</td>
                                    <td>{{ $factura->fecha_vencimiento ? $factura->fecha_vencimiento->format('Y-m-d') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $factura->pagada ? 'success' : 'warning' }}">
                                            {{ $factura->pagada ? 'Pagada' : 'Pendiente' }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.facturas.destroy', $factura->id) }}" class="d-inline"
                                              onsubmit="return confirm('¿Estás seguro de eliminar esta factura?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No hay facturas registradas.</p>
            @endif
        </div>
    </div>
</div>
@endsection
