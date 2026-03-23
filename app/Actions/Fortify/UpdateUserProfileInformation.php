<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string|null>  $input
     */
    public function update(User $user, array $input): void
    {
        $validated = Validator::make($input, [
            'nombres' => ['required', 'string', 'max:50'],
            'apellidos' => ['required', 'string', 'max:50'],
            'cargo' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'regex:/^[0-9]{8}$/'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ], [
            'telefono.regex' => 'El telefono debe tener exactamente 8 digitos numéricos.',
        ])->validateWithBag('updateProfileInformation');

        $validated['nombres'] = trim($validated['nombres']);
        $validated['apellidos'] = trim($validated['apellidos']);
        $validated['cargo'] = isset($validated['cargo']) && $validated['cargo'] !== '' ? trim($validated['cargo']) : null;
        $validated['telefono'] = isset($validated['telefono']) && $validated['telefono'] !== '' ? trim($validated['telefono']) : null;
        $validated['email'] = mb_strtolower(trim($validated['email']));

        if ($validated['email'] !== $user->email) {
            $user->forceFill([
                'nombres' => $validated['nombres'],
                'apellidos' => $validated['apellidos'],
                'cargo' => $validated['cargo'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'email' => $validated['email'],
                'email_verified_at' => null,
            ])->save();

            return;
        }

        $user->forceFill([
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'cargo' => $validated['cargo'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'email' => $validated['email'],
        ])->save();
    }
}
