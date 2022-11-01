<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Profile;
use Mail;
use App\Mail\ConfirmEmailMailable;
use App\Mail\ForgottenPasswordEmailMailable;
use Validator;

class RegistrationController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'email' => 'required|unique:users',
            'verify_email' => 'required|same:email',
            'password' => 'required|min:8',
            'telephone' => 'required|min:10',
            'birthday' => 'required',
            'photo' => 'mimes:jpeg,jpg,png|max:1024',
            'whatsapp' => 'required',
            'share_personal_data' => 'required',
            'marketing_messages' => 'required',
        ],
        $messages = [
            'required' => 'El campo :attribute es obligatorio.',
            'same' => 'El campo :attribute debe ser igual a email.',
            'min' => 'El campo :attribute debe de tener minimo :min caracteres'
        ]);
        if ($validator->fails()) {
            $data_arr = ["errors" => $validator->errors()->first()];
            return response()->json($data_arr, 401);
        }
        $user = new User;
        $user->name = $request->firstname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        $profile = new Profile;
        $profile->name = $request->firstname;
        $profile->telephone = $request->telephone;
        $profile->birthday = date('Y-m-d', strtotime($request->birthday));
        $profile->whatsapp = $request->whatsapp;
        $profile->share_personal_data = $request->share_personal_data;
        $profile->marketing_messages = $request->marketing_messages;
        if ($request->file('photo')) {
            $file = $request->file('photo');
            $image_url = 'img/users/' . $user->email . "/" . time() . $file->getClientOriginalName();
            $path = public_path() . '/img/users/' . $user->email;
            $file->move($path, $image_url);
            $profile->image_url = $image_url;
        } else {
            $profile->image_url = "";
        }
        $user->profile()->save($profile);
        $email = $user->email;
        Mail::to($email)->send(new ConfirmEmailMailable($user));
        return response()->json([
            'name' => $user->name,
            'email' => $user->email
        ]);
    }

    public function confirmEmail($request)
    {
        $user = User::find($request);
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        return redirect('https://dev-portal-minorista.iusa.com.mx/#/');
    }

    public function forgottenPassword(Request $request)
    {
        $user = User::where('email', $request->email)->get();
        $countUser = count($user);
        if ($countUser == 0) {
            $msg = 'El email ingresado no se encuentra en nuestros registros.';
            return response()->json(['error' => $msg], 200);
        } else {
            $user = $user[0];
            $email = $user->email;
            //$email_o = "omartinez@iusa.com.mx";
            Mail::to($email)->send(new ForgottenPasswordEmailMailable($user));
            return response()->json('Correo de recuperar contraseña enviado.', 200);
        }
    }

    public function newPassword($request)
    {
        $code = base64_encode($request);
        return redirect('https://dev-portal-minorista.iusa.com.mx/#/recover-password/'.$code);
    }

    public function confirmPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8',
            'confirm_password' => 'required|min:8|same:password',
        ],
        $messages = [
            'required' => 'El campo :attribute es obligatorio.',
            'same' => 'Las contraseñas no coinciden',
            'min' => 'El campo :attribute debe de tener como mínimo :min caracteres'
        ]);
        if ($validator->fails()) {
            $data_arr = ["errors" => $validator->errors()->first()];
            return response()->json($data_arr, 401);
        }
        $undecode = base64_decode($request->id);
        $user = User::find($undecode);
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json('Contraseña actualizada', 200);
    }
}
