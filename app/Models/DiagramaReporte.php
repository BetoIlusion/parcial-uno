<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class DiagramaReporte extends Model
{
    protected $fillable = [
        'user_id',
        'diagrama_id',
        'diagrama_json',
        'ultima_modificacion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function diagrama()
    {
        return $this->belongsTo(Diagrama::class);
    }
    public static function crear($diagramaJson, $diagramaId)
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        try {
            return static::create([
                'user_id' => $user->id,
                'diagrama_id' => $diagramaId,
                'diagrama_json' => json_encode($diagramaJson, JSON_PRETTY_PRINT),
            ]);
        } catch (\Exception $e) {
            // Manejar la excepción, por ejemplo, loggearla o relanzarla
            throw new \Exception('Error al crear el reporte del diagrama: ' . $e->getMessage());
        }
    }
}
