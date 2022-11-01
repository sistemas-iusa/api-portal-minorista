<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!$token = auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['Usuario y/o contraseña incorrectos'], 401);
        }
        $user = User::where('email',$request->email)->get()->first();
        if(!$user->email_verified_at){
            return response()->json(['Confirmar dirección de correo electrónico'], 401);
        }
        return response()->json(compact('token'));
    }
}
