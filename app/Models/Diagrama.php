<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UsuarioDiagrama;
use Illuminate\Support\Facades\Auth;


class Diagrama extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'descripcion',
        'contenido',
    ];

    // protected $hidden = [
    //     'contenido',
    // ];

    // protected $appends = [
    //     'contenido',
    // ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    // protected function casts(): array
    // {
    //     return [
    //         'contenido' => 'json',
    //     ];
    // }



    public static function diagramaInicial(): array
    {
        return [
            'cells' => [
                [
                    'type' => 'standard.HeaderedRectangle',
                    'position' => ['x' => 100, 'y' => 100],
                    'size' => ['width' => 160, 'height' => 100],
                    'attrs' => [
                        'header' => ['label' => ['text' => 'Class1']],
                        'body' => ['label' => ['text' => '+ atributo: Tipo']]
                    ],
                    'id' => 'class1'
                ],
                [
                    'type' => 'standard.HeaderedRectangle',
                    'position' => ['x' => 400, 'y' => 100],
                    'size' => ['width' => 160, 'height' => 100],
                    'attrs' => [
                        'header' => ['label' => ['text' => 'Class2']],
                        'body' => ['label' => ['text' => '+ atributo: Tipo']]
                    ],
                    'id' => 'class2'
                ],
                [
                    'type' => 'standard.Link',
                    'source' => ['id' => 'class1'],
                    'target' => ['id' => 'class2'],
                    'attrs' => [
                        'line' => [
                            'stroke' => '#000',
                            'strokeWidth' => 2,
                            'targetMarker' => ['type' => 'path', 'd' => 'M 10 -5 0 0 10 5 z']
                        ]
                    ]
                ]
            ]
        ];
    }
    public static function crear($nombre, $descripcion)
    {

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        $diagrama = static::create([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'contenido' => json_encode(static::diagramaInicial(), JSON_PRETTY_PRINT)
        ]);

        UsuarioDiagrama::crearRelacion($user->id, $diagrama->id, 'creando diagrama', 'creador');
        return $diagrama;
    }
}
