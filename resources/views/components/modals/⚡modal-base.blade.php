<?php

use Livewire\Component;

new class extends Component {
    // Variables
    public $id, $title;
};
?>

<div
    class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center transition ease-out duration-150">
    <div
        class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl transform transition ease-out duration-150 ">

        <!-- Header -->
        <header class="flex justify-between px-6 py-3">
            <p
                class="text-xl font-semibold {{ $id ? 'text-amber-600 dark:text-amber-400' : 'text-teal-600 dark:text-teal-400' }}">
                {{ isset($id) ? 'Editar' : 'Agregar' }}
                {{ $title ?? 'Formulario' }} </p>
            <button
                class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover:text-gray-700  hover:border"
                wire:click="cerrarModal" aria-label="close">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </header>

        <hr class="border-gray-200 dark:border-gray-700">

        <!-- Contenido -->
        <div class="px-6 py-4">
            <!-- Componente renderizado -->
            {{ $slot }}
        </div>
    </div>
</div>
