{{-- Toast Notification System --}}
{{-- Componente global Alpine.js que escucha eventos Livewire 'mostrar-mensaje' --}}
{{-- Uso: colocar <x-toast-notification /> en el layout principal --}}

<div x-data="{
    toasts: [],
    init() {
        Livewire.on('mostrar-mensaje', (data) => {
            const payload = Array.isArray(data) ? data[0] : data;
            this.addToast(payload);
        });
    },
    addToast({ tipo = 'info', mensaje = '' }) {
        const id = Date.now() + Math.random();
        this.toasts.push({ id, tipo, mensaje, visible: true });
        setTimeout(() => this.removeToast(id), 5000);
    },
    removeToast(id) {
        const index = this.toasts.findIndex(t => t.id === id);
        if (index > -1) {
            this.toasts[index].visible = false;
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 300);
        }
    }
}" class="toast toast-bottom toast-end z-[100] pointer-events-none">

    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8" class="alert shadow-lg pointer-events-auto max-w-sm"
            :class="{
                'alert-success': toast.tipo === 'success',
                'alert-warning': toast.tipo === 'warning',
                'alert-error': toast.tipo === 'error',
                'alert-info': toast.tipo === 'info'
            }"
            role="alert">

            {{-- Icono según tipo --}}
            <div x-show="toast.tipo === 'success'" class="shrink-0">
                <x-heroicon-o-check-circle class="w-5 h-5" />
            </div>
            <div x-show="toast.tipo === 'warning'" class="shrink-0">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
            </div>
            <div x-show="toast.tipo === 'error'" class="shrink-0">
                <x-heroicon-o-x-circle class="w-5 h-5" />
            </div>
            <div x-show="toast.tipo === 'info'" class="shrink-0">
                <x-heroicon-o-information-circle class="w-5 h-5" />
            </div>

            <span x-text="toast.mensaje" class="text-sm"></span>

            <button @click="removeToast(toast.id)" class="btn btn-sm btn-circle btn-ghost shrink-0">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        </div>
    </template>
</div>
