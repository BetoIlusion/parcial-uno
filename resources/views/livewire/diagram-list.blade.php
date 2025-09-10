<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session()->has('message'))
            <div class="mb-4 px-4 py-3 leading-normal text-blue-700 bg-blue-100 rounded-lg" role="alert">
                <p>{{ session('message') }}</p>
            </div>
        @endif

        {{-- Contenedor principal para la lista de diagramas --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($diagrams as $diagram)
                    <div class="p-6 flex items-center justify-between transition ease-in-out duration-150 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">

                        {{-- Sección de Información del Diagrama --}}
                        <div class="flex items-center space-x-6 flex-1">
                            <!-- ID destacado en un círculo -->
                            <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300">
                                <span class="text-base font-medium">{{ $diagram['id'] }}</span>
                            </div>

                            <!-- Nombre y Descripción -->
                            <div class="flex-1">
                                <h3 class="text-base font-medium text-gray-900 dark:text-white leading-6">
                                    {{ $diagram['name'] }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-5">
                                    {{ $diagram['description'] }}
                                </p>
                            </div>
                        </div>

                        {{-- Sección de Acciones --}}
                        <div class="flex items-center space-x-2 ml-4">
                            <!-- Botones de acción con tooltips y un estilo más sutil -->
                            <button title="Lista de Usuarios"
                                class="p-2 rounded-full text-gray-400 hover:text-blue-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                </svg>
                            </button>
                            <button wire:click="preview({{ $diagram['id'] }})" title="Vista Previa"
                                class="p-2 rounded-full text-gray-400 hover:text-green-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd"
                                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.022 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button wire:click="edit({{ $diagram['id'] }})" title="Editar"
                                class="p-2 rounded-full text-gray-400 hover:text-yellow-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                    <path fill-rule="evenodd"
                                        d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button wire:click="delete({{ $diagram['id'] }})" title="Eliminar"
                                class="p-2 rounded-full text-gray-400 hover:text-red-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    {{-- Mensaje para cuando no haya diagramas --}}
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        <p>No tienes diagramas guardados todavía. ¡Crea uno para empezar!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>