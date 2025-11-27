<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
   
   public function register(Request $request)
        {
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
        }


    
   public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Identifiants incorrects'], 401);
    }

    // Vérifier si compte activé
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
}


   
    public function verifyOtp(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'otp'   => 'required|string',
    ]);

    $user = User::where('email', $request->email)->firstOrFail();

    if (!$user->isOtpValid($request->otp)) {
        return response()->json(['message' => "OTP invalide ou expiré"], 422);
    }

   
    $user->update([
        'otp_code' => null,
        'otp_expires_at' => null,
        'email_verified_at' => now(),
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => "Compte activé avec succès !",
        'token'   => $token,
        'user'    => $user,
    ]);
}

    
    public function me(Request $request)
    {
        return $request->user();
    }

  
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Déconnecté avec succès']);
    }
}
