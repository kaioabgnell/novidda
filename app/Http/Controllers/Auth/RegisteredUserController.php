<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use App\Models\WidgetSetting;
use App\Support\Tenant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company' => ['required', 'string', 'max:255'],
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], [
            'company' => 'nome da empresa/produto',
            'name' => 'nome',
            'email' => 'e-mail',
            'password' => 'senha',
        ]);

        // Cria a conta (workspace) + usuário owner numa transação.
        $user = DB::transaction(function () use ($data) {
            $account = Account::create(['name' => $data['company']]);

            $user = User::create([
                'account_id' => $account->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'owner',
            ]);

            // Configuração padrão do widget para a conta nova.
            Tenant::set($account->id);
            WidgetSetting::create([
                'account_id' => $account->id,
                'theme' => ['accent' => '#6c5ce7', 'dark' => false],
            ]);

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
