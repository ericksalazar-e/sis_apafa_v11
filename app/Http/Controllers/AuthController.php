<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $empresas = Empresa::all();
        $currentTenant = Empresa::find(session('empresa_id') ?? 1) ?? $empresas->first();

        return view('auth.login', compact('empresas', 'currentTenant'));
    }

    /**
     * Procesa el inicio de sesión.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Por favor ingrese su usuario o correo institucional.',
            'password.required' => 'Por favor ingrese su contraseña de acceso.',
        ]);

        $loginInput = trim($credentials['login']);
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Buscar el usuario sin restricciones de tenant para permitir login institucional
        $user = User::withoutGlobalScopes()->where($field, $loginInput)->first();

        if (!$user) {
            return back()->withInput()->withErrors([
                'login' => 'El usuario o correo electrónico no se encuentra registrado en el sistema.',
            ])->with('error', 'Usuario no registrado. Verifique los datos ingresados.');
        }

        if (!Auth::validate([$field => $loginInput, 'password' => $credentials['password']])) {
            return back()->withInput()->withErrors([
                'password' => 'La contraseña ingresada no es válida.',
            ])->with('error', 'Contraseña incorrecta. Intente de nuevo o consulte con el Administrador.');
        }

        if ($user->estado != 1) {
            return back()->withInput()->withErrors([
                'login' => 'Su cuenta se encuentra inactiva o deshabilitada temporalmente.',
            ])->with('error', 'Acceso denegado: Su cuenta está inactiva. Contacte a la Presidencia de la APAFA.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Establecer la empresa institucional en la sesión (Sistema para una sola IIEE)
        session(['empresa_id' => $user->empresa_id ?? 1]);

        // Redirección inteligente según el rol
        if ($user->isVocalOperador()) {
            return redirect()->route('asistencia.estudiantes.index')
                ->with('success', "¡Bienvenido(a), {$user->name}! Terminal de escaneo de portería iniciada con éxito.");
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', "¡Bienvenido al SIS-APAFA, {$user->name}! Has ingresado con el rol de {$user->rol_nombre}.");
    }

    /**
     * Cierra la sesión activa.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Ha cerrado sesión de forma segura.');
    }

    /**
     * Inicio de sesión rápido para demostración de roles en desarrollo.
     */
    public function quickLogin($rol)
    {
        $rolUpper = strtoupper($rol);

        // Priorizar usuarios oficiales de la institución (empresa_id = 1)
        $user = User::withoutGlobalScopes()
            ->where('rol', $rolUpper)
            ->where('empresa_id', 1)
            ->where('username', '!=', 'admin2')
            ->first()
            ?? User::withoutGlobalScopes()->where('rol', $rolUpper)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => "No se encontró ningún usuario con el rol {$rol}."]);
        }

        Auth::login($user);
        session(['empresa_id' => 1]); // Bloqueado a la I.E. oficial única

        if ($user->isVocalOperador()) {
            return redirect()->route('asistencia.estudiantes.index')
                ->with('success', "Acceso Demostración: Sesión iniciada como {$user->rol_nombre}.");
        }

        return redirect()->route('dashboard')
            ->with('success', "Acceso Demostración: Sesión iniciada como {$user->name} ({$user->rol_nombre}).");
    }
}
