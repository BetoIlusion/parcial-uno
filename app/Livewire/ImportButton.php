<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;


class ImportButton extends Component
{
    use WithFileUploads;

    public $file;

    public function updatedFile()
    {
        $this->validate([
            'file' => 'file|mimes:jpg,jpeg,png,gif|max:2048', // ajusta según lo que necesites
        ]);

        // Aquí podrías procesar el archivo o guardarlo
        // $path = $this->file->store('imports');

        session()->flash('message', 'Archivo seleccionado: ' . $this->file->getClientOriginalName());
    }
    public function render()
    {
        return view('livewire.import-button');
    }
}
