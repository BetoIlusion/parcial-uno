<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Diagrama;
use Illuminate\Support\Facades\Auth;
use App\Models\UsuarioDiagrama;
use App\Models\User;

class DiagramaTable extends Component
{
    public $diagramas;
    public $showModal = false;
    public $selectedDiagramId;
    public $users = [];
    public $tipoUsuario;
    public $usuariosSinRelacion = [];
    public $nombreDiagramaSeleccionado;


    public function mount($tipoUsuario = 'creador')
    {
        $this->tipoUsuario = $tipoUsuario;
        $this->diagramas = Diagrama::where('estado', true)
            ->whereHas('usuariosDiagrama', function ($query) {
                $query->where('user_id', Auth::id())
                    ->where('tipo_usuario', $this->tipoUsuario);
            })->get();
    }

    public function openModal($diagramId)
    {
        $this->selectedDiagramId = $diagramId;
        $diagrama = Diagrama::find($diagramId);
        $this->nombreDiagramaSeleccionado = $diagrama ? $diagrama->nombre : '';
        $this->loadUsersData();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedDiagramId = null;
        $this->users = [];
        $this->usuariosSinRelacion = [];
        $this->nombreDiagramaSeleccionado = null;
    }

    public function redirectToDiagram($diagramId)
    {
        return redirect()->route('diagramas.show', $diagramId);
    }

    public function agregarUsuario($userId)
    {
        try {
            UsuarioDiagrama::create([
                'user_id' => $userId,
                'diagrama_id' => $this->selectedDiagramId,
                'actividad' => 'colaborador',
                'tipo_usuario' => 'colaborador',
                'estado' => true,
            ]);

            $this->loadUsersData(); // Recarga ambas listas para reflejar el cambio.
            session()->flash('message', 'Colaborador agregado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al agregar el colaborador.');
        }
    }

    public function eliminarUsuario($userId)
    {
        try {
            UsuarioDiagrama::where('user_id', $userId)
                ->where('diagrama_id', $this->selectedDiagramId)
                ->delete();

            $this->loadUsersData(); // Recarga ambas listas para reflejar el cambio.
            session()->flash('message', 'Colaborador eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el colaborador.');
        }
    }

    private function loadUsersData()
    {
        if (!$this->selectedDiagramId) {
            return;
        }

        // Obtiene usuarios que SÍ tienen relación con el diagrama.
        $this->users = User::whereHas('usuarioDiagrama', function ($query) {
            $query->where('diagrama_id', $this->selectedDiagramId);
        })->get()->reject(function ($user) {
            return $user->id === Auth::id();
        });

        // Obtiene usuarios que NO tienen relación con el diagrama.
        $this->usuariosSinRelacion = User::whereDoesntHave('usuarioDiagrama', function ($query) {
            $query->where('diagrama_id', $this->selectedDiagramId);
        })->get()->reject(function ($user) {
            return $user->id === Auth::id();
        });
    }

    public function render()
    {
        return view('livewire.diagrama-table');
    }
}