<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Json;
use App\Models\DiagramaReporte;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Diagrama;
use App\Models\UsuarioDiagrama;

class DiagramaController extends Controller
{
    public function procesarImagen(Request $request) {}
    public function create()
    {
        return view('diagramas.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:diagramas,nombre',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $diagrama = Diagrama::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'contenido' => json_encode(Diagrama::diagramaInicial(), JSON_PRETTY_PRINT)
        ]);

        // Asegúrate de pasar los datos correctamente
        DiagramaReporte::crear($user->id, $diagrama->id, Diagrama::diagramaInicial());

        UsuarioDiagrama::crearRelacion($user->id, $diagrama->id, 'creando diagrama', 'creador');
        return Redirect::route('diagramas.show', compact('diagrama'));
    }
    public function show(Diagrama $diagrama)
    {
        // Decodificamos el JSON para pasarlo al JS
        $diagramaId = $diagrama->id;
        $ultimoReporte = DiagramaReporte::query()
            ->where('diagrama_id', $diagrama->id)
            ->latest()->first();
        $jsonInicial = json_decode($ultimoReporte->contenido, true);
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
        $modeloInicial = [
            'class' => 'go.GraphLinksModel',
            'nodeDataArray' => [],
            'linkDataArray' => []
        ];
        $jsonInicial = json_encode($modeloInicial);
        return view('diagramas.uml', [
            'jsonInicial' => $jsonInicial
        ]);
    }
    public function diagramaReporte(Request $request)
    {
        $user = Auth::user();
        Log::info('JSON recibido:', $request->all());
        $diagramaJson = $request->input('diagramData');
        $diagramaId = $request->input('diagramaId');
        DiagramaReporte::crear($user->id, $diagramaId, $diagramaJson);
    }

    public function destroy(Diagrama $diagrama)
    {
        $diagrama->delete();
        return Redirect::route('dashboard')->with('success', 'Diagrama eliminado correctamente.');
    }
}
