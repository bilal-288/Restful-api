<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\PersonalAccessTokenResult;
use App\Models\OAuthAccessToken;


class UserController extends Controller
{
    /**
     * I will make registration for the user
     */

    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|max:255',
                'password' => 'required',
                'email' => 'required|unique:users',
            ],
            [
                'name.required' => 'Please enter the name first', // custom message
                'password.required' => 'Please enter the password first', // custom message       
            ]

        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $user = new User();  

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->created_at = Carbon::now()->toDateTimeString();
        $user->updated_at = Carbon::now()->toDateTimeString();
        $is_saved = $user->save();
        
        $token = $user->createToken('auth_token')->accessToken;

        if ($is_saved) {
            return response()->json([
                'message' => 'User register successfully',
                'token'   => $token,
                'status'  => 1
            ], 200);
        } else {
            return response()->json([
                'message' => 'internel server error'
            ], 505);
        }
    }

    /**
     * I will do login for the users
     */

    public function login(Request $req)
    {
        $validator = Validator::make(
            $req->all(),
            [
                'password' => 'required',
                'email' => 'required',
            ],
            [
                
                'password.required' => 'Please enter the password first', // custom message   
                'email.required' => 'Please enter the email first', // custom message    
            ]

        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }


        $user = User::where('email', $req->email)->first();
        $token = $user->createToken('auth_token')->accessToken;
        
        if (!$user || !Hash::check($req->password, $user->password)) {
            $response =
                [
                    'error' => 'User or Password is incorrect',
                    'status' => 0
                ];
        } else {
            $response =
                [
                    'message' => 'User found',
                    'status' => 1,
                    'data'   => $user,
                    'token'  => $token
                    
                    
                ];
        }

        return response()->json($response, 200);
    }

    public function getAllusers()
    {
        $users = User::all();
        if (count($users) > 0) {
            $response =
                [
                    'message' => count($users) . 'users found',
                    'status'  => 1,
                    'data'    => $users
                ];
        } else {
            $response =
                [
                    'message' => count($users) . 'users found',
                    'status'  => 0,
                ];
        }

        return response()->json($response, 200);
    }

    public function getUser($id)
    {
        $user = User::find($id);
        if ($user) {
            return response()->json([
                'message'   => 'User found',
                'status'    => 1,
                'user data' => $user,

            ], 200);
        } else {
            return response()->json([
                'message' => 'Not found',
                'status'    => 0
            ], 200);
        }
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => 'user not exist',
                'status'  => 0
            ];
            $responseCode = 404;
        } else {
            /**
             *  DB::beginTransaction() menan start a tranection
             *  DB::commit()  mean commit the changes in the database
             *  DB::rollBack() mean if some part of query is run and other not
             *  then revert all the changes that has been done
             */
            DB::beginTransaction();
            try {
                $user->delete();
                DB::commit();
                $response = [
                    'message' => 'User deleted successfully',
                    'status'  => 1
                ];
                $responseCode = 200;
            } catch (\Exception $err) {
                DB::rollBack();
                $response = [
                    'message' => 'Internel server error',
                    'status'  => 0
                ];
                $responseCode = 500;
            }
        }

        return response()->json($response, $responseCode);
    }

    public function changeUserPassword($id, Request $req)
    {

        $user = User::find($id);
        if (!$user) {
            $response =  [
                'status' => 0,
                'message' => 'User does not found'
            ];

            $responseCode = 404;
        } else {

            if (Hash::check($req->old_password, $user->password)) {
                if ($req->new_password == $req->confirm_password) {
                    DB::beginTransaction();
                    try {
                        $user->password = Hash::make($req->new_password);
                        $user->save();
                        DB::commit();
                    } catch (\Exception $err) {
                        $user = null;
                        DB::rollBack();
                    }

                    if (is_null($user)) {
                        $response = [
                            'status'    => 0,
                            'message'   => 'internet server error',
                            'error_msg' => $err->getMessage()
                        ];

                        $responseCode = 500;
                    } else {
                        $response = [
                            'status' => 1,
                            'message' => 'password updated successfully'
                        ];

                        $responseCode = 200;
                    }
                } else {
                    $response =  [
                        'status' => 0,
                        'message' => 'new password and confirm password doesnot matched'
                    ];

                    $responseCode = 400;
                }
            } else {
                $response =  [
                    'status' => 0,
                    'message' => 'password not matched',
                    'data'    => $req->old_password,
                ];

                $responseCode = 400;
            }
        }

        return response()->json($response, $responseCode);
    }
}
