<?php

namespace Packages\User\Livewire;

use Packages\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

class Register extends Component
{
    #[Rule('required|min:3|max:255')]
    public $name = '';
    
    #[Rule('required|email|unique:users,email')]
    public $email = '';
    
    #[Rule('required|min:8')]
    public $password = '';
    
    #[Rule('required|same:password')]
    public $password_confirmation = '';

    public function register()
    {
        $this->validate();
        
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);
        
        event(new Registered($user));
        
        Auth::login($user);
        
        return redirect()->intended(route('dashboard'));
    }

    #[Title('Register')]
    public function render()
    {
        return view('user::livewire.register')
            ->layout('layouts.guest');
    }
}
