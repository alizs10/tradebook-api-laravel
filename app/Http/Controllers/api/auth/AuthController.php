<?php

namespace App\Http\Controllers\api\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as FacadesPassword;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);


        $credentials = $request->only(['email', 'password']);
        $result = Auth::attempt($credentials);

        if ($result) {
            $token = Auth::user()->createToken('login')->plainTextToken;
            return response([
                'message' => 'successfully logged in',
                'user' => Auth::user(),
                'token' => $token
            ], 200);
        }

        return response([
            'message' => 'email or password is wrong'
        ], 401);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:5|max:90',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|digits:11,unique:users,mobile',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'max:16', 'confirmed']
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password)
        ]);

        if ($user) {
            return response([
                'message' => 'user created successfully',
                'user' => $user
            ], 201);
        }

        return response([
            'message' => 'something went wrong, try again'
        ], 500);
    }

    public function logout()
    {
        $result = Auth::user()->tokens()->delete();
        if ($result) {
            return response([
                'message' => 'successfully logged out'
            ], 200);
        }

        return response([
            'message' => 'something went wrong, try again'
        ], 500);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = FacadesPassword::sendResetLink(
            $request->only('email')
        );


        if ($status === FacadesPassword::RESET_LINK_SENT) {
            return response([
                'message' => 'reset password link is sent successfully',
                'status' => true
            ], 200);
        }

        return response([
            'message' => 'something went wrong',
            'status' => false
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'max:16', 'confirmed']
        ]);

        $status = FacadesPassword::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === FacadesPassword::PASSWORD_RESET) {
            return response([
                'message' => 'password changed successfully',
                'status' => true
            ], 200);
        }

        return response([
            'message' => 'something went wrong',
            'status' => false
        ], 200);
    }
}
