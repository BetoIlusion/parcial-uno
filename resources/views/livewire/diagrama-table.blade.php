<div>
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
                            @forelse ($diagramas as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-200 cursor-pointer"
                                    wire:click="redirectToDiagram({{ $item->id }})">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                        {{ $item->nombre }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <span class="truncate">{{ $item->descripcion }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    {{-- ACCIONES CONFIGS --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-center space-x-6">

                                            <button wire:click="openModal({{ $item->id }})"
                                                class="flex items-center justify-center p-2 transition-colors duration-200 bg-gray-100 rounded-lg text-gray-500 hover:bg-blue-200 hover:text-blue-700 focus:outline-none"
                                                title="Lista de usuarios">

                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </button>

                                            <form action="{{ route('diagrama.destroy', $item->id) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('¿Estás seguro de eliminar este diagrama?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center justify-center p-2 transition-colors duration-200 bg-gray-100 rounded-lg text-gray-500 hover:bg-red-200 hover:text-red-700 focus:outline-none"
                                                    title="Eliminar">

                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
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
            </div>
        </div>
    </div>

    <!-- Modal para lista de usuarios -->
    @if ($showModal)
        <div
            class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 transition-opacity duration-300">
            <div
                class="relative w-96 p-6 rounded-lg bg-white border-2 border-yellow-500 shadow-[0_0_30px_rgba(255,223,0,0.6)] before:absolute before:inset-0 before:rounded-lg before:border-4 before:border-yellow-300 before:pointer-events-none">
                <div class="text-center relative z-10">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b-2 border-yellow-500 pb-2">
                        Lista de Usuarios del Diagrama
                    </h3>

                    <div class="max-h-60 overflow-y-auto mb-4">
                        @if (count($users) > 0)
                            <ul class="text-left">
                                @foreach ($users as $user)
                                    <li class="py-2 border-b border-gray-200">{{ $user->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500">No hay usuarios asociados a este diagrama.</p>
                        @endif
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="closeModal"
                            class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 font-semibold transition duration-150">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
