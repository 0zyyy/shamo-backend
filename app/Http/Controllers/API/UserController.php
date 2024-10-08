<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Exception;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'phone' => ['nullable', 'string', 'max:255'],
                'password' => ['required', 'string', new Password],
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
            ]);
            $user = User::where('email',$request->email)->first();

            $token = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ],'User Registered!');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Somehing went wrong',
                'error' => $error
            ],'Authentication failed', 500);
        }
    }
    
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $credentials = request(['email','password']);
            if(!Auth::attempt($credentials))
            {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ],'Authentication failed',500);
            }

            $user = User::where('email',$request->email)->first();

            if(!Hash::check($request->password,$user->password))
            {
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ],'Authenticated');

        } catch(Exception $error){
            return ResponseFormatter::error([
                'message' => 'Somehing went wrong',
                'error' => $error
            ],'Authentication failed', 500);
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(),'Data profil user berhasil diambil'
        );
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token,'Token Revoked');
    }
    public function updateProfil(Request $request)
    {
        $data = $request->all();
        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user,'User Data Updated');
    }
}
