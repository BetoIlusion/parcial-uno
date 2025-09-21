<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{-- {{ __('Dashboard') }} --}}
            </h2>
            <div class="flex items-center">
                {{-- Boton importar con su ubicacion establecida --}}
                @livewire('import-button')
                {{-- Boton Crear Diagrama con su ubicacion establecida --}}
                @livewire('create-diagram')
            </div>
        </div>
    </x-slot>
    @livewire('diagrama-table')
</x-app-layout>
