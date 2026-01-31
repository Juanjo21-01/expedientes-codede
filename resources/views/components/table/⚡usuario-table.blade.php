<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Role;
use App\Models\User;

new class extends Component {
    // Variables
    public $usuarios;
    public $rolFiltro, $buscar;

    // Constructor
    public function mount()
    {
        $this->usuarios = $this->obtenerUsuarios();
    }

    public function obtenerUsuarios()
    {
        // return User::query()
        //     ->when(
        //         $this->buscar,
        //         fn($q) => $q->where(
        //             fn($q) => $q
        //                 ->where('nombres', 'like', "%{$this->buscar}%")
        //                 ->orWhere('apellidos', 'like', "%{$this->buscar}%")
        //                 ->orWhere('email', 'like', "%{$this->buscar}%"),
        //         ),
        //     )
        //     ->when($this->rolFiltro, fn($q) => $q->where('role_id', $this->rolFiltro))
        //     ->with('role', 'municipios')
        //     ->paginate(15);

        return User::all();
    }

    public function delete($id)
    {
        $this->authorize('delete', User::find($id));
        User::find($id)->delete();
        session()->flash('message', 'Usuario eliminado.');
    }
};
?>

<div>
    <h3 class="font-bold accent-yellow-800">Tabla Usuarios</h3>
    <div class="overflow-x-auto">
        <table class="table table-zebra">
            <thead>
                <tr
                    class="text-xs font-semibold tracking-widest text-center text-gray-500 uppercase border-b-2  dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                    <th class="px-4 py-3 w-1/12">No.</th>
                    <th class="px-4 py-3 w-3/12">Nombres</th>
                    <th class="px-4 py-3 w-3/12">Correo Electrónico</th>
                    <th class="px-4 py-3 w-2/12">Rol</th>
                    <th class="px-4 py-3 w-1/12">Estado</th>
                    <th class="px-4 py-3 w-2/12">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    <tr class="text-gray-700 dark:text-gray-400 text-center">
                        <td class="px-4 py-3 font-semibold w-1/12">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-semibold w-3/12">{{ $usuario->nombres }}
                            {{ $usuario->apellidos }}
                        </td>
                        <td class="px-4 py-3 font-semibold w-3/12">{{ $usuario->email }}</td>
                        <td class="px-4 py-3 font-semibold w-2/12">{{ $usuario->role->nombre }}</td>
                        <td class="px-4 py-3 w-1/12">
                            <button wire:click="cambiarEstado({{ $usuario->id }})"
                                class="px-4 py-2 font-semibold leading-tight rounded-full {{ $usuario->estado == 1 ? 'bg-teal-100 dark:bg-teal-700 text-teal-700 dark:text-teal-100 ' : 'bg-rose-100 dark:bg-rose-700 text-rose-700 dark:text-rose-100' }} {{ $usuario->role->nombre == 'Administrador' ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                {{ $usuario->estado == 1 ? 'Activo' : 'Inactivo' }}
                            </button>
                        <td class="px-4 py-3 w-2/12">
                            <div class="flex justify-center items-center space-x-1">
                                <a title="Ver información del usuario" href="#"
                                    class="py-1 px-2 text-purple-600 rounded-lg focus:outline-none focus:shadow-outline-gray hover:border hover:border-purple-600 border border-transparent"
                                    aria-label="Ver">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </a>
                                <button title="Editar el usuario" wire:click="editar({{ $usuario->id }})"
                                    class="py-1 px-2 text-orange-600 rounded-lg focus:outline-none focus:shadow-outline-gray hover:border hover:border-orange-600 border border-transparent"
                                    aria-label="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                                @if ($usuario->role->nombre != 'Administrador')
                                    <button title="Eliminar el usuario" wire:click="modalEliminar({{ $usuario->id }})"
                                        class="py-1 px-2 text-rose-600 rounded-lg focus:outline-none focus:shadow-outline-gray hover:border hover:border-rose-600 border border-transparent"
                                        aria-label="Eliminar">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <!-- td como antes -->
                        {{-- <td>
                            <button @click="$dispatch('open-modal', 'edit-usuario-' . {{ $usuario->id }})"
                                class="btn btn-sm btn-warning">Editar</button>
                            <button wire:click="delete({{ $usuario->id }})" wire:confirm="¿Eliminar?"
                                class="btn btn-sm btn-error">Eliminar</button>
                        </td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- {{ $usuarios->links() }} --}}

</div>
