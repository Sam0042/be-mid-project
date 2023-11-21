<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Auth;

class AuthController extends Controller
{
    //Register
    public function register(Request $request){
        $input = [
            "name"=>$request->name,
            "email"=>$request->email,
            "password"=> Hash::make($request->password)
        ];

        $user = User::create($input);

        $data=[
            "message"=>"User is created successfully"
        ];

        return response()->json($data,200);
    }

    //Login
    public function login(Request $request){
        $input = [
            "email"=>$request->email,
            "password"=>$request->password
        ];

        if (Auth::attempt($input)){
            $token = Auth::user()->createToken("auth_token");

            $data=[
                "message"=>"Login successfully",
                "token"=> $token->plainTextToken
            ];

            return response()->json($data,200);
        }
        else{
            $data=[
                "message"=>"Username or Password is wrong"
            ];

            return response()->json($data,401);
        }
    }
}
