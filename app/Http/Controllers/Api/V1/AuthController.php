<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        Log::channel('auth')->info('New user registered', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'ip'      => $request->ip(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->createdResponse([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'User registered successfully');
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {

            Log::channel('auth')->warning('Failed login attempt', [
                'email' => $request->email,
                'ip'    => $request->ip(),
            ]);

            return $this->errorResponse('Invalid credentials', 401);
        }

        $user = Auth::user();

        if (!$user->isActive()) {

            Log::channel('auth')->warning('Inactive user login attempt', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $request->ip(),
            ]);

            return $this->forbiddenResponse('Your account is deactivated');
        }

        Log::channel('auth')->info('User logged in', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'ip'      => $request->ip(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'Logged in successfully');
    }

    public function logout()
    {
        $user = auth()->user();

        Log::channel('auth')->info('User logged out', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);

        $token = $user->currentAccessToken();

        if (method_exists($token, 'delete')) {
            $token->delete();
        }

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function me()
    {
        return $this->successResponse([
            'user' => new UserResource(auth::user()),
        ]);
    }
}