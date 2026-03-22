<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    <h2 class="sr-only">Configuración de apariencia</h2>

    <x-auth.settings.layout heading="Apariencia" subheading="Personaliza la apariencia del sistema">
        <div x-data="{
            appearance: localStorage.theme ?? 'system',
            applyTheme(value) {
                this.appearance = value;
        
                if (value === 'system') {
                    localStorage.removeItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-theme', systemTheme);
                    return;
                }
        
                localStorage.theme = value;
                document.documentElement.setAttribute('data-theme', value);
            }
        }" class="space-y-4">
            <div class="rounded-box border border-base-content/10 bg-base-100 p-4 sm:p-5">
                <p class="mb-3 text-xs uppercase tracking-wide text-base-content/55">Tema de interfaz</p>
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="radio" class="radio radio-primary" name="appearance" value="light"
                            x-model="appearance" @change="applyTheme('light')">
                        <span class="label-text">Claro</span>
                    </label>
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="radio" class="radio radio-primary" name="appearance" value="dark"
                            x-model="appearance" @change="applyTheme('dark')">
                        <span class="label-text">Oscuro</span>
                    </label>
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="radio" class="radio radio-primary" name="appearance" value="system"
                            x-model="appearance" @change="applyTheme('system')">
                        <span class="label-text">Sistema</span>
                    </label>
                </div>
            </div>
        </div>
    </x-auth.settings.layout>
</section>
