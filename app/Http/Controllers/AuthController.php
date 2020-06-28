<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\User;
use App\LoginToken;

class AuthController extends Controller
{
    public function login(Request $request) {
        if(Auth::attempt($request->all())) {
            $userId = Auth::user()->id;
            $role = Auth::user()->role;

            $token = md5($userId);

            LoginToken::create([
                'token' => $token,
                'user_id' => $userId
            ]);

            return response()->json([
                'token' => $token,
                'role' => $role
            ], 200);
        }

        return response()->json(['message' => 'invalid login'], 401);
    }

    public function logout(Request $request) {
        $token = LoginToken::where('token', $request->token)->first();

        if(!$token)
            return response()->json(['message' => 'invalid login'], 401);

        $token->delete();
        return response()->json(['message' => 'Logout success'], 200);
    }
}
