<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\HistoriaClinicaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Historia clinica digital de una atencion. — RF-05 / RN-19 / RN-20
 *
 * @property int $historia_id
 * @property int|null $cita_id
 */
class HistoriaClinica extends Model
{
    /** @use HasFactory<HistoriaClinicaFactory> */
    use HasFactory;

    protected $table = 'historias_clinicas';

    protected $primaryKey = 'historia_id';

    /** La tabla tiene su propia columna de tiempo (`fecha_atencion`). */
    public $timestamps = false;

    protected $fillable = [
        'mascota_id',
        'cita_id',
        'fecha_atencion',
        'diagnostico',
        'tratamiento',
        'vacuna_aplicada',
        'proxima_fecha',
    ];

    protected $casts = [
        'fecha_atencion' => 'datetime',
        'proxima_fecha' => 'date',
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    /** Mascota a la que pertenece la historia. */
    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class, 'mascota_id', 'mascota_id');
    }

    /** Cita que origino la atencion. — RN-19 */
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_id', 'cita_id');
    }
}
