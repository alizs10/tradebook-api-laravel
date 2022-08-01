<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Services\EmailServices;
use App\Services\Image\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        if ($user) {
            return response([
                'user' => $user
            ], 200);
        }

        return response([
            'message' => 'something went wrong, try again'
        ], 500);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ImageService $imageService)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|min:5',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'required|digits:11|unique:users,mobile,' . $user->id,
            'profile_photo_path' => 'nullable|image|mimes:png,jpg,jpeg,webp'
        ]);

        $inputs = $request->only(['name', 'email', 'mobile']);

        if ($request->hasFile('profile_photo_path')) {

            $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'avatars');
            $imagePath = $imageService->fitAndSave($request->file('profile_photo_path'), 600, 600);
            $inputs['profile_photo_path'] = $imagePath;
        }

        if ($user->email !== $request->email) {
            $inputs['email_verified_at'] = null;
            $inputs['verification_code'] = null;
        }

        $result = $user->update($inputs);

        if ($result) {
            return response([
                'message' => 'profile updated successfully',
                'user' => $user
            ], 200);
        }

        return response([
            'message' => 'something went wrong, try again'
        ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function sendVerificationCode()
    {

        $user = Auth::user();

        $verification_code = Str::random(8);
        $user->update(['verification_code' => $verification_code]);

        EmailServices::SendVCode($user->email, $verification_code);

        return response([
            'message' => 'verification code sent'
        ], 200);
    }

    public function checkVerificationCode(Request $request)
    {

        $user = Auth::user();

        $request->validate([
            'verification_code' => 'required|string|size:8'
        ]);

        $verification_code = $user->verification_code;

        if ($verification_code === $request->verification_code) {
            $user->update(['email_verified_at' => now(), 'verification_code' => null]);

            return response([
                'message' => 'user account activated successfully',
                'user' => $user
            ], 200);
        }

        return response([
            'message' => 'verification code is wrong'
        ], 422);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'old_password' =>  ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'max:16'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'max:16', 'confirmed'],
            'password_confirmation' => 'required|same:password'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response([
                'message' => 'old password in wrong',
                'status' => false
            ], 200);
        }

        $result = $user->update(['password' => Hash::make($request->password)]);

        if ($result) {
            return response([
                'message' => 'password changed successfully',
                'status' => true
            ], 200);
        }

        return response([
            'message' => 'something went wrong, try again'
        ], 500);
    }

    public function forgotPassword()
    {
        $user = Auth::user();

        $newPass = Str::random(16);
        $result = DB::transaction(function () use($user, $newPass) {
            $user->update(['password' => Hash::make($newPass)]);

            EmailServices::SendNewPassword($user->email, $newPass);
        });

        if (!is_null($result)) {
            return response([
                'message' => 'something went wrong',
                'status' => false,
                'res' => $result
            ], 200);
        }

        return response([
            'message' => 'new password sent',
            'status' => true
        ], 200);
    }
}
