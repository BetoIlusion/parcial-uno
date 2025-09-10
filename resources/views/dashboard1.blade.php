<x-app-layout>


    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <div class="flex items-center">
                {{-- Boton importar con su ubicacion establecida--}}
                <livewire:import-button/>
                {{-- Boton Crear Diagrama con su ubicacion establecida --}}
                <livewire:create-diagram />
            </div>
        </div>
    </x-slot>


    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col"
                                    class="w-1/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    ID
                                </th>
                                <th scope="col"
                                    class="w-3/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Nombre
                                </th>
                                <th scope="col"
                                    class="w-4/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th scope="col"
                                    class="w-2/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Fecha de creación
                                </th>
                                <th scope="col"
                                    class="w-2/12 px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($diagrama as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                        {{ $item->nombre }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{-- Para evitar que textos muy largos rompan el diseño, puedes usar 'truncate' --}}
                                        <span class="truncate">{{ $item->descripcion }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-center space-x-4">
                                            <a href="#" class="text-gray-400 hover:text-blue-600"
                                                title="Ver detalles">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                    <path fill-rule="evenodd"
                                                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.022 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                            <a href="#" class="text-gray-400 hover:text-green-600" title="Editar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path
                                                        d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                    <path fill-rule="evenodd"
                                                        d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                            <form action="#" method="POST" class="inline"
                                                onsubmit="return confirm('¿Estás seguro de eliminar este diagrama?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-red-600"
                                                    title="Eliminar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No hay diagramas registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $diagrama->links() }}
            </div> --}}
            </div>
        </div>
    </div>
</x-app-layout>
