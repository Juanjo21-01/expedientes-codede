<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <div class="relative w-full h-auto" x-cloak x-data="{
            showRecoveryInput: @js($errors->has('recovery_code')),
            code: '',
            recovery_code: '',
            toggleInput() {
                this.showRecoveryInput = !this.showRecoveryInput;
        
                this.code = '';
                this.recovery_code = '';
        
                $dispatch('clear-2fa-auth-code');
        
                $nextTick(() => {
                    this.showRecoveryInput ?
                        this.$refs.recovery_code?.focus() :
                        $dispatch('focus-2fa-auth-code');
                });
            },
        }">
            <div x-show="!showRecoveryInput">
                <x-auth-header title="Código de autenticación"
                    description="Ingresa el código de autenticación proporcionado por tu aplicación autenticadora." />
            </div>

            <div x-show="showRecoveryInput">
                <x-auth-header title="Código de recuperación"
                    description="Confirma el acceso a tu cuenta ingresando uno de tus códigos de recuperación de emergencia." />
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}"
                class="card border border-base-content/10 bg-base-100/60 shadow-sm">
                @csrf

                <div class="card-body space-y-5 p-5 text-center">
                    <div x-show="!showRecoveryInput">
                        <div class="flex items-center justify-center my-5">
                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" name="code"
                                x-model="code"
                                class="input input-bordered w-full max-w-xs border-base-content/20 text-center tracking-[0.35em]"
                                placeholder="000000" />
                        </div>
                        @error('code')
                            <p class="text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="showRecoveryInput">
                        <div class="my-5">
                            <input type="text" name="recovery_code" x-ref="recovery_code"
                                x-bind:required="showRecoveryInput" autocomplete="one-time-code" x-model="recovery_code"
                                class="input input-bordered w-full border-base-content/20" />
                        </div>

                        @error('recovery_code')
                            <p class="text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-full">
                        Continuar
                    </button>
                </div>

                <div class="mt-5 pb-5 text-sm leading-5 text-center text-base-content/70">
                    <span>o puedes</span>
                    <div class="inline font-medium underline cursor-pointer text-base-content/80">
                        <span x-show="!showRecoveryInput" @click="toggleInput()">iniciar sesión con un código de
                            recuperación</span>
                        <span x-show="showRecoveryInput" @click="toggleInput()">iniciar sesión con un código de
                            autenticación</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts::auth>
