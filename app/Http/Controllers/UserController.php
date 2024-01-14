<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
class UserController extends Controller
{
    public function register(Request $request)
    {
        $validasi = array(
            'name'        => 'required|max:225',
            'email'       => 'required|max:225|email|unique:users',
            'password'    => 'required|min:8',
            'level'       => 'required',
        );

        $validator = Validator::make($request->all(), $validasi);

        if ($validator->fails()) {
            return Response::Json(array('errors' => $validator->getMessageBag()->toArray()));
        } 
 
        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'level'       => $request->level,
            'password'    => bcrypt($request->password)
        ]);
 
        $token = $user->createToken('RandomKeyPassportAuth')->accessToken;

        return response()->json([
            'message' => 'Registered successfully.',
            'data'    => $token,
            'status'  => 'success'
        ]);
    }

    // login
    public function login(Request $request)
    {
        $data = [
            'email'       => $request->email,
            'password'    => $request->password
        ];
 
        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('RandomKeyPassportAuth')->accessToken;

            return response()->json([
                'status'        => 'success',
                'user'          => Auth::user(),
                'authorization' => [
                    'token' => $token,
                    'type' => 'Bearer',
                ]
            ]);

        } else {
            return $this->sendError(['error' => 'Unauthorised'], "Login Failed". 401);
        }
    }

    // profile
    public function userInfo(){
        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'data'   => $user,
            'message' => 'user retrieved successfully.'
        ]);
    }
}
