<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Entreprise;
use App\Models\Role;
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

    // Vérifier OTP
    if (!$user->isOtpValid($request->otp)) {
        return response()->json(['message' => "OTP invalide ou expiré"], 422);
    }

    // Activer le compte
    $user->update([
        'otp_code'        => null,
        'otp_expires_at'  => null,
        'email_verified_at' => now(),
    ]);

    // Récupérer l'ID du rôle entreprise dynamiquement
    $entrepriseRoleId = Role::where('name', 'entreprise')->value('id');

    if ($user->role_id == $entrepriseRoleId) {

        // Créer l'entreprise si elle n'existe pas
        if (!$user->entreprise) {
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
    }

    // Générer token
    $token = $user->createToken('auth_token')->plainTextToken;

    // Charger l'entreprise et ses documents
    $user->load(['entreprise.documents']);

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
