{{-- Toast Notification System --}}
{{-- Componente global Alpine.js que escucha eventos Livewire 'mostrar-mensaje' --}}
{{-- Uso: colocar <x-toast-notification /> en el layout principal --}}

<div x-data="{
    toasts: [],
    init() {
        const initialToast = @js(session('toast'));

        if (initialToast) {
            this.addToast(initialToast);
        }

        if (window.Livewire && typeof Livewire.on === 'function') {
            Livewire.on('mostrar-mensaje', (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                this.addToast(payload);
            });
        }
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
    },
    getTitle(tipo) {
        return {
            success: 'Operacion completada',
            warning: 'Atencion',
            error: 'Error',
            info: 'Informacion'
        } [tipo] ?? 'Notificacion';
    }
}" class="toast toast-bottom toast-center sm:toast-end z-100 pointer-events-none">

    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="alert relative isolate w-[calc(100vw-1.5rem)] max-w-md overflow-hidden rounded-2xl border border-base-content/10 bg-base-100/95 shadow-xl backdrop-blur pointer-events-auto"
            :class="{
                'text-success': toast.tipo === 'success',
                'text-warning': toast.tipo === 'warning',
                'text-error': toast.tipo === 'error',
                'text-info': toast.tipo === 'info'
            }"
            role="alert" aria-live="polite">

            <span class="absolute inset-x-0 top-0 h-0.5 opacity-70"
                :class="{
                    'bg-success': toast.tipo === 'success',
                    'bg-warning': toast.tipo === 'warning',
                    'bg-error': toast.tipo === 'error',
                    'bg-info': toast.tipo === 'info'
                }"></span>

            {{-- Icono según tipo --}}
            <div x-show="toast.tipo === 'success'" class="shrink-0 rounded-xl bg-success/15 p-1.5">
                <x-heroicon-o-check-circle class="w-5 h-5 text-success" />
            </div>
            <div x-show="toast.tipo === 'warning'" class="shrink-0 rounded-xl bg-warning/15 p-1.5">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-warning" />
            </div>
            <div x-show="toast.tipo === 'error'" class="shrink-0 rounded-xl bg-error/15 p-1.5">
                <x-heroicon-o-x-circle class="w-5 h-5 text-error" />
            </div>
            <div x-show="toast.tipo === 'info'" class="shrink-0 rounded-xl bg-info/15 p-1.5">
                <x-heroicon-o-information-circle class="w-5 h-5 text-info" />
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-base-content/55"
                    x-text="getTitle(toast.tipo)"></p>
                <p x-text="toast.mensaje" class="mt-0.5 line-clamp-3 text-sm text-base-content/90"></p>
            </div>

            <button @click="removeToast(toast.id)"
                class="btn btn-ghost btn-xs btn-circle shrink-0 text-base-content/60 hover:text-base-content">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        </div>
    </template>
</div>
