<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Diagrama;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Json;
use App\Models\DiagramaReporte;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


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
        );
        $diagrama = Diagrama::crear(
            $validated['nombre'],
            $validated['descripcion']
        );
        DiagramaReporte::crear($diagrama->contenido, $diagrama->id);
        
        return Redirect::route('diagramas.show', compact('diagrama'));
    }
    public function show(Diagrama $diagrama)
    {
        // Decodificamos el JSON para pasarlo al JS
        $diagramaId = $diagrama->id;
        $ultimoReporte = DiagramaReporte::query()
            ->where('diagrama_id', $diagrama->id)
            ->latest()->first();
        $jsonInicial = json_decode($ultimoReporte->diagrama_json, true);
        return view('diagramas.uml', compact('jsonInicial', 'diagramaId'));
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
    public function diagramaReporte(Request $request)
    {
        Log::info('JSON recibido:', $request->all());
        $diagramaJson = $request->input('diagrama_json');
        $diagramaId = $request->input('diagrama_id');


        return response()->json([
            'status' => 'ok',
            'data'   => $request->all()
        ], 200);
    }

    public function destroy(Diagrama $diagrama)
    {
        $diagrama->delete();
        return Redirect::route('dashboard')->with('success', 'Diagrama eliminado correctamente.');
    }
}
