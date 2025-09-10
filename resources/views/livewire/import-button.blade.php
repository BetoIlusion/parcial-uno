<div>
    <label
        class="inline-flex items-center justify-center px-4 py-2 bg-white border border-indigo-300 rounded-lg font-semibold text-sm text-indigo-700 shadow-sm
               transition-transform duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg hover:bg-indigo-50 cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
        </svg>
        Importar Imagen
        <input type="file" wire:model="file" class="hidden">
    </label>

    @if ($file)
        <p class="text-sm text-gray-500 mt-1">{{ $file->getClientOriginalName() }}</p>
    @endif

    @if (session()->has('message'))
        <p class="text-sm text-green-600 mt-1">{{ session('message') }}</p>
    @endif
</div>
<script>
    function handleImageImport(event) {
        const file = event.target.files[0];
        if (file) {
            alert(`Has seleccionado: ${file.name}`);
            // Aquí puedes agregar la lógica para enviar la imagen al backend con AJAX o un formulario
        }
    }
</script>
