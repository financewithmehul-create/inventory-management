<?php

namespace Webkul\Security\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\Unauthenticated;
use Webkul\Security\Models\User;

#[Group('Security API Management')]
#[Subgroup('Authentication', 'Handle user authentication')]
class AuthController extends Controller
{
    #[Endpoint('Login', 'Authenticate user and generate API token')]
    #[Unauthenticated]
    #[BodyParam('email', 'string', 'User email address', required: true, example: 'admin@example.com')]
    #[BodyParam('password', 'string', 'User password', required: true, example: 'password')]
    #[Response(status: 200, description: 'Login successful', content: '{"message": "Login successful", "token": "1|abcd1234efgh5678ijkl...", "token_type": "Bearer", "user": {"id": 1, "name": "Admin User", "email": "admin@example.com"}}')]
    #[Response(status: 422, description: 'Validation error', content: '{"message": "The given data was invalid.", "errors": {"email": ["The email field is required."], "password": ["The password field is required."]}}')]
    #[Response(status: 401, description: 'Invalid credentials', content: '{"message": "The provided credentials are incorrect."}')]
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'code'     => 'nullable|string|max:20',
        ]);

        $throttleKey = 'api-login:'.sha1(strtolower((string) $request->email).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'message' => 'Too many sign-in attempts. Try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ], 429);
        }

        RateLimiter::hit($throttleKey, 60);

        $user = User::whereRaw(db_dialect()->caseInsensitiveEquals('email'), [$request->email])->first();

        if (! $user || ! Hash::check($request->password, $user->password) || ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (filled($user->getAppAuthenticationSecret())) {
            if (! AppAuthentication::make()->verifyCode((string) $request->input('code'), $user->getAppAuthenticationSecret())) {
                throw ValidationException::withMessages([
                    'code' => ['Enter the 6-digit code from your authenticator app.'],
                ]);
            }
        } elseif ($user->hasEmailAuthentication()) {
            throw ValidationException::withMessages([
                'email' => ['This account uses email codes to sign in. Use the website, or switch to an authenticator app for API access.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message'    => 'Login successful',
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    #[Endpoint('Logout', 'Revoke current API token')]
    #[Authenticated]
    #[Response(status: 200, description: 'Logout successful', content: '{"message": "Logout successful"}')]
    #[Response(status: 401, description: 'Unauthenticated', content: '{"message": "Unauthenticated."}')]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }
}
