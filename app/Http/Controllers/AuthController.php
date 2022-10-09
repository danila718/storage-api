<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register
     *
     * @param \Illuminate\Http\Request $request
     * @param Response $response
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function register(Request $request, Response $response)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->get('email'))->first();
        if ($user) {
            throw ValidationException::withMessages([
                'email' => ['User with specified email already exists'],
            ]);
        }
        $user = new User();
        $user->fill($request->all($user->getFillable()));
        $user->password = Hash::make($request->get('password'));
        $user->saveOrFail();

        return $response->setStatusCode(201);
    }

    /**
     * Login
     *
     * @param \Illuminate\Http\Request $request
     * @param Response $response
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request, Response $response)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->get('email'))->first();

        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            return $response->setStatusCode(401);
        }

        $newAccessToken = $user->createToken($request->headers->get('User-Agent', 'unknownAgent'));
        $expiresAt = null;
        if ($expiration = config('sanctum.expiration')) {
            $expiresAt = $newAccessToken->accessToken->created_at->addMinutes($expiration)->toDateTimeLocalString();
        }

        return $response->setContent([
            'success' => true,
            'token' => $newAccessToken->plainTextToken,
            'expires_at' => $expiresAt,
        ]);
    }
}
