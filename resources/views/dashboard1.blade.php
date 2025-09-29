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
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800">Mis Diagramas</h1>
                </div>

    @livewire('diagrama-table', ['tipoUsuario' => 'creador'])

                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800">Colaborador</h1>
                </div>

    @livewire('diagrama-table', ['tipoUsuario' => 'colaborador'])

</x-app-layout>
