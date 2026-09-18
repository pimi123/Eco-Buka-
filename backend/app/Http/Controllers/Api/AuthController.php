<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:80'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $user = User::query()->create([
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => Str::lower($data['email']),
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'is_admin' => false,
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => 'Llogaria u krijua. Ju lutemi verifikoni emailin para checkout-it.',
            'user' => $this->userPayload($user),
            'token' => $user->createToken('customer')->plainTextToken,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', Str::lower($data['email']))
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Emaili ose fjalëkalimi nuk është i saktë.',
            ], 422);
        }

        if ($user->is_admin) {
            return response()->json([
                'message' => 'Ky endpoint është vetëm për klientë.',
            ], 403);
        }

        return response()->json([
            'message' => 'Kyçja u krye me sukses.',
            'user' => $this->userPayload($user),
            'token' => $user->createToken('customer')->plainTextToken,
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Dolët nga llogaria me sukses.',
        ]);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Emaili është tashmë i verifikuar.',
                'user' => $this->userPayload($user),
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Emaili i verifikimit u dërgua përsëri.',
        ]);
    }

    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse|JsonResponse
    {
        $user = User::query()->findOrFail($id);

        abort_unless(hash_equals((string) $hash, sha1($user->getEmailForVerification())), 403);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Emaili u verifikua me sukses.',
                'user' => $this->userPayload($user),
            ]);
        }

        return redirect()->away(rtrim((string) config('nestpay.frontend_url'), '/').'/login?verified=1');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::sendResetLink([
            'email' => Str::lower($data['email']),
        ]);

        return response()->json([
            'message' => 'Nëse emaili ekziston, do të pranoni një link për rivendosjen e fjalëkalimit.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::reset(
            [
                'email' => Str::lower($data['email']),
                'password' => $data['password'],
                'password_confirmation' => $request->string('password_confirmation')->toString(),
                'token' => $data['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Linku për rivendosjen e fjalëkalimit nuk është valid ose ka skaduar.',
            ], 422);
        }

        return response()->json([
            'message' => 'Fjalëkalimi u ndryshua me sukses.',
        ]);
    }

    public function passwordResetRedirect(Request $request, string $token): RedirectResponse
    {
        $query = http_build_query(array_filter([
            'token' => $token,
            'email' => $request->query('email'),
        ]));

        return redirect()->away(rtrim((string) config('nestpay.frontend_url'), '/').'/reset-password'.($query ? '?'.$query : ''));
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'email_verified' => $user->hasVerifiedEmail(),
        ];
    }
}
