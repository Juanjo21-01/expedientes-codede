<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['auth', 'usuario_activo'])->group(function () {
	Route::redirect('settings', 'settings/profile');

	Route::livewire('settings/profile', 'auth::settings.profile')->name('profile.edit');
});

Route::middleware(['auth', 'usuario_activo'])->group(function () {
	Route::livewire('settings/password', 'auth::settings.password')->name('user-password.edit');
	Route::livewire('settings/appearance', 'auth::settings.appearance')->name('appearance.edit');

	Route::livewire('settings/two-factor', 'auth::settings.two-factor')
		->middleware(
			when(
				Features::canManageTwoFactorAuthentication()
				&& Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
				['password.confirm'],
				[],
			),
		)
		->name('two-factor.show');
});
