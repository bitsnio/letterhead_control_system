<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\CustomClasses\LdapService;
use Illuminate\Support\Facades\Log;

class Login extends BaseLogin
{
    /**
     * 1. Define the form with 'username'
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getUsernameFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getUsernameFormComponent()
    {
        return TextInput::make('username')
            ->label('Username')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * 2. Custom Authentication Flow (LDAP -> Local Fallback)
     */
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        $username = trim($data['username']);
        $password = $data['password'];
        $remember = $data['remember'] ?? false;

        $user = User::where('email', $username)->first();

        // 1️⃣ LDAP authentication
        $ldapAuthenticated = false;

        try {
            // IMPORTANT: suppress PHP warnings
            $ldapAuthenticated = @LdapService::AuthenticateUser($username, $password);
        } catch (\Throwable $e) {
            // NEVER throw in Livewire auth
            logger()->error('LDAP exception', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            $ldapAuthenticated = false;
        }

        // 2️⃣ If LDAP passed and user exists → login
        if ($ldapAuthenticated && $user) {
            \Filament\Facades\Filament::auth()->login($user, $remember);
            session()->regenerate();

            return app(LoginResponse::class);
        }

        // 3️⃣ Local DB fallback
        if (Auth::attempt(['email' => $username, 'password' => $password], $remember)) {
            return $this->handleSuccess();
        }

        // 4️⃣ Unified failure
        $this->throwFailureValidationException();
    }


    /**
     * Helper to handle post-login logic (session and redirect)
     */
    protected function handleSuccess(): LoginResponse
    {
        $user = Auth::user();

        // Ensure user is allowed to access the specific panel
        if (
            ! ($user instanceof \Filament\Models\Contracts\FilamentUser) ||
            ! $user->canAccessPanel(\Filament\Facades\Filament::getCurrentOrDefaultPanel())
        ) {
            Auth::logout();
            $this->throwFailureValidationException();
        }

        session()->regenerate();
        return app(LoginResponse::class);
    }

    /**
     * Map the error specifically to the 'username' field
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
