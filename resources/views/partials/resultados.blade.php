<section class="resultados-tabla">
    <h2>Facturas Pendientes de Pago</h2>

    @if($facturas->isEmpty())
        <p>No se encontraron facturas pendientes.</p>
    @else
        <form action="{{ route('pagar.facturas') }}" method="POST" id="formPagar">
            @csrf
            <table>
                <thead>
                    <tr>
                        <th></th> {{-- Checkbox --}}
                        <th>Nro. Factura</th>
                        <th>Nombre</th>
                        <th>Fecha</th>
                        <th>Monto Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($facturas as $factura)
                        <tr>
                            <td>
                                @if ($factura->pagada == 0)
                                    <input 
                                        type="checkbox" 
                                        name="facturas[]" 
                                        value="{{ $factura->id }}" 
                                        class="check-factura" 
                                        data-valor="{{ $factura->valor }}">
                                @endif
                            </td>
                            <td>{{ $factura->id }}</td>
                            <td>{{ $factura->nombre_cliente }}</td>
                            <td>{{ \Carbon\Carbon::parse($factura->fecha_vencimiento)->format('d/m/Y') }}</td>
                            <td>$ {{ number_format($factura->valor, 0, ',', '.') }}</td>
                            <td>
                                @if ($factura->pagada == 1)
                                    <span class="estado pagada">Pagada</span>
                                @else
                                    <span class="estado pendiente">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="acciones">
                <p><strong>Total seleccionado:</strong> $ <span id="totalSeleccionado">0</span></p>
                <button type="submit" id="btnPagar" disabled> Pagar seleccionadas</button>
            </div>
        </form>
    @endif
</section>

{{-- Script para recalcular total dinámicamente --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.check-factura');
    const totalSpan = document.getElementById('totalSeleccionado');
    const btnPagar = document.getElementById('btnPagar');

    function actualizarTotal() {
        let total = 0;
        let algunaSeleccionada = false;

        checkboxes.forEach(chk => {
            if (chk.checked) {
                total += parseFloat(chk.dataset.valor);
                algunaSeleccionada = true;
            }
        });

        totalSpan.textContent = total.toLocaleString('es-CO');
        btnPagar.disabled = !algunaSeleccionada;
    }

    checkboxes.forEach(chk => chk.addEventListener('change', actualizarTotal));
});
</script>
