<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'domain' => ['required']
        ]);

        // registrar o tenant no schema public
        $domain = $request->get('domain');
        Tenant::create(['name' => $domain]);

        // criar o novo schema
        DB::connection('pgsql')->statement("CREATE SCHEMA {$domain}");

        // muda para o schema do usuario
        DB::connection('pgsql')->statement("SET search_path TO {$domain}");

        // migrar as tabelas para o novo schema
        try {
            Artisan::call("tenant:migrate", [
                '--schema' => $domain,
                '--install' => true
            ]);

            // registrar o usuario no schema do tenant
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            event(new Registered($user));

            Auth::login($user);

            return redirect($request->getScheme() . "://" . $domain . '.' . $request->getHttpHost() . RouteServiceProvider::HOME);

        }catch (\Exception $exception) {

            DB::connection('pgsql')->statement("DROP SCHEMA {$domain}");
            DB::connection('pgsql')->statement("SET search_path TO public");

            Tenant::on()->where('name', $domain)->delete();

            dd($exception);
        }
    }
}
