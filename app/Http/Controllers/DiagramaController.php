<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Diagrama;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Json;


class DiagramaController extends Controller
{
    public function procesarImagen(Request $request) {}
    public function create()
    {
        return view('diagramas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'nombre' => 'required|string|max:255|unique:diagramas,nombre',
                'descripcion' => 'nullable|string|max:1000',
            ],
            // [
            //     'nombre.required' => 'El nombre es requerido',
            //     'nombre.string' => 'El nombre debe ser una cadena de caracteres',
            //     'nombre.max' => 'El nombre no puede tener más de 255 caracteres',
            //     'nombre.unique' => 'El nombre ya está en uso',
            //     'descripcion.string' => 'La descripción debe ser una cadena de caracteres',
            //     'descripcion.max' => 'La descripción no puede tener más de 1000 caracteres'
            // ]
        );
        $diagrama = Diagrama::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? '',
            'contenido' => json_encode(Diagrama::diagramaInicial(), JSON_PRETTY_PRINT)
        ]);
        return redirect()->route('diagramas.show', $diagrama->id)
            ->with('success', 'Diagrama creado correctamente');
    }
    public function show(Diagrama $diagrama)
    {
        // Decodificamos el JSON para pasarlo al JS
        $contenido = json_decode($diagrama->contenido, true);

        return view('diagramas.pizarra', compact('diagrama', 'contenido'));
    }
    public function updateContenido(Request $request, Diagrama $diagrama)
    {
        $validated = $request->validate([
            'data' => 'required|array'
        ]);

        $diagrama->update([
            'contenido' => json_encode($validated['data'], JSON_PRETTY_PRINT)
        ]);

        return response()->json(['message' => 'Contenido actualizado']);
    }
    public function uml()
    {
        // Modelo inicial vacío para GoJS
        $modeloInicial = [
            'class' => 'go.GraphLinksModel',
            'nodeDataArray' => [],
            'linkDataArray' => []
        ];

        // Lo codificamos a JSON
        $jsonInicial = json_encode($modeloInicial);

        return view('diagramas.uml', [
            'jsonInicial' => $jsonInicial
        ]);
    }
}
