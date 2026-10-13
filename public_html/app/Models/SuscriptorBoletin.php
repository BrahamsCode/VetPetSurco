<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Suscriptor del boletin informativo.
 */
final class SuscriptorBoletin extends Model
{
    protected $table = 'suscriptores_boletin';

    protected $primaryKey = 'suscriptor_id';

    public $timestamps = false;

    protected $fillable = ['correo', 'nombre', 'token_baja', 'activo', 'fecha_alta', 'fecha_baja'];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_alta' => 'datetime',
        'fecha_baja' => 'datetime',
    ];
}
