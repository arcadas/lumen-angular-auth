<?php

namespace App\Http\Controllers\Auth;

use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Handle a registration request to the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|max:255',
                'email' => 'required|unique:users|email|max:255',
                // Password must contain 1 lower case character 1 upper case character and 1 number
                'password' => 'required|confirmed|min:8|regex:/^(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/',
            ]);
        } catch (HttpResponseException $e) {
            return response()->json([
                'error' => [
                    'message' => 'invalid_registration',
                    'status_code' => IlluminateResponse::HTTP_BAD_REQUEST,
                ],
            ], IlluminateResponse::HTTP_BAD_REQUEST);
        }

        $evh = app('hash')->make($request->input('email'));

        // Attempt to register the user
        $user = \DB::table('users')->insert([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => app('hash')->make($request->input('password')),
            'evh' => $evh,
            'active' => false
        ]);

        // Send e-mail verification
        Mail::send('emails.verify', [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'host' => env('APP_HOST_PUBLIC'),
            'hash' => $evh
        ], function ($msg) use ($request) {
            $msg->to([$request->input('email')]);
            $msg->from(['noreply@laa.com']);
            $msg->subject('Please verify your email address');
        });

        // All good so return the user
        return response()->json([
            'success' => [
                'message' => 'user_registrated',
                'evh' => env('APP_TEST') ? $evh : null
            ]
        ]);
    }

    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email|max:255',
                'password' => 'required',
            ]);
        } catch (HttpResponseException $e) {
            return response()->json([
                'error' => [
                    'message' => 'invalid_auth',
                    'status_code' => IlluminateResponse::HTTP_BAD_REQUEST,
                ],
            ], IlluminateResponse::HTTP_BAD_REQUEST);
        }

        try {
            $token = JWTAuth::attempt($request->only('email', 'password'));
            $user = JWTAuth::user();
            // Attempt to verify the credentials and create a token for the user
            if (!$token || !$user->active) {
                return response()->json([
                    'error' => [
                        'message' => 'invalid_credentials',
                    ],
                ], IlluminateResponse::HTTP_UNAUTHORIZED);
            }
        } catch (JWTException $e) {
            // Something went wrong whilst attempting to encode the token
            return response()->json([
                'error' => [
                    'message' => 'could_not_create_token'
                ],
            ], IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // All good so return the token
        return response()->json([
            'success' => [
                'message' => 'token_generated',
                'token' => $token,
                'user' => $user
            ]
        ]);
    }

    /**
     * Invalidate (logout) a token.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        $token = JWTAuth::parseToken();

        $token->invalidate();

        return ['success' => 'token_invalidated'];
    }

    /**
     * Verify e-mail address
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        // Find user by e-mail and hash
        $result = \DB::table('users')
            ->where('email', $request->input('email'))
            ->where('evh', $request->input('hash'))
            ->first();

        // Activate the user
        \DB::table('users')
            ->where('email', $request->input('email'))
            ->update(['active' => 1]);

        return response()->json([
            'success' => [
                'message' => $result ? 'email_verified' : 'email_not_verified'
            ]
        ]);
    }
}
