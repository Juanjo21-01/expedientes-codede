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
        Validator::make($input, [
            'nombres' => ['required', 'string', 'max:50'],
            'apellidos' => ['required', 'string', 'max:50'],
            'cargo' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:8'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ])->validateWithBag('updateProfileInformation');

        if ($input['email'] !== $user->email) {
            $user->forceFill([
                'nombres' => $input['nombres'],
                'apellidos' => $input['apellidos'],
                'cargo' => $input['cargo'] ?? null,
                'telefono' => $input['telefono'] ?? null,
                'email' => $input['email'],
                'email_verified_at' => null,
            ])->save();

            return;
        }

        $user->forceFill([
            'nombres' => $input['nombres'],
            'apellidos' => $input['apellidos'],
            'cargo' => $input['cargo'] ?? null,
            'telefono' => $input['telefono'] ?? null,
            'email' => $input['email'],
        ])->save();
    }
}
