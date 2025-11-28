<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Entreprise;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'role_id'        => 'required|exists:roles,id',
                'name'           => 'required|string',
                'email'          => 'required|email|unique:users,email',
                'password'       => 'required|string|min:6',
                'genre'          => 'required|string',
                'phone'          => 'required|string',
                'date_naissance' => 'required|date',
            ]);

            $otp = rand(100000, 999999);

            $user = User::create([
                'role_id'        => $request->role_id,
                'name'           => $request->name,
                'email'          => $request->email,
                'password'       => Hash::make($request->password),
                'genre'          => $request->genre,
                'phone'          => $request->phone,
                'date_naissance' => $request->date_naissance,
                'otp_code'       => $otp,
                'otp_expires_at' => now()->addMinutes(10),
            ]);

            return response()->json([
                'message' => 'Compte créé. Vérifiez votre email/SMS pour l’OTP.',
                'otp'     => $otp, // retirer en production
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la création du compte', 'error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Identifiants incorrects'], 401);
            }

            if (!$user->email_verified_at) {
                return response()->json([
                    'message' => 'Votre compte n’est pas encore activé. Validez l’OTP.'
                ], 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Connexion réussie',
                'token'   => $token,
                'user'    => $user,
            ]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la connexion', 'error' => $e->getMessage()], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp'   => 'required|string',
            ]);

            $user = User::where('email', $request->email)->firstOrFail();

            if (!$user->isOtpValid($request->otp)) {
                return response()->json(['message' => "OTP invalide ou expiré"], 422);
            }

            $user->update([
                'otp_code'        => null,
                'otp_expires_at'  => null,
                'email_verified_at' => now(),
            ]);

            $entrepriseRoleId = Role::where('name', 'entreprise')->value('id');

            if ($user->role_id == $entrepriseRoleId && !$user->entreprise) {
                Entreprise::create([
                    'user_id'     => $user->id,
                    'name'        => $user->name . " Entreprise",
                    'sector'      => null,
                    'adresse'     => null,
                    'description' => null,
                    'logo'        => null,
                    'country_id'  => null,
                    'city_id'     => null,
                    'status'      => 'pending',
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $user->load(['entreprise.documents']);

            return response()->json([
                'message' => "Compte activé avec succès !",
                'token'   => $token,
                'user'    => $user,
            ]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la vérification OTP', 'error' => $e->getMessage()], 500);
        }
    }

    public function me(Request $request)
    {
        return $request->user();
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Déconnecté avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la déconnexion', 'error' => $e->getMessage()], 500);
        }
    }
}
