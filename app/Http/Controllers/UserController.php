<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $user = new User();
        $user->name = $request['name'];
        $user->email = $request['email'];
        $user->password = Hash::make($request['password']);
        $user->save();

        $response = array();
        $response['status'] = 1;
        $response['statuscode'] = 201;
        $response['msg'] = "User Registration Successfully";

        return response()->json($response)->header("Content-type", 'applocation/json');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'token' => $token,
            'user' => Auth::user(),
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60 // in seconds
        ]);
    }
}
