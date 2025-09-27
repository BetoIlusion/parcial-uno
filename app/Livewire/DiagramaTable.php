<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Diagrama;
use Illuminate\Support\Facades\Auth;


class DiagramaTable extends Component
{
    public $diagramas;
    public $showModal = false;
    public $selectedDiagramId;
    public $users = []; // Aquí almacenarías los usuarios del diagrama

    public function mount()
    {
        $this->diagramas = Diagrama::where('estado', true)
            ->whereHas('usuariosDiagrama', function ($query) {
                $query->where('user_id', Auth::id());
            })->get();
    }    


    public function openModal($diagramId)
    {
        $this->selectedDiagramId = $diagramId;

        // Aquí cargarías los usuarios relacionados con este diagrama
        // $this->users = Diagrama::find($diagramId)->users;

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedDiagramId = null;
        $this->users = [];
    }

    public function redirectToDiagram($diagramId)
    {
        return redirect()->route('diagramas.show', $diagramId);
    }

    public function render()
    {
        return view('livewire.diagrama-table');
    }
}
