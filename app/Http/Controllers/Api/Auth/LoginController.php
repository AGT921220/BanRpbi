<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        info('login');
        info($request->all());
        $request->validate([
            'nickname' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $nickname = Str::lower(trim((string) $request->input('nickname')));
        $user = User::query()->where('nickname', $nickname)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'nickname' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $user
            ->createToken($request->input('device_name', 'api'))
            ->plainTextToken;

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => $user,
        ]);
    }
}
