

@extends('layouts.app')

@section('title', 'Consulta de Facturas')

@section('content')
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    <script>
        alert('{{ session('success') }}');
    </script>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    <script>
        alert('{{ session('error') }}');
    </script>
@endif

<section class="consulta-form">
    <h2>Consulta Rápida de Facturas</h2>

    <form action="{{ route('consultar') }}" method="POST">
        @csrf
        <label for="documento">Documento de Identidad:</label>
        <input type="text" id="documento" name="documento" placeholder="Ej:98765432" required>
        <button type="submit">Consultar Facturas</button>
    </form>
</section>

@if(isset($facturas))
    @include('partials.resultados', ['facturas' => $facturas])
@endif
@endsection
