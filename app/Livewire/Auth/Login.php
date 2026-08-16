<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('ログイン - ContractFlow')]
class Login extends Component
{
    public string $email = 'demo@example.com';
    public string $password = 'password123';
    public bool $remember = true;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function login(): void
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            $this->redirectIntended(route('dashboard'));
            return;
        }

        $this->addError('email', 'メールアドレスまたはパスワードが正しくありません。');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
