<?php
namespace App\Livewire;

use Livewire\Component;


class DiagramList extends Component
{
    public $diagrams = [];

    public function mount()
    {
        // Datos de prueba - en una aplicación real estos vendrían de una base de datos
        $this->diagrams = [
            [
                'id' => 1,
                'name' => 'Flujo de Autenticación de Usuarios',
                'description' => 'Diagrama que detalla el proceso de inicio de sesión, registro y recuperación de contraseña.',
            ],
            [
                'id' => 2,
                'name' => 'Arquitectura de Microservicios',
                'description' => 'Vista general de la arquitectura del sistema y cómo se comunican sus componentes.',
            ],
            [
                'id' => 3,
                'name' => 'Modelo Entidad-Relación de la Base de Datos',
                'description' => 'Estructura de la base de datos, incluyendo todas las tablas, campos y sus relaciones.',
            ],
            [
                'id' => 4,
                'name' => 'Proceso de Compra del E-commerce',
                'description' => 'Flujo completo desde que un usuario añade un producto al carrito hasta que finaliza el pago.',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.diagram-list');
    }

    // Métodos para las acciones (preview, edit, delete, etc.)
    public function preview($id)
    {
        // Lógica para vista previa
        session()->flash('message', 'Vista previa del diagrama #' . $id);
    }

    public function edit($id)
    {
        // Lógica para editar
        session()->flash('message', 'Editando diagrama #' . $id);
    }

    public function delete($id)
    {
        // Lógica para eliminar
        session()->flash('message', 'Eliminando diagrama #' . $id);
    }
}
