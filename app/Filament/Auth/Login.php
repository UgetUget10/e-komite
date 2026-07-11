<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    public function hasLogo(): bool
    {
        return false;
    }
}
