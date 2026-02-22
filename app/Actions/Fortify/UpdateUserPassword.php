<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the given user's password.
     *
     * @param  array<string, string>  $input
     */
    public function update($user, array $input): void
    {
        Validator::make($input, [
            'current_password' => $this->currentPasswordRules(),
            'password' => $this->passwordRules(),
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');

        $user->forceFill([
            'password' => $input['password'],
        ])->save();
    }
}
