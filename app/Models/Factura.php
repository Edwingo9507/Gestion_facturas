<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = 'facturas';

    protected $fillable = [
        'documento',
        'nombre_cliente',
        'valor',
        'fecha_vencimiento',
        'estado',
        'pagada',
    ];

    protected $casts = [
        'pagada' => 'boolean',
        'fecha_vencimiento' => 'date',
        'valor' => 'decimal:2',
    ];

    /**
     * Get the estado attribute
     */
    public function getEstadoAttribute()
    {
        return $this->pagada ? 'pagada' : 'pendiente';
    }

    /**
     * Set the estado attribute
     */
    public function setEstadoAttribute($value)
    {
        $this->pagada = in_array(strtolower($value), ['pagada', '1', 'true']);
    }
}
