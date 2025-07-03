<?php

namespace App\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Auth\Events\Login as LoginEvent;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;

class LoginPage extends BaseLogin
{
    public function getHeading(): string | Htmlable
    {
        return "Assalamu'alaikum";
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Masukkan akun Anda untuk melanjutkan.';
    }
    
    // ... sisa kode Anda (getForms, getEmailFormComponent) tetap sama ...
    /**
     * Menambahkan branding kustom di bawah form.
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }
    
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }
}
