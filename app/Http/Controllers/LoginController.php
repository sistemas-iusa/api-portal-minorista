<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    //
    public function __invoke(Request $request)
    {
        if (!$token = auth()->attempt($request->only('email', 'password'))) {
            //return response('Wrong authentication', 401);
            return response()->json(['Wrong authentication'], 401);
        }
        return response()->json(compact('token'));
    }
}
